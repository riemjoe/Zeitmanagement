<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RecurringTask;
use App\Models\User;
use Illuminate\Http\Request;

class RecurringTaskController extends Controller
{
    /** Neue Vorlage anlegen */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string|max:2000',
            'priority'           => 'required|in:low,medium,high',
            'kanban_status'      => 'required|in:ready,wip,testing,completed',
            'assigned_to'        => 'nullable|exists:users,id',
            'frequency'          => 'required|in:daily,weekly,monthly',
            'frequency_interval' => 'required|integer|min:1|max:52',
            'day_of_week'        => 'nullable|integer|min:0|max:6',
            'day_of_month'       => 'nullable|integer|min:1|max:28',
            'due_days_offset'    => 'required|integer|min:0|max:365',
            'time_of_day'        => 'nullable|date_format:H:i',
            'is_maintenance'     => 'boolean',
        ]);

        $data['is_maintenance'] = $request->boolean('is_maintenance');
        $data['project_id']     = $project->id;

        // Ersten next_run_at berechnen
        $template             = new RecurringTask($data);
        $data['next_run_at']  = $template->calculateNextRun();

        RecurringTask::create($data);

        return back()->with('success', 'Wiederkehrende Aufgabe wurde gespeichert.');
    }

    /** Vorlage aktualisieren */
    public function update(Request $request, RecurringTask $recurringTask)
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'nullable|string|max:2000',
            'priority'           => 'required|in:low,medium,high',
            'kanban_status'      => 'required|in:ready,wip,testing,completed',
            'assigned_to'        => 'nullable|exists:users,id',
            'frequency'          => 'required|in:daily,weekly,monthly',
            'frequency_interval' => 'required|integer|min:1|max:52',
            'day_of_week'        => 'nullable|integer|min:0|max:6',
            'day_of_month'       => 'nullable|integer|min:1|max:28',
            'due_days_offset'    => 'required|integer|min:0|max:365',
            'time_of_day'        => 'nullable|date_format:H:i',
            'is_active'          => 'boolean',
            'is_maintenance'     => 'boolean',
        ]);

        // next_run_at neu berechnen wenn Zeitplan geändert wurde
        $scheduleChanged = $recurringTask->frequency          !== $data['frequency']
                        || $recurringTask->frequency_interval !== (int) $data['frequency_interval']
                        || $recurringTask->day_of_week        !== ($data['day_of_week'] ?? null)
                        || $recurringTask->day_of_month       !== ($data['day_of_month'] ?? null)
                        || $recurringTask->time_of_day        !== ($data['time_of_day'] ?? null);

        $recurringTask->fill($data);

        if ($scheduleChanged) {
            $recurringTask->next_run_at = $recurringTask->calculateNextRun();
        }

        $recurringTask->is_active      = $request->boolean('is_active');
        $recurringTask->is_maintenance = $request->boolean('is_maintenance');
        $recurringTask->save();

        return back()->with('success', 'Vorlage wurde aktualisiert.');
    }

    /** Vorlage löschen */
    public function destroy(RecurringTask $recurringTask)
    {
        $recurringTask->delete();
        return back()->with('success', 'Vorlage wurde gelöscht.');
    }

    /** Sofort manuell ausführen (einen Task jetzt erstellen) */
    public function runNow(RecurringTask $recurringTask)
    {
        $task = $recurringTask->spawnTask();

        $recurringTask->update([
            'last_run_at' => now(),
            'next_run_at' => $recurringTask->calculateNextRun(),
        ]);

        return back()->with('success', 'Aufgabe "' . $task->title . '" wurde sofort erstellt.');
    }
}
