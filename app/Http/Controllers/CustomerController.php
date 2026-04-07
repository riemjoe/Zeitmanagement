<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerSlaSetting;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index()
    {
        $customers = Customer::withCount('projects')->orderBy('name')->get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'street'  => 'nullable|string|max:255',
            'zip'     => 'nullable|string|max:20',
            'city'    => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'notes'   => 'nullable|string',
        ]);

        Customer::create($data);
        return redirect()->route('customers.index')->with('success', 'Kunde wurde angelegt.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['projects.timeEntries', 'projects.expenses', 'invoices', 'contracts.template']);
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'nullable|email|max:255',
            'phone'   => 'nullable|string|max:50',
            'street'  => 'nullable|string|max:255',
            'zip'     => 'nullable|string|max:20',
            'city'    => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'notes'   => 'nullable|string',
        ]);

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Kunde wurde aktualisiert.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->projects()->exists()) {
            return back()->with('error', 'Kunde kann nicht gelöscht werden – es existieren noch Projekte.');
        }
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Kunde wurde gelöscht.');
    }

    /** SLA-Zeiten pro Support-Kategorie für einen Kunden speichern */
    public function updateSla(Request $request, Customer $customer)
    {
        $slaData = $request->input('sla', []);

        foreach ($slaData as $categoryId => $hours) {
            if ($hours !== null && $hours !== '') {
                CustomerSlaSetting::updateOrCreate(
                    ['customer_id' => $customer->id, 'support_category_id' => $categoryId],
                    ['sla_hours' => (int) $hours]
                );
            } else {
                // Leer = SLA entfernen
                CustomerSlaSetting::where('customer_id', $customer->id)
                    ->where('support_category_id', $categoryId)
                    ->delete();
            }
        }

        return back()->with('success', 'SLA-Zeiten wurden gespeichert.');
    }
}
