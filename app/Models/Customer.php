<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_number', 'name', 'email', 'contact_person', 'phone',
        'street', 'zip', 'city', 'country',
        'notes',
    ];

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
