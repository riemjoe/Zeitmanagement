<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ServiceTask extends Model
{
    protected $fillable = [
        'number', 'title', 'description', 'type', 'status', 'priority',
        'project_id', 'customer_id', 'assigned_to', 'due_date',
        'taskable_type', 'taskable_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    // ── Status-Optionen ──────────────────────────────────────────────────────

    public const STATUSES = [
        'open'        => ['label' => 'Offen',           'color' => 'blue'],
        'in_progress' => ['label' => 'In Bearbeitung',  'color' => 'indigo'],
        'completed'   => ['label' => 'Abgeschlossen',   'color' => 'green'],
        'cancelled'   => ['label' => 'Abgebrochen',     'color' => 'red'],
    ];

    public const PRIORITIES = [
        'low'      => ['label' => 'Niedrig',  'color' => 'gray'],
        'medium'   => ['label' => 'Mittel',   'color' => 'yellow'],
        'high'     => ['label' => 'Hoch',     'color' => 'orange'],
        'critical' => ['label' => 'Kritisch', 'color' => 'red'],
    ];

    public const TYPES = [
        'task'        => ['label' => 'Aufgabe',    'icon' => 'ph-check-square'],
        'maintenance' => ['label' => 'Wartung',    'icon' => 'ph-wrench'],
    ];

    // ── Relationen ───────────────────────────────────────────────────────────

    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
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

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type]['label'] ?? ucfirst($this->type);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    // ── Nummer generieren ────────────────────────────────────────────────────

    public static function generateNumber(): string
    {
        $last = static::orderByDesc('id')->value('number');
        $seq  = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'TSK-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ── Hilfsmethoden zur Synchronisation ───────────────────────────────────

    /**
     * Erstellt oder aktualisiert einen ServiceTask für ein Task-Objekt.
     */
    public static function syncFromTask(Task $task): self
    {
        $kanbanToStatus = [
            'ready'     => 'open',
            'wip'       => 'in_progress',
            'testing'   => 'in_progress',
            'completed' => 'completed',
        ];

        $customerId = $task->project?->customer_id ?? null;

        return static::updateOrCreate(
            ['taskable_type' => Task::class, 'taskable_id' => $task->id],
            [
                'number'      => static::where('taskable_type', Task::class)
                                       ->where('taskable_id', $task->id)
                                       ->value('number') ?? static::generateNumber(),
                'title'       => $task->title,
                'description' => $task->description,
                'type'        => 'task',
                'status'      => $kanbanToStatus[$task->kanban_status] ?? 'open',
                'priority'    => $task->priority ?? 'medium',
                'project_id'  => $task->project_id,
                'customer_id' => $customerId,
                'assigned_to' => $task->assigned_to,
                'due_date'    => $task->due_date,
            ]
        );
    }

    /**
     * Erstellt oder aktualisiert einen ServiceTask für ein MaintenanceEvent-Objekt.
     */
    public static function syncFromMaintenance(MaintenanceEvent $event): self
    {
        $customerId = $event->project?->customer_id ?? null;

        return static::updateOrCreate(
            ['taskable_type' => MaintenanceEvent::class, 'taskable_id' => $event->id],
            [
                'number'      => static::where('taskable_type', MaintenanceEvent::class)
                                       ->where('taskable_id', $event->id)
                                       ->value('number') ?? static::generateNumber(),
                'title'       => $event->title,
                'description' => $event->description,
                'type'        => 'maintenance',
                'status'      => $event->is_done ? 'completed' : 'open',
                'priority'    => $event->priority ?? 'medium',
                'project_id'  => $event->project_id,
                'customer_id' => $customerId,
                'assigned_to' => $event->assigned_to,
                'due_date'    => $event->scheduled_date,
            ]
        );
    }

    /**
     * Initialer Sync aller bestehenden Tasks und MaintenanceEvents.
     * Aufruf: php artisan tinker --execute="App\Models\ServiceTask::initialSync()"
     */
    public static function initialSync(): void
    {
        Task::with('project')->chunk(100, function ($tasks) {
            foreach ($tasks as $task) {
                static::syncFromTask($task);
            }
        });

        MaintenanceEvent::with('project')->chunk(100, function ($events) {
            foreach ($events as $event) {
                static::syncFromMaintenance($event);
            }
        });
    }
}
