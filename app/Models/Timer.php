<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Timer extends Model
{
    protected $fillable = [
        'project_id', 'work_category_id', 'started_at', 'description',
        'paused_at', 'paused_seconds',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'paused_at'  => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function workCategory(): BelongsTo
    {
        return $this->belongsTo(WorkCategory::class);
    }

    /** Timer ist gerade pausiert. */
    public function getIsPausedAttribute(): bool
    {
        return $this->paused_at !== null;
    }

    /**
     * Vergangene (aktive) Sekunden – pausierte Zeit wird abgezogen.
     * Wenn pausiert: zählt bis zum Pausenzeitpunkt.
     */
    public function getElapsedSecondsAttribute(): int
    {
        $accumulated = (int) ($this->paused_seconds ?? 0);
        $reference   = $this->paused_at ?? now();
        return max(0, (int) $reference->diffInSeconds($this->started_at) - $accumulated);
    }

    /**
     * Vergangene Zeit als Dezimalstunden (auf 2 Nachkommastellen gerundet).
     */
    public function getElapsedHoursAttribute(): float
    {
        return round($this->elapsed_seconds / 3600, 2);
    }
}
