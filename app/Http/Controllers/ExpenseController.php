<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Project;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with('project.customer')->orderByDesc('date');

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        $expenses = $query->paginate(25)->withQueryString();
        $projects = Project::orderBy('name')->get();

        return view('expenses.index', compact('expenses', 'projects'));
    }

    public function create(Request $request)
    {
        $projects  = Project::with('customer')->where('status', 'active')->orderBy('name')->get();
        $preselect = $request->project_id;
        return view('expenses.create', compact('projects', 'preselect'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string',
        ]);

        Expense::create($data);
        return redirect()->route('expenses.index')->with('success', 'Ausgabe wurde gespeichert.');
    }

    public function edit(Expense $expense)
    {
        $projects = Project::with('customer')->orderBy('name')->get();
        return view('expenses.edit', compact('expense', 'projects'));
    }

    public function update(Request $request, Expense $expense)
    {
        $data = $request->validate([
            'project_id'  => 'required|exists:projects,id',
            'date'        => 'required|date',
            'description' => 'required|string|max:255',
            'category'    => 'nullable|string|max:100',
            'amount'      => 'required|numeric|min:0.01',
            'notes'       => 'nullable|string',
        ]);

        $expense->update($data);
        return redirect()->route('expenses.index')->with('success', 'Ausgabe wurde aktualisiert.');
    }

    public function destroy(Expense $expense)
    {
        if ($expense->invoices()->exists()) {
            return back()->with('error', 'Ausgabe kann nicht gelöscht werden – sie ist bereits abgerechnet.');
        }
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Ausgabe wurde gelöscht.');
    }
}
