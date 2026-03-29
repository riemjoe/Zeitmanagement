<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'description',
        'hourly_rate', 'status', 'notes',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Effektiver Stundenlohn: projektspezifisch oder globale Einstellung.
     */
    public function getEffectiveHourlyRateAttribute(): float
    {
        return $this->hourly_rate ?? (float) Setting::get('hourly_rate', 80);
    }

    /**
     * Gesamtstunden des Projekts.
     */
    public function getTotalHoursAttribute(): float
    {
        return (float) $this->timeEntries()->sum('hours');
    }

    /**
     * Gesamtbetrag Arbeitszeit des Projekts.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->timeEntries->sum(fn ($e) =>
            $e->hours * $this->effective_hourly_rate
        );
    }
}
