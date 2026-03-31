<?php

namespace App\Models;

use App\Mail\NewTaskCreated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Mail;

class RecurringTask extends Model
{
    protected $fillable = [
        'project_id', 'assigned_to', 'title', 'description',
        'priority', 'kanban_status', 'frequency', 'frequency_interval',
        'day_of_week', 'day_of_month', 'due_days_offset', 'time_of_day',
        'is_active', 'is_maintenance', 'last_run_at', 'next_run_at',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'is_maintenance'     => 'boolean',
        'frequency_interval' => 'integer',
        'day_of_week'        => 'integer',
        'day_of_month'       => 'integer',
        'due_days_offset'    => 'integer',
        'last_run_at'        => 'datetime',
        'next_run_at'        => 'datetime',
    ];

    // ── Beziehungen ────────────────────────────────────────────────────────────

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    // ── Lesbare Labels ─────────────────────────────────────────────────────────

    public function getFrequencyLabelAttribute(): string
    {
        $interval = $this->frequency_interval;

        return match ($this->frequency) {
            'daily'   => $interval === 1 ? 'Täglich'        : "Alle {$interval} Tage",
            'weekly'  => $interval === 1 ? 'Wöchentlich'    : "Alle {$interval} Wochen",
            'monthly' => $interval === 1 ? 'Monatlich'      : "Alle {$interval} Monate",
            default   => ucfirst($this->frequency),
        };
    }

    public function getScheduleSummaryAttribute(): string
    {
        $weekdays = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'];
        $timeStr  = $this->time_of_day ? ' · ' . substr($this->time_of_day, 0, 5) . ' Uhr' : '';

        return match ($this->frequency) {
            'daily'   => $this->frequency_label . $timeStr,
            'weekly'  => $this->frequency_label
                . ($this->day_of_week !== null ? ' · ' . $weekdays[$this->day_of_week] : '')
                . $timeStr,
            'monthly' => $this->frequency_label
                . ($this->day_of_month ? ' · ' . $this->day_of_month . '. des Monats' : '')
                . $timeStr,
            default   => $this->frequency_label . $timeStr,
        };
    }

    // ── next_run_at berechnen ──────────────────────────────────────────────────

    /**
     * Berechnet und setzt next_run_at basierend auf dem Startpunkt.
     * Wird beim Erstellen und nach jeder Ausführung aufgerufen.
     */
    public function calculateNextRun(?Carbon $from = null): Carbon
    {
        $from = $from ?? now();

        $next = match ($this->frequency) {
            'daily'   => $this->nextDaily($from),
            'weekly'  => $this->nextWeekly($from),
            'monthly' => $this->nextMonthly($from),
            default   => $from->copy()->addDay()->startOfDay(),
        };

        // Uhrzeit anwenden (Standard 06:00 falls nicht gesetzt)
        $time = $this->time_of_day ?? '06:00:00';
        [$h, $m] = array_map('intval', explode(':', $time));
        $next->setTime($h, $m, 0);

        return $next;
    }

    private function nextDaily(Carbon $from): Carbon
    {
        return $from->copy()->startOfDay()->addDays($this->frequency_interval);
    }

    private function nextWeekly(Carbon $from): Carbon
    {
        $target = $this->day_of_week ?? $from->dayOfWeek;
        $next   = $from->copy()->startOfDay();

        // Nächsten passenden Wochentag finden (mindestens 1 Tag in der Zukunft)
        do {
            $next->addDay();
        } while ($next->dayOfWeek !== $target);

        // Wenn interval > 1: weitere Wochen draufaddieren
        if ($this->frequency_interval > 1) {
            $next->addWeeks($this->frequency_interval - 1);
        }

        return $next;
    }

    private function nextMonthly(Carbon $from): Carbon
    {
        $day  = $this->day_of_month ?? $from->day;
        $next = $from->copy()->startOfDay()->addMonths($this->frequency_interval);

        // Sicherstellen, dass der Tag im Monat existiert (z.B. 31. im Feb)
        $next->day = min($day, $next->daysInMonth);

        return $next;
    }

    // ── Task-Instanz aus Template erstellen ───────────────────────────────────

    public function spawnTask(): Task
    {
        $maxPos = Task::where('project_id', $this->project_id)
                      ->where('kanban_status', $this->kanban_status)
                      ->max('position') ?? -1;

        $dueDate = $this->due_days_offset > 0
            ? now()->addDays($this->due_days_offset)->toDateString()
            : null;

        $task = Task::create([
            'project_id'    => $this->project_id,
            'assigned_to'   => $this->assigned_to,
            'title'         => $this->title,
            'description'   => $this->description,
            'priority'      => $this->priority,
            'kanban_status' => $this->kanban_status,
            'position'      => $maxPos + 1,
            'due_date'      => $dueDate,
        ]);

        // Relations für die Mail vorladen
        $task->load(['project', 'assignedUser']);

        $this->dispatchTaskMail($task);

        return $task;
    }

    /**
     * Versendet E-Mail-Benachrichtigungen nach der Task-Erstellung.
     *
     * – Direkt zugewiesen: nur an den zugewiesenen Nutzer.
     * – Nicht zugewiesen: an alle aktiven Mitarbeiter.
     */
    // ── Kalender-Hilfsmethode ─────────────────────────────────────────────────

    /**
     * Berechnet alle Ausführungstermine dieser Vorlage innerhalb eines Monats.
     * Wird vom Wartungsplan-Kalender genutzt, um alle Vorkommen anzuzeigen.
     *
     * @return Carbon[]  Array von Carbon-Instanzen (eine pro Vorkommen im Monat)
     */
    public function occurrencesInMonth(Carbon $monthStart): array
    {
        if (!$this->next_run_at || !$this->is_active) {
            return [];
        }

        $monthEnd = $monthStart->copy()->endOfMonth();
        $cursor   = $this->next_run_at->copy();
        $maxIter  = 500; // Sicherheitslimit gegen Endlosschleifen

        // Cursor bis zum Monatsbeginn vorspulen
        $i = 0;
        while ($cursor->lt($monthStart) && $i++ < $maxIter) {
            $cursor = $this->calculateNextRun($cursor);
        }

        // Alle Vorkommen innerhalb des Monats sammeln
        $dates = [];
        $i     = 0;
        while ($cursor->lte($monthEnd) && $i++ < $maxIter) {
            $dates[] = $cursor->copy();
            $cursor  = $this->calculateNextRun($cursor);
        }

        return $dates;
    }

    private function dispatchTaskMail(Task $task): void
    {
        try {
            if ($task->assigned_to && $task->assignedUser) {
                // Direkt zugewiesen → nur diese Person benachrichtigen
                Mail::to($task->assignedUser->email)
                    ->send(new NewTaskCreated($task, isAssigned: true));
            } else {
                // Nicht zugewiesen → alle aktiven Nutzer benachrichtigen
                $recipients = \App\Models\User::where('is_active', true)
                    ->whereNotNull('email')
                    ->get();

                foreach ($recipients as $user) {
                    Mail::to($user->email)
                        ->send(new NewTaskCreated($task, isAssigned: false));
                }
            }
        } catch (\Throwable $e) {
            // Mail-Fehler dürfen den Task-Erstellungsprozess nicht unterbrechen.
            \Illuminate\Support\Facades\Log::warning(
                'NewTaskCreated mail konnte nicht gesendet werden: ' . $e->getMessage(),
                ['task_id' => $task->id]
            );
        }
    }
}
