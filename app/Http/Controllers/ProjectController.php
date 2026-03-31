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
            'todos',
            'quote',
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

    /**
     * Gibt die offenen Tasks eines Projekts als JSON zurück.
     * Wird vom Zeiterfassungs-Formular und Timer-Widget per fetch() genutzt.
     */
    public function tasksJson(Project $project)
    {
        $tasks = $project->tasks()
            ->with('workCategory')
            ->where('kanban_status', '!=', 'completed')
            ->select('id', 'title', 'work_category_id', 'description')
            ->get()
            ->map(fn ($t) => [
                'id'               => $t->id,
                'title'            => $t->title,
                'work_category_id' => $t->work_category_id,
                'description'      => $t->description,
            ]);

        return response()->json($tasks);
    }
}
