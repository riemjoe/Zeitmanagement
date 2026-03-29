<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimeEntry;
use App\Models\WorkCategory;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $query = TimeEntry::with(['project.customer', 'workCategory'])
            ->orderByDesc('date')
            ->orderByDesc('id');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('work_category_id', $request->category_id);
        }
        if ($request->filled('from')) {
            $query->where('date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->where('date', '<=', $request->to);
        }

        $entries    = $query->paginate(25)->withQueryString();
        $projects   = Project::orderBy('name')->get();
        $categories = WorkCategory::orderBy('name')->get();

        return view('time-entries.index', compact('entries', 'projects', 'categories'));
    }

    public function create(Request $request)
    {
        $projects   = Project::with('customer')->where('status', 'active')->orderBy('name')->get();
        $categories = WorkCategory::orderBy('name')->get();
        $preselect  = $request->project_id;
        return view('time-entries.create', compact('projects', 'categories', 'preselect'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'work_category_id' => 'required|exists:work_categories,id',
            'date'             => 'required|date',
            'hours'            => 'required|numeric|min:0.01|max:24',
            'description'      => 'nullable|string',
        ]);

        TimeEntry::create($data);

        if ($request->filled('redirect_to')) {
            return redirect($request->redirect_to)->with('success', 'Zeiteintrag wurde gespeichert.');
        }
        return redirect()->route('time-entries.index')->with('success', 'Zeiteintrag wurde gespeichert.');
    }

    public function edit(TimeEntry $timeEntry)
    {
        $projects   = Project::with('customer')->orderBy('name')->get();
        $categories = WorkCategory::orderBy('name')->get();
        return view('time-entries.edit', compact('timeEntry', 'projects', 'categories'));
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        $data = $request->validate([
            'project_id'       => 'required|exists:projects,id',
            'work_category_id' => 'required|exists:work_categories,id',
            'date'             => 'required|date',
            'hours'            => 'required|numeric|min:0.01|max:24',
            'description'      => 'nullable|string',
        ]);

        $timeEntry->update($data);
        return redirect()->route('time-entries.index')->with('success', 'Zeiteintrag wurde aktualisiert.');
    }

    public function destroy(TimeEntry $timeEntry)
    {
        if ($timeEntry->invoices()->exists()) {
            return back()->with('error', 'Zeiteintrag kann nicht gelöscht werden – er ist bereits abgerechnet.');
        }
        $timeEntry->delete();
        return redirect()->route('time-entries.index')->with('success', 'Zeiteintrag wurde gelöscht.');
    }
}
