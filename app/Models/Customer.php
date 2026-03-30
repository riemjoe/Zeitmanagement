<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'email', 'phone',
        'street', 'zip', 'city', 'country',
        'notes',
    ];

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
