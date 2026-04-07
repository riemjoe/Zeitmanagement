<?php

namespace App\Http\Controllers;

use App\Models\SupportCategory;
use App\Models\WorkCategory;
use Illuminate\Http\Request;

class SupportCategoryController extends Controller
{
    public function index()
    {
        $categories   = SupportCategory::with('workCategory')->orderBy('name')->get();
        $workCategories = WorkCategory::orderBy('name')->get();
        return view('helpdesk.categories', compact('categories', 'workCategories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'priority'         => 'required|in:low,medium,high,critical',
            'work_category_id' => 'nullable|exists:work_categories,id',
        ]);

        SupportCategory::create($data);

        return back()->with('success', 'Supportkategorie wurde erstellt.');
    }

    public function update(Request $request, SupportCategory $supportCategory)
    {
        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'priority'         => 'required|in:low,medium,high,critical',
            'work_category_id' => 'nullable|exists:work_categories,id',
        ]);

        $supportCategory->update($data);

        return back()->with('success', 'Supportkategorie wurde aktualisiert.');
    }

    public function destroy(SupportCategory $supportCategory)
    {
        $supportCategory->delete();
        return back()->with('success', 'Supportkategorie wurde gelöscht.');
    }
}
