<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectTodo;
use App\Models\Quote;
use App\Models\QuoteFeature;
use App\Models\Setting;
use Illuminate\Http\Request;

class QuoteController extends Controller
{
    public function index()
    {
        $quotes = Quote::with('customer')
            ->orderByDesc('date')
            ->get();
        return view('quotes.index', compact('quotes'));
    }

    public function create()
    {
        $customers   = Customer::orderBy('name')->get();
        $settings    = Setting::getAll();
        $defaultRate = $settings['hourly_rate'] ?? 80;
        $defaultTax  = $settings['tax_rate'] ?? 19;
        return view('quotes.create', compact('customers', 'settings', 'defaultRate', 'defaultTax'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'title'          => 'required|string|max:255',
            'date'           => 'required|date',
            'valid_until'    => 'nullable|date|after_or_equal:date',
            'status'         => 'required|in:draft,sent,accepted,rejected',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'lines_per_hour' => 'required|integer|min:1',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'discount'       => 'nullable|numeric|min:0',
            'buffer_percent' => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
            // Features als Array
            'features'              => 'nullable|array',
            'features.*.name'         => 'required_with:features|string|max:255',
            'features.*.description'  => 'nullable|string',
            'features.*.lines_of_code'=> 'nullable|integer|min:0',
            'features.*.hours_override'=> 'nullable|numeric|min:0',
        ]);

        $settings = Setting::getAll();

        $quote = Quote::create([
            'customer_id'    => $data['customer_id'],
            'quote_number'   => Quote::generateNumber(),
            'title'          => $data['title'],
            'date'           => $data['date'],
            'valid_until'    => $data['valid_until'] ?? null,
            'status'         => $data['status'],
            'hourly_rate'    => $data['hourly_rate'] ?: null,
            'lines_per_hour' => $data['lines_per_hour'],
            'tax_rate'       => $data['tax_rate'],
            'discount'       => $data['discount'] ?? 0,
            'buffer_percent' => $data['buffer_percent'] ?? 0,
            'notes'          => $data['notes'] ?? null,
            'sender_snapshot'=> $settings,
        ]);

        foreach ($data['features'] ?? [] as $i => $feat) {
            if (empty($feat['name'])) continue;
            QuoteFeature::create([
                'quote_id'       => $quote->id,
                'name'           => $feat['name'],
                'description'    => $feat['description'] ?? null,
                'lines_of_code'  => $feat['lines_of_code'] ?: null,
                'hours_override' => ($feat['hours_override'] ?? '') !== '' ? $feat['hours_override'] : null,
                'sort_order'     => $i,
            ]);
        }

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Angebot wurde erstellt.');
    }

    public function show(Quote $quote)
    {
        $quote->load(['customer', 'features', 'projects']);
        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        $customers   = Customer::orderBy('name')->get();
        $settings    = Setting::getAll();
        $defaultRate = $settings['hourly_rate'] ?? 80;
        $defaultTax  = $settings['tax_rate'] ?? 19;
        $quote->load('features');
        return view('quotes.edit', compact('quote', 'customers', 'settings', 'defaultRate', 'defaultTax'));
    }

    public function update(Request $request, Quote $quote)
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'title'          => 'required|string|max:255',
            'date'           => 'required|date',
            'valid_until'    => 'nullable|date|after_or_equal:date',
            'status'         => 'required|in:draft,sent,accepted,rejected',
            'hourly_rate'    => 'nullable|numeric|min:0',
            'lines_per_hour' => 'required|integer|min:1',
            'tax_rate'       => 'required|numeric|min:0|max:100',
            'discount'       => 'nullable|numeric|min:0',
            'buffer_percent' => 'nullable|numeric|min:0|max:100',
            'notes'          => 'nullable|string',
            'features'              => 'nullable|array',
            'features.*.name'         => 'required_with:features|string|max:255',
            'features.*.description'  => 'nullable|string',
            'features.*.lines_of_code'=> 'nullable|integer|min:0',
            'features.*.hours_override'=> 'nullable|numeric|min:0',
        ]);

        $quote->update([
            'customer_id'    => $data['customer_id'],
            'title'          => $data['title'],
            'date'           => $data['date'],
            'valid_until'    => $data['valid_until'] ?? null,
            'status'         => $data['status'],
            'hourly_rate'    => $data['hourly_rate'] ?: null,
            'lines_per_hour' => $data['lines_per_hour'],
            'tax_rate'       => $data['tax_rate'],
            'discount'       => $data['discount'] ?? 0,
            'buffer_percent' => $data['buffer_percent'] ?? 0,
            'notes'          => $data['notes'] ?? null,
        ]);

        // Features komplett neu schreiben
        $quote->features()->delete();
        foreach ($data['features'] ?? [] as $i => $feat) {
            if (empty($feat['name'])) continue;
            QuoteFeature::create([
                'quote_id'       => $quote->id,
                'name'           => $feat['name'],
                'description'    => $feat['description'] ?? null,
                'lines_of_code'  => $feat['lines_of_code'] ?: null,
                'hours_override' => ($feat['hours_override'] ?? '') !== '' ? $feat['hours_override'] : null,
                'sort_order'     => $i,
            ]);
        }

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Angebot wurde aktualisiert.');
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();
        return redirect()->route('quotes.index')
            ->with('success', 'Angebot wurde gelöscht.');
    }

    public function pdf(Quote $quote)
    {
        $quote->load(['customer', 'features']);
        return view('quotes.pdf', compact('quote'));
    }

    public function lastenheft(Quote $quote)
    {
        $quote->load(['customer', 'features']);
        return view('quotes.lastenheft', compact('quote'));
    }

    /**
     * Angebot in ein Projekt umwandeln.
     */
    public function convertToProject(Quote $quote)
    {
        $quote->load(['customer', 'features']);

        $project = Project::create([
            'customer_id' => $quote->customer_id,
            'name'        => $quote->title,
            'description' => $quote->notes,
            'hourly_rate' => $quote->hourly_rate,
            'status'      => 'active',
            'quote_id'    => $quote->id,
            'budget_hours'=> $quote->total_hours > 0 ? round($quote->total_hours, 2) : null,
        ]);

        // Features als ToDos anlegen
        foreach ($quote->features as $i => $feat) {
            ProjectTodo::create([
                'project_id'  => $project->id,
                'title'       => $feat->name,
                'description' => $feat->description,
                'sort_order'  => $feat->sort_order,
            ]);
        }

        // Angebot als akzeptiert markieren
        $quote->update(['status' => 'accepted']);

        return redirect()->route('projects.show', $project)
            ->with('success', 'Projekt aus Angebot erstellt. ToDos wurden automatisch befüllt.');
    }
}
