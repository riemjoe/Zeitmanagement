<?php

namespace App\Http\Controllers;

use App\Models\WorkCategory;
use Illuminate\Http\Request;

class WorkCategoryController extends Controller
{
    public function index()
    {
        $categories = WorkCategory::withCount('timeEntries')->orderBy('name')->get();
        return view('work-categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:work_categories,name',
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);
        WorkCategory::create($data);
        return redirect()->route('work-categories.index')->with('success', 'Kategorie wurde angelegt.');
    }

    public function update(Request $request, WorkCategory $workCategory)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255|unique:work_categories,name,' . $workCategory->id,
            'color' => 'required|string|regex:/^#[0-9a-fA-F]{6}$/',
        ]);
        $workCategory->update($data);
        return redirect()->route('work-categories.index')->with('success', 'Kategorie wurde aktualisiert.');
    }

    public function destroy(WorkCategory $workCategory)
    {
        if ($workCategory->timeEntries()->exists()) {
            return back()->with('error', 'Kategorie kann nicht gelöscht werden – sie wird noch verwendet.');
        }
        $workCategory->delete();
        return redirect()->route('work-categories.index')->with('success', 'Kategorie wurde gelöscht.');
    }
}
