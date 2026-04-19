<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Webhook extends Model
{
    protected $fillable = [
        'name', 'description', 'token', 'secret', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Boot ─────────────────────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        // Token automatisch beim Erstellen generieren
        static::creating(function (Webhook $webhook) {
            if (empty($webhook->token)) {
                $webhook->token = Str::random(48);
            }
        });
    }

    // ── Beziehungen ──────────────────────────────────────────────────────────

    public function automations(): HasMany
    {
        return $this->hasMany(Automation::class);
    }

    // ── Hilfsmethoden ────────────────────────────────────────────────────────

    /**
     * Gibt die öffentliche Webhook-URL zurück.
     */
    public function getUrl(): string
    {
        return url('/webhook/' . $this->token);
    }

    /**
     * Prüft ob der Payload mit dem konfigurierten Secret signiert wurde.
     * Unterstützt GitHub-Stil: X-Hub-Signature-256: sha256=<hex>
     */
    public function verifySignature(string $payload, string $signature): bool
    {
        if (empty($this->secret)) {
            return true; // Kein Secret → keine Prüfung nötig
        }

        $expected = 'sha256=' . hash_hmac('sha256', $payload, $this->secret);

        return hash_equals($expected, $signature);
    }

    /**
     * Gibt die Anzahl der verknüpften aktiven Automationen zurück.
     */
    public function activeAutomationsCount(): int
    {
        return $this->automations()->where('is_active', true)->count();
    }
}
