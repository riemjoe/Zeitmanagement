<?php

namespace App\Http\Controllers;

use App\Models\ContractTemplate;
use Illuminate\Http\Request;

class ContractTemplateController extends Controller
{
    public function index()
    {
        $templates = ContractTemplate::orderBy('name')->get();
        return view('contract-templates.index', compact('templates'));
    }

    public function create()
    {
        return view('contract-templates.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:privacy,handover,maintenance,custom',
            'content'     => 'required|string',
        ]);

        ContractTemplate::create($data);

        return redirect()->route('contract-templates.index')
            ->with('success', 'Vorlage wurde erstellt.');
    }

    public function show(ContractTemplate $contractTemplate)
    {
        return view('contract-templates.show', compact('contractTemplate'));
    }

    public function edit(ContractTemplate $contractTemplate)
    {
        return view('contract-templates.edit', compact('contractTemplate'));
    }

    public function update(Request $request, ContractTemplate $contractTemplate)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'type'        => 'required|in:privacy,handover,maintenance,custom',
            'content'     => 'required|string',
        ]);

        $contractTemplate->update($data);

        return redirect()->route('contract-templates.index')
            ->with('success', 'Vorlage wurde aktualisiert.');
    }

    public function destroy(ContractTemplate $contractTemplate)
    {
        $contractTemplate->delete();
        return redirect()->route('contract-templates.index')
            ->with('success', 'Vorlage wurde gelöscht.');
    }
}
