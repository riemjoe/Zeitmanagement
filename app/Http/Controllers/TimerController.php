<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Timer;
use App\Models\TimeEntry;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimerController extends Controller
{
    /** Aktuellen Timer-Status des eingeloggten Nutzers (für Alpine.js Polling). */
    public function status()
    {
        $timer = $this->activeTimer();

        if (!$timer) {
            return response()->json(['running' => false]);
        }

        return response()->json([
            'running'      => true,
            'paused'       => $timer->is_paused,
            'id'           => $timer->id,
            'started_at'   => $timer->started_at->toISOString(),
            'elapsed_s'    => $timer->elapsed_seconds,
            'project'      => $timer->project->name,
            'customer'     => $timer->project->customer->name,
            'category'     => $timer->workCategory->name,
            'description'  => $timer->description ?? '',
            'hourly_rate'  => $timer->project->effective_hourly_rate,
        ]);
    }

    /** Timer starten. */
    public function start(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'work_category_id' => 'required|exists:work_categories,id',
            'description'      => 'nullable|string|max:500',
        ]);

        // Ggf. laufenden Timer dieses Nutzers zuerst beenden
        $this->clearUserTimers();

        $timer = Timer::create([
            'user_id'          => Auth::id(),
            'project_id'       => $data['project_id'],
            'work_category_id' => $data['work_category_id'],
            'started_at'       => now(),
            'description'      => $data['description'] ?? null,
        ]);

        $timer->load(['project.customer', 'workCategory']);

        return response()->json([
            'running'     => true,
            'id'          => $timer->id,
            'started_at'  => $timer->started_at->toISOString(),
            'elapsed_s'   => 0,
            'project'     => $timer->project->name,
            'customer'    => $timer->project->customer->name,
            'category'    => $timer->workCategory->name,
            'description' => $timer->description ?? '',
            'hourly_rate' => $timer->project->effective_hourly_rate,
        ]);
    }

    /** Timer pausieren. */
    public function pause()
    {
        $timer = $this->activeTimer();

        if (!$timer) {
            return response()->json(['error' => 'Kein aktiver Timer'], 404);
        }
        if ($timer->is_paused) {
            return response()->json(['error' => 'Timer ist bereits pausiert'], 422);
        }

        $timer->update(['paused_at' => now()]);

        return response()->json([
            'running'   => true,
            'paused'    => true,
            'elapsed_s' => $timer->fresh()->elapsed_seconds,
        ]);
    }

    /** Pausierten Timer fortsetzen. */
    public function resume()
    {
        $timer = $this->activeTimer();

        if (!$timer) {
            return response()->json(['error' => 'Kein aktiver Timer'], 404);
        }
        if (!$timer->is_paused) {
            return response()->json(['error' => 'Timer ist nicht pausiert'], 422);
        }

        $pauseDuration = (int) now()->diffInSeconds($timer->paused_at);
        $timer->update([
            'paused_seconds' => $timer->paused_seconds + $pauseDuration,
            'paused_at'      => null,
        ]);

        return response()->json([
            'running'   => true,
            'paused'    => false,
            'elapsed_s' => $timer->fresh()->elapsed_seconds,
        ]);
    }

    /** Timer stoppen und als Zeiteintrag speichern. */
    public function stop(Request $request)
    {
        $timer = $this->activeTimer();

        if (!$timer) {
            return response()->json(['error' => 'Kein aktiver Timer'], 404);
        }

        $rawHours = $timer->elapsed_seconds / 3600;
        $hours    = max(round($rawHours / 0.25) * 0.25, 0.25);
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

    /** Timer abbrechen ohne Zeiteintrag. */
    public function cancel()
    {
        $this->clearUserTimers();
        return response()->json(['running' => false]);
    }

    // ── Private Hilfsmethoden ────────────────────────────────────────────

    /** Aktiven Timer des eingeloggten Nutzers laden. */
    private function activeTimer(): ?Timer
    {
        return Timer::with(['project.customer', 'workCategory'])
            ->where('user_id', Auth::id())
            ->first();
    }

    /** Alle Timer des eingeloggten Nutzers löschen. */
    private function clearUserTimers(): void
    {
        Timer::where('user_id', Auth::id())->delete();
    }
}
