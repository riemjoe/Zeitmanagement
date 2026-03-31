<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'project_id', 'assigned_to', 'work_category_id', 'title', 'description',
        'priority', 'kanban_status', 'position', 'due_date',
    ];

    protected $casts = [
        'due_date' => 'date',
        'position' => 'integer',
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
