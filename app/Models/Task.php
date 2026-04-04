<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'assigned_to', 'work_category_id', 'title', 'description',
        'priority', 'kanban_status', 'position', 'due_date', 'budget_hours',
    ];

    protected $casts = [
        'due_date'     => 'date',
        'position'     => 'integer',
        'budget_hours' => 'decimal:2',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(\App\Models\WorkCategory::class);
    }

    public function timeEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\TimeEntry::class);
    }

    /** Summe aller erfassten Stunden für diese Aufgabe. */
    public function getTrackedHoursAttribute(): float
    {
        return (float) $this->timeEntries->sum('hours');
    }

    /** Prozentsatz des verbrauchten Zeitbudgets (0–100+). */
    public function getBudgetHoursPercentAttribute(): int
    {
        if (! $this->budget_hours || (float) $this->budget_hours <= 0) {
            return 0;
        }
        return (int) round($this->tracked_hours / (float) $this->budget_hours * 100);
    }

    /** Lesbare Status-Labels */
    public static function statusLabel(string $status): string
    {
        return match($status) {
            'ready'     => 'Ready',
            'wip'       => 'In Arbeit',
            'testing'   => 'Testing',
            'completed' => 'Abgeschlossen',
            default     => ucfirst($status),
        };
    }

    /** Prioritäts-Label */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'Niedrig',
            'medium' => 'Mittel',
            'high'   => 'Hoch',
            default  => ucfirst($this->priority),
        };
    }

    /** Ist die Aufgabe überfällig? */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && $this->kanban_status !== 'completed';
    }
}
