<?php

namespace App\Models;

use App\Services\AutomationEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Problem extends Model
{
    protected $fillable = [
        'number', 'title', 'description', 'status', 'priority', 'impact',
        'category', 'affected_service',
        'customer_id', 'assigned_to',
        'root_cause', 'workaround', 'resolution',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    // ── Status-Optionen ──────────────────────────────────────────────────────

    public const STATUSES = [
        'open'                 => ['label' => 'Offen',               'color' => 'blue'],
        'under_investigation'  => ['label' => 'In Untersuchung',     'color' => 'indigo'],
        'known_error'          => ['label' => 'Known Error',         'color' => 'orange'],
        'resolved'             => ['label' => 'Gelöst',              'color' => 'green'],
        'closed'               => ['label' => 'Geschlossen',         'color' => 'gray'],
    ];

    public const PRIORITIES = [
        'critical' => ['label' => 'Kritisch', 'color' => 'red'],
        'high'     => ['label' => 'Hoch',     'color' => 'orange'],
        'medium'   => ['label' => 'Mittel',   'color' => 'yellow'],
        'low'      => ['label' => 'Niedrig',  'color' => 'gray'],
    ];

    public const IMPACTS = ['high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];

    // ── Relationen ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status]['label'] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUSES[$this->status]['color'] ?? 'gray';
    }

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? ucfirst($this->priority);
    }

    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['color'] ?? 'gray';
    }

    // ── Nummer generieren ────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->value('number');
        $seq  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'PRB-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Automationen auslösen ────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Problem $problem) {
            AutomationEngine::dispatch('model_created', $problem->toArray(), 'Problem');
        });

        static::updated(function (Problem $problem) {
            AutomationEngine::dispatch('model_updated', $problem->toArray(), 'Problem');
        });
    }
}
