<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Project extends Model
{
    protected $fillable = [
        'customer_id', 'name', 'description',
        'hourly_rate', 'status', 'notes',
        'budget_hours', 'budget_amount', 'deadline', 'quote_id',
    ];

    protected $casts = [
        'hourly_rate'    => 'decimal:2',
        'budget_hours'   => 'decimal:2',
        'budget_amount'  => 'decimal:2',
        'deadline'       => 'date',
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

    /** Alle Aufgaben des Projekts (nach Position sortiert). */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class)->orderBy('position')->orderBy('id');
    }

    /** Alias für tasks() – Rückwärtskompatibilität. */
    public function todos(): HasMany
    {
        return $this->tasks();
    }

    /** Wiederkehrende Aufgaben-Vorlagen des Projekts. */
    public function recurringTasks(): HasMany
    {
        return $this->hasMany(RecurringTask::class)->orderBy('title');
    }

    /** Wartungsplan-Ereignisse des Projekts. */
    public function maintenanceEvents(): HasMany
    {
        return $this->hasMany(\App\Models\MaintenanceEvent::class)->orderBy('scheduled_date');
    }

    public function quote(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Quote::class);
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

    /** Stunden-Budget-Prozentsatz (0–100+). */
    public function getBudgetHoursPercentAttribute(): float
    {
        if (!$this->budget_hours || (float) $this->budget_hours <= 0) return 0;
        return min(100, round($this->total_hours / (float) $this->budget_hours * 100, 1));
    }

    /** Euro-Budget-Prozentsatz (0–100+). */
    public function getBudgetAmountPercentAttribute(): float
    {
        if (!$this->budget_amount || (float) $this->budget_amount <= 0) return 0;
        return min(100, round($this->total_amount / (float) $this->budget_amount * 100, 1));
    }

    /** Tage bis Deadline (negativ = überfällig). */
    public function getDaysUntilDeadlineAttribute(): ?int
    {
        if (!$this->deadline) return null;
        return (int) now()->startOfDay()->diffInDays($this->deadline->startOfDay(), false);
    }
}
