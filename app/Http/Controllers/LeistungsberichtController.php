<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Leistungsbericht;
use App\Models\Setting;
use Illuminate\Http\Request;

class LeistungsberichtController extends Controller
{
    /**
     * Weiterleitung zur Rechnungs-Übersicht mit Tab "leistungsberichte".
     * (Wird von der Tab-Navigation genutzt.)
     */
    public function index()
    {
        return redirect()->route('invoices.index', ['tab' => 'leistungsberichte']);
    }

    /**
     * Formular für manuelle Erstellung eines Leistungsberichts.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();

        // Standard: aktueller Monat
        $dateFrom = now()->startOfMonth()->format('Y-m-d');
        $dateTo   = now()->endOfMonth()->format('Y-m-d');

        return view('leistungsberichte.create', compact('customers', 'dateFrom', 'dateTo'));
    }

    /**
     * Neuen Leistungsbericht speichern.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'date_from'   => 'required|date',
            'date_to'     => 'required|date|after_or_equal:date_from',
            'description' => 'nullable|string',
        ]);

        $lb = Leistungsbericht::create([
            'customer_id'     => $data['customer_id'],
            'invoice_id'      => null,
            'date_from'       => $data['date_from'],
            'date_to'         => $data['date_to'],
            'description'     => $data['description'] ?? null,
            'sender_snapshot' => Setting::getAll(),
        ]);

        return redirect()->route('leistungsberichte.show', $lb)
            ->with('success', 'Leistungsbericht wurde erstellt.');
    }

    /**
     * Einzelnen Leistungsbericht anzeigen (HTML-Druckansicht mit ITIL-Daten).
     */
    public function show(Leistungsbericht $leistungsbericht)
    {
        $leistungsbericht->load('customer');

        if ($leistungsbericht->invoice_id) {
            $leistungsbericht->invoice->load(['timeEntries.workCategory', 'timeEntries.project', 'expenses.project']);
        }

        $incidents = $leistungsbericht->incidents;
        $problems  = $leistungsbericht->problems;
        $changes   = $leistungsbericht->changes;
        $entries   = $leistungsbericht->time_entries_in_period;

        return view('leistungsberichte.show', compact(
            'leistungsbericht', 'incidents', 'problems', 'changes', 'entries'
        ));
    }

    /**
     * Leistungsbericht löschen.
     */
    public function destroy(Leistungsbericht $leistungsbericht)
    {
        $leistungsbericht->delete();

        return redirect()->route('invoices.index', ['tab' => 'leistungsberichte'])
            ->with('success', 'Leistungsbericht wurde gelöscht.');
    }
}
