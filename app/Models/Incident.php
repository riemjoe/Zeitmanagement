<?php

namespace App\Models;

use App\Services\AutomationEngine;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Incident extends Model
{
    protected $fillable = [
        'number', 'title', 'description', 'status', 'priority', 'impact', 'urgency',
        'category', 'affected_service',
        'customer_id', 'ticket_id', 'problem_id', 'assigned_to',
        'reported_by', 'workaround', 'resolution',
        'response_due_at', 'resolve_due_at', 'responded_at',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'response_due_at' => 'datetime',
        'resolve_due_at'  => 'datetime',
        'responded_at'    => 'datetime',
        'resolved_at'     => 'datetime',
        'closed_at'       => 'datetime',
    ];

    // ── Status-Optionen ──────────────────────────────────────────────────────

    public const STATUSES = [
        'open'        => ['label' => 'Offen',           'color' => 'blue'],
        'in_progress' => ['label' => 'In Bearbeitung',  'color' => 'indigo'],
        'pending'     => ['label' => 'Ausstehend',      'color' => 'yellow'],
        'resolved'    => ['label' => 'Gelöst',          'color' => 'green'],
        'closed'      => ['label' => 'Geschlossen',     'color' => 'gray'],
    ];

    public const PRIORITIES = [
        'critical' => ['label' => 'Kritisch', 'color' => 'red'],
        'high'     => ['label' => 'Hoch',     'color' => 'orange'],
        'medium'   => ['label' => 'Mittel',   'color' => 'yellow'],
        'low'      => ['label' => 'Niedrig',  'color' => 'gray'],
    ];

    public const IMPACTS   = ['high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];
    public const URGENCIES = ['high' => 'Hoch', 'medium' => 'Mittel', 'low' => 'Niedrig'];

    // ── SLA-Defaults (Stunden) ───────────────────────────────────────────────

    public const SLA_DEFAULTS = [
        'critical' => ['response' => 1,  'resolve' => 4],
        'high'     => ['response' => 4,  'resolve' => 8],
        'medium'   => ['response' => 8,  'resolve' => 24],
        'low'      => ['response' => 24, 'resolve' => 72],
    ];

    // ── Relationen ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function problem(): BelongsTo
    {
        return $this->belongsTo(Problem::class);
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

    public function getPriorityLabelAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['label'] ?? ucfirst($this->priority);
    }

    public function getPriorityColorAttribute(): string
    {
        return self::PRIORITIES[$this->priority]['color'] ?? 'gray';
    }

    public function getIsResponseOverdueAttribute(): bool
    {
        return $this->response_due_at
            && !$this->responded_at
            && $this->response_due_at->isPast()
            && !in_array($this->status, ['resolved', 'closed']);
    }

    public function getIsResolveOverdueAttribute(): bool
    {
        return $this->resolve_due_at
            && !$this->resolved_at
            && $this->resolve_due_at->isPast()
            && !in_array($this->status, ['resolved', 'closed']);
    }

    // ── Nummer generieren ────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->value('number');
        $seq  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'INC-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── SLA berechnen ────────────────────────────────────────────────────────

    public static function calcSla(string $priority): array
    {
        $responseHours = (int) Setting::get("itil_sla_{$priority}_response", self::SLA_DEFAULTS[$priority]['response']);
        $resolveHours  = (int) Setting::get("itil_sla_{$priority}_resolve",  self::SLA_DEFAULTS[$priority]['resolve']);

        return [
            'response_due_at' => now()->addHours($responseHours),
            'resolve_due_at'  => now()->addHours($resolveHours),
        ];
    }

    // ── Automationen auslösen ────────────────────────────────────────────────

    protected static function boot(): void
    {
        parent::boot();

        static::created(function (Incident $incident) {
            AutomationEngine::dispatch('model_created', $incident->toArray(), 'Incident');
        });

        static::updated(function (Incident $incident) {
            AutomationEngine::dispatch('model_updated', $incident->toArray(), 'Incident');
        });
    }
}
