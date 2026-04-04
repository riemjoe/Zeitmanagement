<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TimeEntry extends Model
{
    protected $fillable = [
        'project_id', 'work_category_id', 'task_id',
        'date', 'hours', 'description', 'ticket_id', 'billed',
    ];

    protected $casts = [
        'date'   => 'date',
        'hours'  => 'decimal:2',
        'billed' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Task::class);
    }

    public function invoices(): BelongsToMany
    {
        return $this->belongsToMany(Invoice::class, 'invoice_time_entry');
    }

    /**
     * Betrag dieser Zeiterfassung (Stunden × Stundenlohn des Projekts).
     */
    public function getAmountAttribute(): float
    {
        return (float) $this->hours * $this->project->effective_hourly_rate;
    }
}
