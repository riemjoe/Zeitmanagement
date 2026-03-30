<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timer extends Model
{
    protected $fillable = [
        'project_id', 'work_category_id', 'started_at', 'description',
    ];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    /**
     * Vergangene Sekunden seit Start.
     */
    public function getElapsedSecondsAttribute(): int
    {
        return (int) now()->diffInSeconds($this->started_at);
    }

    /**
     * Vergangene Zeit als Dezimalstunden (auf 2 Nachkommastellen gerundet).
     */
    public function getElapsedHoursAttribute(): float
    {
        return round($this->elapsed_seconds / 3600, 2);
    }
}
