<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractTemplate;
use App\Models\Customer;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ContractController extends Controller
{
    public function index()
    {
        $contracts = Contract::with('customer', 'template')
            ->orderByDesc('date')
            ->get();
        return view('contracts.index', compact('contracts'));
    }

    public function create(Request $request)
    {
        $customers  = Customer::orderBy('name')->get();
        $templates  = ContractTemplate::orderBy('name')->get();
        $settings   = Setting::getAll();

        // Optional: Vorlage und Kunde vorbelegen via Query-String
        $selectedTemplate = $request->query('template_id')
            ? ContractTemplate::find($request->query('template_id'))
            : null;
        $selectedCustomerId = $request->query('customer_id');

        // Vorausgefüllten Inhalt generieren, falls Vorlage + Kunde schon bekannt
        $prefillContent = '';
        if ($selectedTemplate && $selectedCustomerId) {
            $customer = Customer::find($selectedCustomerId);
            if ($customer) {
                $prefillContent = $selectedTemplate->render($customer, $settings);
            }
        }

        return view('contracts.create', compact(
            'customers', 'templates', 'settings',
            'selectedTemplate', 'selectedCustomerId', 'prefillContent'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'contract_template_id' => 'nullable|exists:contract_templates,id',
            'title'                => 'required|string|max:255',
            'content'              => 'nullable|string',
            'status'               => 'required|in:draft,sent,signed,terminated',
            'date'                 => 'required|date',
            'valid_until'          => 'nullable|date|after_or_equal:date',
            'notes'                => 'nullable|string',
        ]);

        $contract = Contract::create($data);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Vertrag wurde erstellt.');
    }

    public function show(Contract $contract)
    {
        $contract->load('customer', 'template');
        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $customers = Customer::orderBy('name')->get();
        $templates = ContractTemplate::orderBy('name')->get();
        $contract->load('customer', 'template');
        return view('contracts.edit', compact('contract', 'customers', 'templates'));
    }

    public function update(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'customer_id'          => 'required|exists:customers,id',
            'contract_template_id' => 'nullable|exists:contract_templates,id',
            'title'                => 'required|string|max:255',
            'content'              => 'nullable|string',
            'status'               => 'required|in:draft,sent,signed,terminated',
            'date'                 => 'required|date',
            'valid_until'          => 'nullable|date|after_or_equal:date',
            'notes'                => 'nullable|string',
        ]);

        $contract->update($data);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Vertrag wurde aktualisiert.');
    }

    public function destroy(Contract $contract)
    {
        // Signierte PDF löschen, falls vorhanden
        if ($contract->signed_pdf_path) {
            Storage::delete('contracts/' . $contract->signed_pdf_path);
        }
        $contract->delete();
        return redirect()->route('contracts.index')
            ->with('success', 'Vertrag wurde gelöscht.');
    }

    /** Signierte PDF hochladen (gespeichert außerhalb von public/) */
    public function uploadPdf(Request $request, Contract $contract)
    {
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf|max:20480',
        ]);

        // Altes PDF löschen
        if ($contract->signed_pdf_path) {
            Storage::delete('contracts/' . $contract->signed_pdf_path);
        }

        $filename = 'contract-' . $contract->id . '-signed-' . time() . '.pdf';
        $request->file('signed_pdf')->storeAs('contracts', $filename);

        $contract->update([
            'signed_pdf_path' => $filename,
            'status'          => 'signed',
        ]);

        return redirect()->route('contracts.show', $contract)
            ->with('success', 'Signiertes Dokument wurde hochgeladen. Status auf „Unterzeichnet" gesetzt.');
    }

    /** Signierte PDF herunterladen (nur für eingeloggte Nutzer) */
    public function downloadPdf(Contract $contract)
    {
        if (! $contract->signed_pdf_path) {
            abort(404, 'Kein signiertes Dokument vorhanden.');
        }

        $path = 'contracts/' . $contract->signed_pdf_path;

        if (! Storage::exists($path)) {
            abort(404, 'Datei nicht gefunden.');
        }

        return Storage::download($path, 'Vertrag-' . $contract->id . '-signiert.pdf');
    }

    /** Vorlage für einen Kunden rendern (AJAX) */
    public function renderTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|exists:contract_templates,id',
            'customer_id' => 'required|exists:customers,id',
        ]);

        $template = ContractTemplate::findOrFail($request->template_id);
        $customer = Customer::findOrFail($request->customer_id);
        $settings = Setting::getAll();

        return response()->json([
            'content' => $template->render($customer, $settings),
            'title'   => $template->name,
        ]);
    }

    /** Druckansicht */
    public function print(Contract $contract)
    {
        $contract->load('customer', 'template');
        return view('contracts.print', compact('contract'));
    }
}
