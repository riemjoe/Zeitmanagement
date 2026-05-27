<?php

namespace App\Models;

use App\Services\AutomationEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ITIL Change – Klasse heißt ItilChange, da "Change" in PHP kein reserviertes Wort,
 * aber potenziell konfliktreich mit Paketen ist. Die Tabelle bleibt "changes".
 */
class ItilChange extends Model
{
    protected $table = 'changes';

    protected $fillable = [
        'number', 'title', 'description', 'status', 'type', 'priority', 'impact', 'risk',
        'category', 'affected_service',
        'customer_id', 'ticket_id', 'assigned_to', 'requested_by',
        'planned_start_at', 'planned_end_at', 'actual_start_at', 'actual_end_at',
        'implementation_plan', 'rollback_plan', 'test_plan', 'post_review',
        'completed_at', 'cancelled_at',
    ];

    protected $casts = [
        'planned_start_at' => 'datetime',
        'planned_end_at'   => 'datetime',
        'actual_start_at'  => 'datetime',
        'actual_end_at'    => 'datetime',
        'completed_at'     => 'datetime',
        'cancelled_at'     => 'datetime',
    ];

    // ── Status-Optionen ──────────────────────────────────────────────────────

    public const STATUSES = [
        'draft'       => ['label' => 'Entwurf',         'color' => 'gray'],
        'submitted'   => ['label' => 'Eingereicht',     'color' => 'blue'],
        'in_progress' => ['label' => 'In Bearbeitung',  'color' => 'indigo'],
        'completed'   => ['label' => 'Abgeschlossen',   'color' => 'green'],
        'cancelled'   => ['label' => 'Abgebrochen',     'color' => 'red'],
    ];

    public const TYPES = [
        'standard'  => ['label' => 'Standard',   'color' => 'gray'],
        'normal'    => ['label' => 'Normal',      'color' => 'blue'],
        'emergency' => ['label' => 'Notfall',     'color' => 'red'],
    ];

    public const PRIORITIES = [
        'critical' => ['label' => 'Kritisch', 'color' => 'red'],
        'high'     => ['label' => 'Hoch',     'color' => 'orange'],
        'medium'   => ['label' => 'Mittel',   'color' => 'yellow'],
        'low'      => ['label' => 'Niedrig',  'color' => 'gray'],
    ];

    public const IMPACTS = ['high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];
    public const RISKS   = ['high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];

    // ── Relationen ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getTypeColorAttribute(): string
    {
        return self::TYPES[$this->type]['color'] ?? 'gray';
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
        return 'CHG-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Automationen auslösen ────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (ItilChange $change) {
            AutomationEngine::dispatch('model_created', $change->toArray(), 'ItilChange');
        });

        static::updated(function (ItilChange $change) {
            AutomationEngine::dispatch('model_updated', $change->toArray(), 'ItilChange');
        });
    }
}
