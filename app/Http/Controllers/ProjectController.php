<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('customer')
            ->withSum('timeEntries', 'hours')
            ->orderByDesc('created_at')
            ->get();
        return view('projects.index', compact('projects'));
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
            'customer_id' => 'required|exists:customers,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status'      => 'required|in:active,paused,completed',
            'notes'       => 'nullable|string',
        ]);

        // Leerer String → null (globalen Stundenlohn verwenden)
        $data['hourly_rate'] = $data['hourly_rate'] ?: null;

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
            'customer_id' => 'required|exists:customers,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'status'      => 'required|in:active,paused,completed',
            'notes'       => 'nullable|string',
        ]);

        $data['hourly_rate'] = $data['hourly_rate'] ?: null;

        $project->update($data);
        return redirect()->route('projects.index')->with('success', 'Projekt wurde aktualisiert.');
    }

    public function destroy(Project $project)
    {
        if ($project->timeEntries()->exists() || $project->expenses()->exists()) {
            return back()->with('error', 'Projekt kann nicht gelöscht werden – es existieren noch Zeiteinträge oder Ausgaben.');
        }
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Projekt wurde gelöscht.');
    }
}
