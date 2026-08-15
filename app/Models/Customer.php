<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_number', 'name', 'email', 'contact_person', 'phone',
        'street', 'zip', 'city', 'country', 'notes',
        'portal_enabled', 'portal_password', 'portal_must_change_password',
        'portal_invitation_token', 'portal_invitation_expires_at',
        'portal_2fa_secret', 'portal_2fa_enabled', 'portal_2fa_backup_codes',
    ];

    protected $casts = [
        'portal_enabled'               => 'boolean',
        'portal_must_change_password'  => 'boolean',
        'portal_invitation_expires_at' => 'datetime',
        'portal_2fa_enabled'           => 'boolean',
        'portal_2fa_backup_codes'      => 'array',
    ];

    protected $hidden = ['portal_password', 'portal_2fa_secret', 'portal_invitation_token'];

    /**
     * Eindeutige 8-stellige Kundennummer im Format xxxx-xxxx generieren.
     */
    public static function generateNumber(): string
    {
        do {
            $part1 = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
            $part2 = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4));
            $number = $part1 . '-' . $part2;
        } while (static::where('customer_number', $number)->exists());

        return $number;
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function slaSettings(): HasMany
    {
        return $this->hasMany(CustomerSlaSetting::class);
    }

    public function itilSlaSettings(): HasMany
    {
        return $this->hasMany(CustomerItilSlaSetting::class);
    }

    public function customerApprovals(): HasMany
    {
        return $this->hasMany(CustomerApproval::class);
    }

    /**
     * Vollständige Adresse als String.
     */
    public function getAddressAttribute(): string
    {
        $parts = array_filter([
            $this->street,
            trim($this->zip . ' ' . $this->city),
            $this->country !== 'Deutschland' ? $this->country : null,
        ]);
        return implode(', ', $parts);
    }
}
