<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerItilSlaSetting;
use App\Models\CustomerSlaSetting;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'street'         => 'nullable|string|max:255',
            'zip'            => 'nullable|string|max:20',
            'city'           => 'nullable|string|max:255',
            'country'        => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $data['customer_number'] = Customer::generateNumber();

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
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:50',
            'street'         => 'nullable|string|max:255',
            'zip'            => 'nullable|string|max:20',
            'city'           => 'nullable|string|max:255',
            'country'        => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $customer->update($data);
        return redirect()->route('customers.index')->with('success', 'Kunde wurde aktualisiert.');
    }

    /** Nachricht an Kunden per E-Mail senden */
    public function sendMessage(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'client_message' => 'required|string|max:5000',
            'subject'        => 'required|string|max:255',
            'ticket_number'  => 'nullable|string|max:100',
        ]);

        $template = \App\Models\Setting::get('customer_message_template', '');

        if (! $template) {
            return back()->with('error', 'Kein Nachrichtentemplate in den Einstellungen hinterlegt.');
        }

        if (! $customer->email) {
            return back()->with('error', 'Dieser Kunde hat keine E-Mail-Adresse hinterlegt.');
        }

        $body = $this->replacePlaceholders($template, $customer, $data);

        // Betreff aus Template extrahieren (erste Zeile, falls vorhanden) oder Standard
        $subject = $data['subject'] ?: 'Nachricht von Ihrem Dienstleister';

        try {
            \Illuminate\Support\Facades\Mail::html($body, function ($msg) use ($customer, $subject) {
                $msg->to($customer->email, $customer->name)->subject($subject);
            });

            \App\Models\EmailLog::record('customer_message', $customer->email, $subject, 'sent');

            return back()->with('success', 'Nachricht wurde erfolgreich an ' . $customer->email . ' gesendet.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Kundennachricht fehlgeschlagen: ' . $e->getMessage());
            \App\Models\EmailLog::record('customer_message', $customer->email, $subject, 'failed', $e->getMessage());
            return back()->with('error', 'E-Mail-Versand fehlgeschlagen: ' . $e->getMessage());
        }
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

    /** ITIL-SLA-Zeiten (Incidents) pro Priorität für einen Kunden speichern */
    public function updateItilSla(Request $request, Customer $customer)
    {
        $priorities = array_keys(Incident::PRIORITIES);

        foreach ($priorities as $priority) {
            $responseHours = $request->input("itil_sla.{$priority}.response");
            $resolveHours  = $request->input("itil_sla.{$priority}.resolve");

            if ($responseHours !== null && $responseHours !== '' && $resolveHours !== null && $resolveHours !== '') {
                CustomerItilSlaSetting::updateOrCreate(
                    ['customer_id' => $customer->id, 'priority' => $priority],
                    [
                        'response_hours' => max(1, (int) $responseHours),
                        'resolve_hours'  => max(1, (int) $resolveHours),
                    ]
                );
            } else {
                // Leer = Eintrag entfernen → globale Einstellung greift
                CustomerItilSlaSetting::where('customer_id', $customer->id)
                    ->where('priority', $priority)
                    ->delete();
            }
        }

        return back()->with('success', 'ITIL-SLA-Zeiten wurden gespeichert.');
    }


    private function replacePlaceholders($template, $customer, $data)
    {
        $replacements = [
            '{{customer_number}}' => $customer->customer_number,
            '{{customer_name}}' => $customer->name,
            '{{customer_email}}' => $customer->email,
            '{{customer_contact_person}}' => $customer->contact_person,
            '{{customer_phone}}' => $customer->phone,
            '{{customer_street}}' => $customer->street,
            '{{customer_zip}}' => $customer->zip,
            '{{customer_city}}' => $customer->city,
            '{{customer_country}}' => $customer->country,
            '{{client_message}}' => $data['client_message'] ?? '',
            '{{current_date}}' => now()->format('d.m.Y'),
            '{{current_time}}' => now()->format('H:i'),
            '{{current_datetime}}' => now()->format('d.m.Y H:i'),
            '{{company_name}}' => \App\Models\Setting::get('company_name', 'Ihr Dienstleister'),
            '{{company_email}}' => \App\Models\Setting::get('company_email', 'info@ihre-firma.de'),
            '{{company_phone}}' => \App\Models\Setting::get('company_phone', '0123456789'),
            '{{company_website}}' => \App\Models\Setting::get('company_website', 'https://www.ihre-firma.de'),
            '{{current_user_name}}' => Auth::user() ? Auth::user()->name : 'System',
            '{{ticket_number}}' => $data['ticket_number'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
}
