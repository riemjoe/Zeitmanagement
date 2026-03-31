<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceEvent extends Model
{
    protected $fillable = [
        'project_id', 'assigned_to', 'title', 'description',
        'scheduled_date', 'scheduled_time', 'priority',
        'recurring_task_id', 'is_done', 'done_at', 'notify', 'notified_at',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'is_done'        => 'boolean',
        'notify'         => 'boolean',
        'done_at'        => 'datetime',
        'notified_at'    => 'datetime',
    ];

    // ── Beziehungen ──────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    /**
     * Ist das Ereignis überfällig?
     *
     * - Vergangene Tage (vor heute) → immer überfällig
     * - Heute ohne scheduled_time → NICHT überfällig (noch aktuell)
     * - Heute mit scheduled_time → überfällig sobald die Uhrzeit vergangen ist
     * - Zukunft → nie überfällig
     *
     * Achtung: scheduled_date->isPast() schlägt für "heute" ebenfalls an,
     * weil Carbon mitternacht-basiert prüft. Daher explizit isToday() abfragen.
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->is_done) {
            return false;
        }

        $date = $this->scheduled_date;

        // Heute
        if ($date->isToday()) {
            // Ohne Uhrzeit → noch nicht überfällig
            if (! $this->scheduled_time) {
                return false;
            }
            // Mit Uhrzeit → überfällig wenn Uhrzeit bereits vergangen
            return substr($this->scheduled_time, 0, 5) < now()->format('H:i');
        }

        // Vergangene Tage (vor heute)
        return $date->startOfDay()->lt(now()->startOfDay());
    }

    /** Prioritäts-Label */
    public function getPriorityLabelAttribute(): string
    {
        return match ($this->priority) {
            'low'    => 'Niedrig',
            'medium' => 'Mittel',
            'high'   => 'Hoch',
            default  => ucfirst($this->priority),
        };
    }

    /** Anzeige-Uhrzeit formatiert (HH:MM) */
    public function getTimeDisplayAttribute(): ?string
    {
        return $this->scheduled_time ? substr($this->scheduled_time, 0, 5) : null;
    }

    /**
     * Gibt alle Events zurück, die eine Benachrichtigung brauchen.
     *
     * Regeln:
     *  - notify = true, notified_at = null, is_done = false
     *  - scheduled_date < heute  → immer fällig (überfällig)
     *  - scheduled_date = heute  → fällig wenn scheduled_time IS NULL oder scheduled_time <= jetzt
     *
     * Hinweis: whereDate() wird verwendet, damit SQLite-gespeicherte Datumswerte
     * im Format 'Y-m-d H:i:s' korrekt verglichen werden (String-Vergleich sonst
     * unzuverlässig). Zeitvergleich bleibt als String-Vergleich – beide Seiten
     * sind H:i(:s)-formatiert und damit lexikografisch sortierbar.
     */
    public static function dueForNotification()
    {
        $today   = now()->toDateString();   // 'Y-m-d'
        $nowTime = now()->format('H:i:s');  // 'H:i:s' – passt zu H:i-gespeicherten Werten (lexikografisch korrekt)

        return static::with(['project', 'assignedUser'])
            ->where('notify', true)
            ->whereNull('notified_at')
            ->where('is_done', false)
            ->where(function ($q) use ($today, $nowTime) {
                // Vergangene Tage – immer fällig
                $q->whereDate('scheduled_date', '<', $today)
                  // Heute ohne Uhrzeit – sofort fällig
                  ->orWhere(function ($q2) use ($today) {
                      $q2->whereDate('scheduled_date', $today)
                         ->whereNull('scheduled_time');
                  })
                  // Heute mit Uhrzeit – fällig sobald die Zeit erreicht ist
                  ->orWhere(function ($q2) use ($today, $nowTime) {
                      $q2->whereDate('scheduled_date', $today)
                         ->whereRaw("time(scheduled_time) <= time(?)", [$nowTime]);
                  });
            })
            ->get();
    }
}
