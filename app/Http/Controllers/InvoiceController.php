<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\Setting;
use App\Models\TimeEntry;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('customer')
            ->orderByDesc('date')
            ->get();
        return view('invoices.index', compact('invoices'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $settings  = Setting::getAll();
        return view('invoices.create', compact('customers', 'settings'));
    }

    /**
     * Gibt alle nicht abgerechneten Zeiteinträge und Ausgaben für einen Kunden zurück (AJAX).
     */
    public function getBillableItems(Request $request)
    {
        // Manuelle Validierung mit JSON-Fehlerantwort statt Redirect
        if (!$request->filled('customer_id')) {
            return response()->json(['message' => 'customer_id fehlt'], 422);
        }

        $customerId = (int) $request->customer_id;

        $timeEntries = TimeEntry::with(['project', 'workCategory'])
            ->whereHas('project', fn ($q) => $q->where('customer_id', $customerId))
            ->where('billed', false)
            ->orderBy('date')
            ->get()
            ->map(fn ($e) => [
                'id'          => $e->id,
                'date'        => $e->date->format('d.m.Y'),
                'project'     => $e->project->name,
                'project_id'  => $e->project_id,
                'category'    => $e->workCategory->name,
                'hours'       => (float) $e->hours,
                'amount'      => round($e->amount, 2),
                'description' => $e->description ?? '',
                'ticket_id'   => $e->ticket_id ?? '',
            ]);

        $expenses = Expense::with('project')
            ->whereHas('project', fn ($q) => $q->where('customer_id', $customerId))
            ->where('billed', false)
            ->orderBy('date')
            ->get()
            ->map(fn ($e) => [
                'id'          => $e->id,
                'date'        => $e->date->format('d.m.Y'),
                'project'     => $e->project->name,
                'project_id'  => $e->project_id,
                'category'    => $e->category ?? '',
                'amount'      => (float) $e->amount,
                'description' => $e->description,
            ]);

        // Projekte mit abrechnbaren Einträgen für diesen Kunden
        $projectIds = $timeEntries->pluck('project_id')
            ->merge($expenses->pluck('project_id'))
            ->unique()->filter()->values();
        $projects = Project::whereIn('id', $projectIds)
            ->orderBy('name')->get()
            ->map(fn ($p) => ['id' => $p->id, 'name' => $p->name]);

        return response()->json([
            'time_entries' => $timeEntries->values(),
            'expenses'     => $expenses->values(),
            'projects'     => $projects->values(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'      => 'required|exists:customers,id',
            'date'             => 'required|date',
            'due_date'         => 'required|date|after_or_equal:date',
            'tax_rate'         => 'required|numeric|min:0|max:100',
            'discount'         => 'nullable|numeric|min:0',
            'notes'               => 'nullable|string',
            'service_description' => 'nullable|string',
            'time_entry_ids'   => 'nullable|array',
            'time_entry_ids.*' => 'exists:time_entries,id',
            'expense_ids'      => 'nullable|array',
            'expense_ids.*'    => 'exists:expenses,id',
        ]);

        $settings = Setting::getAll();

        $invoice = Invoice::create([
            'customer_id'     => $data['customer_id'],
            'invoice_number'  => Invoice::generateNumber(),
            'date'            => $data['date'],
            'due_date'        => $data['due_date'],
            'tax_rate'        => $data['tax_rate'],
            'discount'        => $data['discount'] ?? 0,
            'notes'               => $data['notes'],
            'service_description' => $data['service_description'] ?? null,
            'sender_snapshot'     => $settings,
        ]);

        if (!empty($data['time_entry_ids'])) {
            $invoice->timeEntries()->attach($data['time_entry_ids']);
            TimeEntry::whereIn('id', $data['time_entry_ids'])->update(['billed' => true]);
        }

        if (!empty($data['expense_ids'])) {
            $invoice->expenses()->attach($data['expense_ids']);
            Expense::whereIn('id', $data['expense_ids'])->update(['billed' => true]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'Rechnung wurde erstellt.');
    }

    public function leistungsbeschreibung(Invoice $invoice)
    {
        $invoice->load(['customer', 'timeEntries.project']);
        return view('invoices.leistungsbeschreibung', compact('invoice'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'timeEntries.workCategory', 'timeEntries.project', 'expenses.project']);
        return view('invoices.show', compact('invoice'));
    }

    public function edit(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Bezahlte Rechnungen können nicht bearbeitet werden.');
        }
        $invoice->load(['timeEntries', 'expenses']);
        $customers = Customer::orderBy('name')->get();
        return view('invoices.edit', compact('invoice', 'customers'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $data = $request->validate([
            'status'   => 'required|in:draft,sent,paid,cancelled',
            'due_date' => 'required|date',
            'notes'    => 'nullable|string',
            'discount' => 'nullable|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $invoice->update($data);
        return redirect()->route('invoices.show', $invoice)->with('success', 'Rechnung wurde aktualisiert.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Bezahlte Rechnungen können nicht gelöscht werden.');
        }

        // Zeiteinträge wieder als nicht abgerechnet markieren
        $invoice->timeEntries()->update(['billed' => false]);
        $invoice->expenses()->update(['billed' => false]);

        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Rechnung wurde gelöscht.');
    }
}
