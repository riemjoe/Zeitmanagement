<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Timer;
use App\Models\TimeEntry;
use App\Models\WorkCategory;
use Illuminate\Http\Request;

class TimerController extends Controller
{
    /**
     * Aktuellen Timer-Status als JSON (für Alpine.js Polling).
     */
    public function status()
    {
        $timer = Timer::with(['project.customer', 'workCategory'])->first();

        if (!$timer) {
            return response()->json(['running' => false]);
        }

        return response()->json([
            'running'      => true,
            'id'           => $timer->id,
            'started_at'   => $timer->started_at->toISOString(),
            'elapsed_s'    => $timer->elapsed_seconds,
            'project'      => $timer->project->name,
            'customer'     => $timer->project->customer->name,
            'category'     => $timer->workCategory->name,
            'description'  => $timer->description ?? '',
        ]);
    }

    /**
     * Timer starten.
     */
    public function start(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'work_category_id' => 'required|exists:work_categories,id',
            'description'      => 'nullable|string|max:500',
        ]);

        // Ggf. laufenden Timer zuerst beenden
        Timer::truncate();

        $timer = Timer::create([
            'project_id'       => $data['project_id'],
            'work_category_id' => $data['work_category_id'],
            'started_at'       => now(),
            'description'      => $data['description'] ?? null,
        ]);

        return response()->json([
            'running'    => true,
            'id'         => $timer->id,
            'started_at' => $timer->started_at->toISOString(),
            'elapsed_s'  => 0,
        ]);
    }

    /**
     * Timer stoppen und als Zeiteintrag speichern.
     */
    public function stop(Request $request)
    {
        $timer = Timer::first();

        if (!$timer) {
            return response()->json(['error' => 'Kein aktiver Timer'], 404);
        }

        $hours = max(round($timer->elapsed_seconds / 3600, 2), 0.01);

        // Beschreibung aus Request oder aus Timer übernehmen
        $description = $request->input('description', $timer->description);

        $entry = TimeEntry::create([
            'project_id'       => $timer->project_id,
            'work_category_id' => $timer->work_category_id,
            'date'             => $timer->started_at->toDateString(),
            'hours'            => $hours,
            'description'      => $description,
        ]);

        $timer->delete();

        return response()->json([
            'running'       => false,
            'time_entry_id' => $entry->id,
            'hours'         => $hours,
        ]);
    }

    /**
     * Timer abbrechen ohne Zeiteintrag.
     */
    public function cancel()
    {
        Timer::truncate();
        return response()->json(['running' => false]);
    }
}
