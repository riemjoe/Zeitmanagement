<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\WorkCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KanbanController extends Controller
{
    private const STATUSES = ['ready', 'wip', 'testing', 'completed'];

    /** Kanban-Board anzeigen */
    public function index(Request $request)
    {
        $projectFilter = $request->query('project_id');

        $query = Task::with(['project.customer', 'assignedUser'])
            ->orderBy('position')
            ->orderBy('id');

        if ($projectFilter) {
            $query->where('project_id', $projectFilter);
        }

        // Abgeschlossene Aufgaben, die länger als eine Stunde in diesem Status sind, ausblenden
        $query->where(function ($q) {
            $q->where('kanban_status', '!=', 'completed')
              ->orWhere('updated_at', '>=', now()->subHour());
        });

        $tasks = $query->get()->groupBy('kanban_status');

        $columns = [];
        foreach (self::STATUSES as $status) {
            $columns[$status] = $tasks->get($status, collect());
        }

        $projects   = Project::with('customer')->where('is_archived', false)->orderBy('name')->get();
        $members    = User::where('is_active', true)->orderBy('name')->get();
        $categories = WorkCategory::orderBy('name')->get();

        return view('kanban.index', compact('columns', 'projects', 'members', 'categories', 'projectFilter'));
    }

    /** Status + Position einer Aufgabe per AJAX aktualisieren (Drag & Drop) */
    public function updateStatus(Request $request, Task $task)
    {
        $data = $request->validate([
            'kanban_status' => 'required|in:ready,wip,testing,completed',
            'position'      => 'required|integer|min:0',
            'siblings'      => 'nullable|array',
            'siblings.*'    => 'integer',
        ]);

        $task->update([
            'kanban_status' => $data['kanban_status'],
            'position'      => $data['position'],
        ]);

        // Positionen aller Geschwister in der Zielspalte aktualisieren
        if (!empty($data['siblings'])) {
            foreach ($data['siblings'] as $pos => $id) {
                if ((int) $id !== $task->id) {
                    Task::where('id', $id)->update(['position' => $pos]);
                }
            }
        }

        return response()->json(['ok' => true]);
    }

    /** Neue Aufgabe anlegen */
    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'priority'         => 'required|in:low,medium,high',
            'kanban_status'    => 'required|in:ready,wip,testing,completed',
            'assigned_to'      => 'nullable|exists:users,id',
            'due_date'         => 'nullable|date',
            'work_category_id' => 'nullable|exists:work_categories,id',
            'budget_hours'     => 'nullable|numeric|min:0.25|max:9999',
        ]);

        // Position ans Ende der Zielspalte setzen
        $maxPos = Task::where('kanban_status', $data['kanban_status'])->max('position') ?? -1;
        $data['position'] = $maxPos + 1;

        Task::create($data);

        return redirect()->back()->with('success', 'Aufgabe wurde angelegt.');
    }

    /** Aufgabe bearbeiten */
    public function update(Request $request, Task $task)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string|max:2000',
            'priority'         => 'required|in:low,medium,high',
            'assigned_to'      => 'nullable|exists:users,id',
            'due_date'         => 'nullable|date',
            'work_category_id' => 'nullable|exists:work_categories,id',
            'budget_hours'     => 'nullable|numeric|min:0.25|max:9999',
        ]);

        $task->update($data);

        return redirect()->back()->with('success', 'Aufgabe wurde aktualisiert.');
    }

    /** Aufgabe löschen */
    public function destroy(Task $task)
    {
        $task->delete();
        return redirect()->back()->with('success', 'Aufgabe wurde gelöscht.');
    }
}
