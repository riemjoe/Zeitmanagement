<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class ProjectTodoController extends Controller
{
    /**
     * Neue Aufgabe für ein Projekt anlegen.
     * POST /projects/{project}/todos
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:2000',
            'kanban_status' => 'nullable|in:ready,wip,testing,completed',
            'priority'      => 'nullable|in:low,medium,high',
        ]);

        $maxPos = $project->tasks()->max('position') ?? -1;

        $task = Task::create([
            'project_id'    => $project->id,
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'kanban_status' => $data['kanban_status'] ?? 'ready',
            'priority'      => $data['priority'] ?? 'medium',
            'position'      => $maxPos + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'id'            => $task->id,
                'title'         => $task->title,
                'description'   => $task->description,
                'kanban_status' => $task->kanban_status,
                'priority'      => $task->priority,
                'completed'     => $task->kanban_status === 'completed',
            ]);
        }

        return back()->with('success', 'Aufgabe hinzugefügt.');
    }

    /**
     * Kanban-Status einer Aufgabe umschalten (AJAX).
     * PATCH /todos/{todo}/toggle
     * Wechselt zwischen "completed" und "ready".
     */
    public function toggle(Task $todo)
    {
        $todo->kanban_status = $todo->kanban_status === 'completed' ? 'ready' : 'completed';
        $todo->save();

        return response()->json([
            'id'            => $todo->id,
            'completed'     => $todo->kanban_status === 'completed',
            'kanban_status' => $todo->kanban_status,
        ]);
    }

    /**
     * Aufgabe löschen.
     * DELETE /todos/{todo}
     */
    public function destroy(Task $todo)
    {
        $todo->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }

        return back()->with('success', 'Aufgabe gelöscht.');
    }

    /**
     * Reihenfolge aktualisieren (AJAX).
     * POST /todos/reorder
     */
    public function reorder(Request $request)
    {
        $ids = $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer',
        ])['ids'];

        foreach ($ids as $order => $id) {
            Task::where('id', $id)->update(['position' => $order]);
        }

        return response()->json(['ok' => true]);
    }
}
