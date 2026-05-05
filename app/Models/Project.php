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
        'hourly_rate', 'status', 'is_archived', 'notes',
        'budget_hours', 'budget_amount', 'deadline', 'quote_id',
        'show_open_only',
    ];

    protected $casts = [
        'hourly_rate'    => 'decimal:2',
        'budget_hours'   => 'decimal:2',
        'budget_amount'  => 'decimal:2',
        'deadline'       => 'date',
        'is_archived'    => 'boolean',
        'show_open_only' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    /** Nur offene (noch keiner Rechnung zugeordnete) Zeiteinträge. */
    public function openTimeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class)->whereDoesntHave('invoices');
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

    /** Hochgeladene Dateien des Projekts. */
    public function files(): HasMany
    {
        return $this->hasMany(\App\Models\ProjectFile::class)->orderByDesc('created_at');
    }

    /** Meilensteine des Projekts. */
    public function milestones(): HasMany
    {
        return $this->hasMany(\App\Models\Milestone::class)->orderBy('due_date')->orderBy('id');
    }

    /** Projekt-Nachrichten (Chat). */
    public function messages(): HasMany
    {
        return $this->hasMany(\App\Models\ProjectMessage::class)->orderBy('created_at');
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
     * Bei show_open_only=true werden nur Einträge ohne Rechnungszuordnung gezählt.
     */
    public function getTotalHoursAttribute(): float
    {
        if ($this->show_open_only) {
            // Wenn die Relation bereits geladen ist (z.B. auf der Detailseite),
            // Collection filtern – andernfalls direkte DB-Abfrage.
            if ($this->relationLoaded('timeEntries')) {
                return (float) $this->timeEntries
                    ->filter(fn ($e) => $e->invoices->isEmpty())
                    ->sum('hours');
            }
            return (float) $this->openTimeEntries()->sum('hours');
        }
        return (float) $this->timeEntries()->sum('hours');
    }

    /**
     * Gesamtbetrag Arbeitszeit des Projekts.
     * Bei show_open_only=true werden nur Einträge ohne Rechnungszuordnung berücksichtigt.
     */
    public function getTotalAmountAttribute(): float
    {
        if ($this->show_open_only) {
            if ($this->relationLoaded('timeEntries')) {
                return $this->timeEntries
                    ->filter(fn ($e) => $e->invoices->isEmpty())
                    ->sum(fn ($e) => $e->hours * $this->effective_hourly_rate);
            }
            return $this->openTimeEntries()->get()
                ->sum(fn ($e) => $e->hours * $this->effective_hourly_rate);
        }
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
