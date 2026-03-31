<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceEvent;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    /** Wartungsplan-Kalender für ein Projekt anzeigen */
    public function index(Request $request, Project $project)
    {
        // Monat bestimmen (Standard: aktueller Monat)
        $year  = (int) $request->get('year',  now()->year);
        $month = (int) $request->get('month', now()->month);

        $from = \Carbon\Carbon::create($year, $month, 1)->startOfDay();
        $to   = $from->copy()->endOfMonth()->endOfDay();

        // Einmalige Ereignisse dieses Monats
        // Hinweis: whereDate() statt whereBetween, da SQLite Datumswerte als
        // 'Y-m-d H:i:s' speichern kann und der String-Vergleich beim letzten
        // Monatstag sonst fehlschlägt ('2026-03-31 00:00:00' > '2026-03-31').
        $events = MaintenanceEvent::with(['assignedUser', 'recurringTask'])
            ->where('project_id', $project->id)
            ->whereDate('scheduled_date', '>=', $from->toDateString())
            ->whereDate('scheduled_date', '<=', $to->toDateString())
            ->orderBy('scheduled_date')
            ->orderBy('scheduled_time')
            ->get();

        // Nur als Wartung markierte, aktive wiederkehrende Aufgaben für Kalender-Vorschau
        $recurringTasks = $project->recurringTasks()
            ->where('is_active', true)
            ->where('is_maintenance', true)
            ->get();

        // Kalender-Grid aufbauen
        $calendar = $this->buildCalendar($from, $events, $recurringTasks);

        $members = User::where('is_active', true)->orderBy('name')->get();

        return view('maintenance.index', compact('project', 'calendar', 'events', 'members', 'year', 'month', 'from'));
    }

    /** Neues Ereignis anlegen */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'priority'       => 'required|in:low,medium,high',
            'assigned_to'    => 'nullable|exists:users,id',
            'notify'         => 'boolean',
        ]);

        $data['project_id'] = $project->id;
        $data['notify']     = $request->boolean('notify', true);

        MaintenanceEvent::create($data);

        // Expliziter Redirect statt back(), damit auch ohne Referer-Header der
        // korrekte Kalender-Monat angezeigt wird.
        return redirect()
            ->route('maintenance.index', array_filter([
                'project' => $project,
                'year'    => $request->input('_year'),
                'month'   => $request->input('_month'),
            ]))
            ->with('success', 'Wartungsereignis wurde geplant.');
    }

    /** Ereignis aktualisieren */
    public function update(Request $request, MaintenanceEvent $maintenanceEvent)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string|max:2000',
            'scheduled_date' => 'required|date',
            'scheduled_time' => 'nullable|date_format:H:i',
            'priority'       => 'required|in:low,medium,high',
            'assigned_to'    => 'nullable|exists:users,id',
            'notify'         => 'boolean',
        ]);

        $data['notify'] = $request->boolean('notify', true);

        // Wenn Datum geändert → Benachrichtigung zurücksetzen
        if ($maintenanceEvent->scheduled_date->toDateString() !== $data['scheduled_date']) {
            $data['notified_at'] = null;
        }

        $maintenanceEvent->update($data);

        return redirect()
            ->route('maintenance.index', array_filter([
                'project' => $maintenanceEvent->project_id,
                'year'    => $request->input('_year'),
                'month'   => $request->input('_month'),
            ]))
            ->with('success', 'Wartungsereignis wurde aktualisiert.');
    }

    /** Ereignis als erledigt markieren (Toggle) */
    public function toggle(MaintenanceEvent $maintenanceEvent)
    {
        $maintenanceEvent->update([
            'is_done' => !$maintenanceEvent->is_done,
            'done_at' => !$maintenanceEvent->is_done ? now() : null,
        ]);

        return back()->with('success', $maintenanceEvent->is_done ? 'Als erledigt markiert.' : 'Als offen markiert.');
    }

    /** Ereignis löschen */
    public function destroy(MaintenanceEvent $maintenanceEvent)
    {
        $maintenanceEvent->delete();
        return back()->with('success', 'Wartungsereignis wurde gelöscht.');
    }

    // ── Kalender-Grid ────────────────────────────────────────────────────────

    private function buildCalendar(\Carbon\Carbon $from, $events, $recurringTasks): array
    {
        $daysInMonth  = $from->daysInMonth;
        $firstWeekday = $from->copy()->startOfMonth()->dayOfWeek; // 0=So, 1=Mo
        // ISO: Woche beginnt Montag → Offset anpassen
        $offset = ($firstWeekday === 0) ? 6 : $firstWeekday - 1;

        // Events nach Tag gruppieren
        $byDay = $events->groupBy(fn ($e) => $e->scheduled_date->day);

        // Alle Vorkommen wiederkehrender Wartungsaufgaben im Monat berechnen
        $recurringByDay = [];
        foreach ($recurringTasks as $rt) {
            foreach ($rt->occurrencesInMonth($from) as $date) {
                $recurringByDay[$date->day][] = $rt;
            }
        }

        $weeks = [];
        $week  = array_fill(0, $offset, null); // Leer-Slots am Anfang

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $week[] = [
                'day'        => $day,
                'date'       => $from->copy()->day($day)->toDateString(),
                'isToday'    => $from->copy()->day($day)->isToday(),
                'isPast'     => $from->copy()->day($day)->isPast() && !$from->copy()->day($day)->isToday(),
                'events'     => $byDay->get($day, collect())->values(),
                'recurring'  => $recurringByDay[$day] ?? [],
            ];

            if (count($week) === 7) {
                $weeks[] = $week;
                $week    = [];
            }
        }

        // Letzte Woche auffüllen
        if (!empty($week)) {
            while (count($week) < 7) $week[] = null;
            $weeks[] = $week;
        }

        return $weeks;
    }
}
