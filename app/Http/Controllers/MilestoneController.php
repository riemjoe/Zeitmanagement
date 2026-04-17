<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;

class MilestoneController extends Controller
{
    /** POST /admin/projects/{project}/milestones */
    public function store(Request $request, Project $project)
    {
        $data = $request->validate([
            'title'       => 'required|string|max:255',
            'due_date'    => 'nullable|date',
            'description' => 'nullable|string|max:1000',
        ]);
        $data['project_id'] = $project->id;

        $milestone = Milestone::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'id'           => $milestone->id,
                'title'        => $milestone->title,
                'due_date'     => $milestone->due_date?->format('d.m.Y'),
                'due_date_raw' => $milestone->due_date?->format('Y-m-d'),
                'description'  => $milestone->description,
                'is_completed' => false,
                'is_overdue'   => $milestone->is_overdue,
            ], 201);
        }

        return back()->with('success', 'Meilenstein wurde angelegt.');
    }

    /** PATCH /admin/milestones/{milestone}/toggle */
    public function toggle(Milestone $milestone)
    {
        $milestone->update([
            'completed_at' => $milestone->is_completed ? null : now(),
        ]);

        return response()->json([
            'is_completed' => $milestone->fresh()->is_completed,
        ]);
    }

    /** DELETE /admin/milestones/{milestone} */
    public function destroy(Milestone $milestone)
    {
        $milestone->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true]);
        }
        return back()->with('success', 'Meilenstein wurde gelöscht.');
    }
}
