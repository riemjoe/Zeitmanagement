<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $showArchived = $request->boolean('archived');
        $projects = Project::with('customer')
            ->withSum('timeEntries', 'hours')
            ->where('is_archived', $showArchived)
            ->orderByDesc('created_at')
            ->get();
        return view('projects.index', compact('projects', 'showArchived'));
    }

    /** Projekt archivieren. */
    public function archive(Project $project)
    {
        $project->update(['is_archived' => true]);
        return back()->with('success', 'Projekt wurde archiviert.');
    }

    /** Projekt aus dem Archiv wiederherstellen. */
    public function unarchive(Project $project)
    {
        $project->update(['is_archived' => false]);
        return back()->with('success', 'Projekt wurde wiederhergestellt.');
    }

    public function create()
    {
        $customers   = Customer::orderBy('name')->get();
        $defaultRate = Setting::get('hourly_rate', 80);
        return view('projects.create', compact('customers', 'defaultRate'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'status'        => 'required|in:active,paused,completed',
            'notes'         => 'nullable|string',
            'budget_hours'  => 'nullable|numeric|min:0',
            'budget_amount' => 'nullable|numeric|min:0',
            'deadline'      => 'nullable|date',
        ]);

        $data['hourly_rate']   = $data['hourly_rate']   ?: null;
        $data['budget_hours']  = $data['budget_hours']  ?: null;
        $data['budget_amount'] = $data['budget_amount'] ?: null;

        Project::create($data);
        return redirect()->route('projects.index')->with('success', 'Projekt wurde angelegt.');
    }

    public function show(Project $project)
    {
        $project->load([
            'customer',
            'timeEntries.workCategory',
            'timeEntries.invoices',
            'expenses',
            'todos.timeEntries',
            'quote',
            'files',
            'milestones',
        ]);
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        $customers   = Customer::orderBy('name')->get();
        $defaultRate = Setting::get('hourly_rate', 80);
        return view('projects.edit', compact('project', 'customers', 'defaultRate'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'customer_id'   => 'required|exists:customers,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'status'        => 'required|in:active,paused,completed',
            'notes'         => 'nullable|string',
            'budget_hours'  => 'nullable|numeric|min:0',
            'budget_amount' => 'nullable|numeric|min:0',
            'deadline'      => 'nullable|date',
        ]);

        $data['hourly_rate']   = $data['hourly_rate']   ?: null;
        $data['budget_hours']  = $data['budget_hours']  ?: null;
        $data['budget_amount'] = $data['budget_amount'] ?: null;

        $project->update($data);
        return redirect()->route('projects.show', $project)->with('success', 'Projekt wurde aktualisiert.');
    }

    public function destroy(Project $project)
    {
        if ($project->timeEntries()->exists() || $project->expenses()->exists()) {
            return back()->with('error', 'Projekt kann nicht gelöscht werden – es existieren noch Zeiteinträge oder Ausgaben.');
        }
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Projekt wurde gelöscht.');
    }

    /** Gantt-Ansicht für ein Projekt. */
    public function gantt(Project $project)
    {
        $project->load(['tasks.workCategory', 'milestones']);
        return view('projects.gantt', compact('project'));
    }

    /** Burndown-Ansicht für ein Projekt. */
    public function burndown(Project $project)
    {
        $project->load(['timeEntries', 'tasks']);

        // Buchungen pro Tag summieren
        $entries = $project->timeEntries()
            ->selectRaw('date, SUM(hours) as hours')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Kumulierte tatsächliche Stunden
        $cumulative   = [];
        $running      = 0;
        foreach ($entries as $e) {
            $running += (float) $e->hours;
            // date kann ein Carbon-Objekt sein → als String verwenden
            $dateKey = $e->date instanceof \Carbon\Carbon
                ? $e->date->format('Y-m-d')
                : (string) $e->date;
            $cumulative[$dateKey] = $running;
        }

        // Ideallinie: Budget-Stunden gleichmäßig über Projektlaufzeit
        $budgetHours = (float) $project->budget_hours;
        $start       = $project->created_at->toDateString();
        $end         = $project->deadline?->toDateString() ?? now()->toDateString();

        $chartData = [
            'labels'      => [],
            'actual'      => [],
            'ideal'       => [],
            'budget'      => $budgetHours,
        ];

        if ($budgetHours > 0) {
            $startDate  = new \DateTime($start);
            $endDate    = new \DateTime($end);
            $diffDays   = max(1, (int) $startDate->diff($endDate)->days);
            $dailyIdeal = $budgetHours / $diffDays;
            $today      = new \DateTime();
            $limit      = $endDate < $today ? $endDate : $today;

            $day          = clone $startDate;
            $idealRunning = 0;
            $lastKnown    = 0;

            while ($day <= $limit) {
                $dateStr = $day->format('Y-m-d');
                $chartData['labels'][] = $day->format('d.m.');

                // Kumulierten Wert aus vorberechneter Map holen, letzten bekannten Wert carry-forwarden
                if (isset($cumulative[$dateStr])) {
                    $lastKnown = $cumulative[$dateStr];
                }
                $chartData['actual'][] = round($lastKnown, 2);

                $idealRunning += $dailyIdeal;
                $chartData['ideal'][] = round(min($idealRunning, $budgetHours), 2);

                $day->modify('+1 day');
            }
        }

        return view('projects.burndown', compact('project', 'chartData'));
    }

    /**
     * Gibt die offenen Tasks eines Projekts als JSON zurück.
     * Wird vom Zeiterfassungs-Formular und Timer-Widget per fetch() genutzt.
     */
    public function tasksJson(Project $project)
    {
        $tasks = $project->tasks()
            ->with(['workCategory', 'timeEntries'])
            ->where('kanban_status', '!=', 'completed')
            ->select('id', 'title', 'work_category_id', 'description', 'budget_hours')
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'title'            => $t->title,
                'work_category_id' => $t->work_category_id,
                'description'      => $t->description,
                'budget_hours'     => $t->budget_hours,
                'tracked_hours'    => $t->tracked_hours,
            ]);

        return response()->json($tasks);
    }
}
