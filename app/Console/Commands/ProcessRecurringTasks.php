<?php

namespace App\Console\Commands;

use App\Models\MaintenanceEvent;
use App\Models\RecurringTask;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessRecurringTasks extends Command
{
    protected $signature   = 'tasks:process-recurring';
    protected $description = 'Erstellt fällige Tasks aus wiederkehrenden Aufgaben-Vorlagen';

    public function handle(): int
    {
        $due = RecurringTask::query()
            ->where('is_active', true)
            ->where('next_run_at', '<=', now())
            ->with('project')
            ->get();

        if ($due->isEmpty()) {
            $this->info('Keine fälligen Vorlagen gefunden.');
            return self::SUCCESS;
        }

        $created = 0;

        foreach ($due as $template) {
            try {
                $task = $template->spawnTask();

                // Wartungsaufgabe → MaintenanceEvent im Kalender eintragen
                if ($template->is_maintenance) {
                    MaintenanceEvent::create([
                        'project_id'        => $template->project_id,
                        'assigned_to'       => $template->assigned_to,
                        'title'             => $template->title,
                        'description'       => $template->description,
                        'scheduled_date'    => $task->due_date ?? now()->toDateString(),
                        'scheduled_time'    => $template->time_of_day,
                        'priority'          => $template->priority,
                        'recurring_task_id' => $template->id,
                        'notify'            => true,
                    ]);
                }

                $next = $template->calculateNextRun();

                $template->update([
                    'last_run_at' => now(),
                    'next_run_at' => $next,
                ]);

                $this->line("  ✓ [{$template->project->name}] {$template->title} → Task #{$task->id}, nächste Ausführung: {$next->format('d.m.Y')}");
                $created++;

            } catch (\Throwable $e) {
                $this->error("  ✗ Vorlage #{$template->id} ({$template->title}): {$e->getMessage()}");
                Log::error("recurring-tasks: Vorlage #{$template->id} fehlgeschlagen", ['error' => $e->getMessage()]);
            }
        }

        $this->info("Fertig: {$created} Task(s) erstellt.");

        return self::SUCCESS;
    }
}
