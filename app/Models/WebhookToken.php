<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookToken extends Model
{
    protected $fillable = [
        'name', 'token', 'permissions', 'is_active', 'last_used_at', 'expires_at',
    ];

    protected $casts = [
        'permissions'  => 'array',
        'is_active'    => 'boolean',
        'last_used_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    /**
     * Alle verfügbaren Endpunkte mit Bezeichnungen, gruppiert nach Kategorie.
     * Der Key entspricht dem Permission-String, der in permissions[] gespeichert wird.
     */
    public const ENDPOINTS = [
        'Helpdesk' => [
            'webhooks.tickets'   => 'Tickets erstellen  (POST /api/webhooks/tickets)',
            'webhooks.customers' => 'Kunden erstellen   (POST /api/webhooks/customers)',
            'webhooks.projects'  => 'Projekte erstellen (POST /api/webhooks/projects)',
        ],
        'ITIL' => [
            'itil.incidents' => 'Incidents erstellen (POST /api/itil/incidents)',
            'itil.problems'  => 'Problems erstellen  (POST /api/itil/problems)',
            'itil.changes'   => 'Changes erstellen   (POST /api/itil/changes)',
        ],
    ];

    /**
     * Gibt alle Endpoint-Keys als flaches Array zurück.
     */
    public static function allEndpointKeys(): array
    {
        return collect(self::ENDPOINTS)->flatMap(fn ($group) => array_keys($group))->values()->all();
    }

    /**
     * Generiert einen neuen, einzigartigen Token-String.
     */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(48);
        } while (static::where('token', $token)->exists());

        return $token;
    }

    /**
     * Authentifiziert einen eingehenden Request gegen die Token-Datenbank.
     * Gibt das WebhookToken-Objekt zurück oder null bei Fehler.
     *
     * @param  string $endpointKey  z. B. 'webhooks.tickets' oder 'itil.incidents'
     */
    public static function authenticate(Request $request, string $endpointKey): ?self
    {
        // Token aus Header oder Query extrahieren
        $rawToken = null;

        $authHeader = $request->header('Authorization', '');
        if (str_starts_with($authHeader, 'Bearer ')) {
            $rawToken = substr($authHeader, 7);
        } elseif ($request->query('token')) {
            $rawToken = $request->query('token');
        }

        if (empty($rawToken)) {
            return null;
        }

        /** @var self|null $wt */
        $wt = static::where('token', $rawToken)->where('is_active', true)->first();

        if (!$wt) {
            return null;
        }

        // Ablaufdatum prüfen
        if ($wt->expires_at && $wt->expires_at->isPast()) {
            return null;
        }

        // Endpoint-Berechtigung prüfen
        if (!in_array($endpointKey, $wt->permissions ?? [], true)) {
            return null;
        }

        // Zeitstempel aktualisieren (fire & forget)
        $wt->updateQuietly(['last_used_at' => now()]);

        return $wt;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
