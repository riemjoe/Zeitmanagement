<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectTodo;
use Illuminate\Http\Request;

class ProjectTodoController extends Controller
{
    /**
     * Neues ToDo für ein Projekt anlegen.
     */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $maxOrder = $project->todos()->max('sort_order') ?? -1;

        $todo = ProjectTodo::create([
            'project_id'  => $project->id,
            'title'       => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $maxOrder + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json($todo);
        }

        return back()->with('success', 'ToDo hinzugefügt.');
    }

    /**
     * Abgeschlossen-Status umschalten (AJAX).
     */
    public function toggle(ProjectTodo $todo)
    {
        $todo->completed    = !$todo->completed;
        $todo->completed_at = $todo->completed ? now() : null;
        $todo->save();

        return response()->json([
            'id'           => $todo->id,
            'completed'    => $todo->completed,
            'completed_at' => $todo->completed_at?->toISOString(),
        ]);
    }

    /**
     * ToDo löschen.
     */
    public function destroy(ProjectTodo $todo)
    {
        $project = $todo->project;
        $todo->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }
        return back()->with('success', 'ToDo gelöscht.');
    }

    /**
     * Reihenfolge aktualisieren (AJAX).
     */
    public function reorder(Request $request)
    {
        $ids = $request->validate(['ids' => 'required|array', 'ids.*' => 'integer'])['ids'];
        foreach ($ids as $order => $id) {
            ProjectTodo::where('id', $id)->update(['sort_order' => $order]);
        }
        return response()->json(['ok' => true]);
    }
}
