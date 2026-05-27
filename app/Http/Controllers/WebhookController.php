<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Models\WebhookToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    // ── Hub-Index (3 Tabs) ────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $activeTab = $request->get('tab', 'webhooks');

        // Tab 1: Automation-Webhooks (eingehende Trigger)
        $webhooks = Webhook::withCount('automations')
            ->orderByDesc('updated_at')
            ->get();

        // Tab 2: API-Bibliothek
        $baseUrl = url('/');
        $libraryGroups = $this->buildLibraryGroups();

        // Tab 3: API-Tokens
        $apiTokens     = WebhookToken::orderByDesc('created_at')->get();
        $endpointGroups = WebhookToken::ENDPOINTS;

        return view('webhooks.index', compact(
            'activeTab', 'webhooks', 'baseUrl', 'libraryGroups', 'apiTokens', 'endpointGroups'
        ));
    }

    // ── API-Token-Verwaltung ──────────────────────────────────────────────────

    public function storeToken(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|in:' . implode(',', WebhookToken::allEndpointKeys()),
            'expires_at'  => 'nullable|date|after:now',
        ]);

        WebhookToken::create([
            'name'        => $data['name'],
            'token'       => WebhookToken::generateToken(),
            'permissions' => $data['permissions'] ?? [],
            'is_active'   => true,
            'expires_at'  => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('webhooks.index', ['tab' => 'tokens'])
            ->with('success', 'API-Token «' . $data['name'] . '» wurde erstellt.');
    }

    public function destroyToken(WebhookToken $webhookToken)
    {
        $name = $webhookToken->name;
        $webhookToken->delete();

        return redirect()->route('webhooks.index', ['tab' => 'tokens'])
            ->with('success', "Token «{$name}» wurde gelöscht.");
    }

    public function toggleToken(WebhookToken $webhookToken)
    {
        $webhookToken->update(['is_active' => !$webhookToken->is_active]);
        $state = $webhookToken->is_active ? 'aktiviert' : 'deaktiviert';

        return redirect()->route('webhooks.index', ['tab' => 'tokens'])
            ->with('success', "Token «{$webhookToken->name}» wurde {$state}.");
    }

    // ── Erstellen ─────────────────────────────────────────────────────────────

    public function create()
    {
        $webhook = new Webhook(['is_active' => true]);
        return view('webhooks.edit', compact('webhook'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $webhook = Webhook::create($data);

        return redirect()->route('webhooks.index', ['tab' => 'webhooks'])
            ->with('success', "Webhook «{$webhook->name}» wurde erstellt.");
    }

    // ── Bearbeiten ────────────────────────────────────────────────────────────

    public function edit(Webhook $webhook)
    {
        $webhook->loadCount('automations');
        $automations = $webhook->automations()->orderBy('name')->get();

        return view('webhooks.edit', compact('webhook', 'automations'));
    }

    public function update(Request $request, Webhook $webhook)
    {
        $data = $this->validated($request, $webhook);
        $webhook->update($data);

        return redirect()->route('webhooks.index', ['tab' => 'webhooks'])
            ->with('success', "Webhook «{$webhook->name}» wurde gespeichert.");
    }

    // ── Löschen ───────────────────────────────────────────────────────────────

    public function destroy(Webhook $webhook)
    {
        $name = $webhook->name;

        // Automationen trennen (webhook_id auf null setzen)
        $webhook->automations()->update(['webhook_id' => null]);

        $webhook->delete();

        return redirect()->route('webhooks.index', ['tab' => 'webhooks'])
            ->with('success', "Webhook «{$name}» wurde gelöscht.");
    }

    // ── Token neu generieren ──────────────────────────────────────────────────

    public function regenerateToken(Webhook $webhook)
    {
        $webhook->update(['token' => \Illuminate\Support\Str::random(48)]);

        return back()->with('success', 'Token wurde neu generiert. Bitte aktualisiere die URL in allen externen Systemen.');
    }

    // ── Hilfsmethoden ────────────────────────────────────────────────────────

    private function validated(Request $request, ?Webhook $webhook = null): array
    {
        return $request->validate([
            'name'        => 'required|string|max:200',
            'description' => 'nullable|string|max:1000',
            'secret'      => 'nullable|string|max:255',
            'is_active'   => 'boolean',
        ]);
    }

    private function buildLibraryGroups(): array
    {
        return [
            [
                'label' => 'Helpdesk',
                'color' => 'blue',
                'icon'  => 'ph-headset',
                'endpoints' => [
                    [
                        'id' => 'tickets', 'method' => 'POST', 'path' => '/api/webhooks/tickets',
                        'label' => 'Ticket erstellen',
                        'permission_key' => 'webhooks.tickets',
                        'desc' => 'Legt ein neues Support-Ticket an. Wird automatisch einem Kunden zugeordnet, wenn die E-Mail-Adresse bekannt ist.',
                        'fields' => [
                            ['name' => 'title',               'type' => 'string',  'required' => true,  'desc' => 'Betreff des Tickets'],
                            ['name' => 'description',         'type' => 'string',  'required' => true,  'desc' => 'Beschreibung / Nachricht'],
                            ['name' => 'customer_email',      'type' => 'string',  'required' => true,  'desc' => 'E-Mail-Adresse des Kunden'],
                            ['name' => 'support_category_id', 'type' => 'integer', 'required' => false, 'desc' => 'ID der Support-Kategorie'],
                            ['name' => 'source',              'type' => 'string',  'required' => false, 'desc' => 'Eingangskanal (default: "webhook")'],
                            ['name' => 'priority',            'type' => 'string',  'required' => false, 'desc' => 'low | medium | high | urgent'],
                        ],
                        'example' => ['title' => 'Login funktioniert nicht', 'description' => 'Seit heute Morgen kann ich mich nicht mehr anmelden.', 'customer_email' => 'kunde@beispiel.de', 'source' => 'website', 'priority' => 'high'],
                        'response' => ['success' => true, 'id' => 42, 'ticket_number' => 'ABC-DEF-GHI'],
                    ],
                ],
            ],
            [
                'label' => 'Kunden & Projekte',
                'color' => 'green',
                'icon'  => 'ph-buildings',
                'endpoints' => [
                    [
                        'id' => 'customers', 'method' => 'POST', 'path' => '/api/webhooks/customers',
                        'label' => 'Kunden erstellen',
                        'permission_key' => 'webhooks.customers',
                        'desc' => 'Legt einen neuen Kunden an und vergibt automatisch eine eindeutige Kundennummer.',
                        'fields' => [
                            ['name' => 'name',           'type' => 'string', 'required' => true,  'desc' => 'Name des Unternehmens oder der Person'],
                            ['name' => 'email',          'type' => 'string', 'required' => false, 'desc' => 'E-Mail-Adresse'],
                            ['name' => 'contact_person', 'type' => 'string', 'required' => false, 'desc' => 'Ansprechpartner'],
                            ['name' => 'phone',          'type' => 'string', 'required' => false, 'desc' => 'Telefonnummer'],
                            ['name' => 'street',         'type' => 'string', 'required' => false, 'desc' => 'Straße und Hausnummer'],
                            ['name' => 'zip',            'type' => 'string', 'required' => false, 'desc' => 'Postleitzahl'],
                            ['name' => 'city',           'type' => 'string', 'required' => false, 'desc' => 'Stadt'],
                            ['name' => 'country',        'type' => 'string', 'required' => false, 'desc' => 'Land (default: "Deutschland")'],
                            ['name' => 'notes',          'type' => 'string', 'required' => false, 'desc' => 'Interne Notizen'],
                        ],
                        'example' => ['name' => 'Muster GmbH', 'email' => 'info@muster-gmbh.de', 'contact_person' => 'Max Mustermann', 'phone' => '+49 30 123456', 'street' => 'Musterstraße 1', 'zip' => '10115', 'city' => 'Berlin'],
                        'response' => ['success' => true, 'id' => 7, 'customer_number' => 'ABCD-1234'],
                    ],
                    [
                        'id' => 'projects', 'method' => 'POST', 'path' => '/api/webhooks/projects',
                        'label' => 'Projekt erstellen',
                        'permission_key' => 'webhooks.projects',
                        'desc' => 'Legt ein neues Projekt an, optional einem bestehenden Kunden zugeordnet.',
                        'fields' => [
                            ['name' => 'name',          'type' => 'string',  'required' => true,  'desc' => 'Projektname'],
                            ['name' => 'customer_id',   'type' => 'integer', 'required' => false, 'desc' => 'ID des zugeordneten Kunden'],
                            ['name' => 'description',   'type' => 'string',  'required' => false, 'desc' => 'Projektbeschreibung'],
                            ['name' => 'status',        'type' => 'string',  'required' => false, 'desc' => 'active | paused | completed | cancelled'],
                            ['name' => 'hourly_rate',   'type' => 'number',  'required' => false, 'desc' => 'Stundensatz in Euro'],
                            ['name' => 'budget_hours',  'type' => 'number',  'required' => false, 'desc' => 'Stundenbudget'],
                            ['name' => 'budget_amount', 'type' => 'number',  'required' => false, 'desc' => 'Budget in Euro'],
                            ['name' => 'deadline',      'type' => 'string',  'required' => false, 'desc' => 'Deadline (YYYY-MM-DD)'],
                            ['name' => 'notes',         'type' => 'string',  'required' => false, 'desc' => 'Interne Notizen'],
                        ],
                        'example' => ['name' => 'Website-Relaunch 2025', 'customer_id' => 7, 'status' => 'active', 'hourly_rate' => 95, 'deadline' => '2025-12-31'],
                        'response' => ['success' => true, 'id' => 15, 'name' => 'Website-Relaunch 2025'],
                    ],
                ],
            ],
            [
                'label' => 'ITIL',
                'color' => 'indigo',
                'icon'  => 'ph-shield-check',
                'endpoints' => [
                    [
                        'id' => 'incidents', 'method' => 'POST', 'path' => '/api/itil/incidents',
                        'label' => 'Incident erstellen',
                        'permission_key' => 'itil.incidents',
                        'desc' => 'Legt einen neuen ITIL-Incident an. SLA-Fristen werden anhand der Priorität automatisch berechnet.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string', 'required' => true,  'desc' => 'Kurzbeschreibung'],
                            ['name' => 'description',      'type' => 'string', 'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'priority',         'type' => 'string', 'required' => false, 'desc' => 'critical | high | medium | low'],
                            ['name' => 'impact',           'type' => 'string', 'required' => false, 'desc' => 'high | medium | low'],
                            ['name' => 'urgency',          'type' => 'string', 'required' => false, 'desc' => 'high | medium | low'],
                            ['name' => 'category',         'type' => 'string', 'required' => false, 'desc' => 'Kategorie'],
                            ['name' => 'affected_service', 'type' => 'string', 'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'reported_by',      'type' => 'string', 'required' => false, 'desc' => 'Name/E-Mail des Melders'],
                            ['name' => 'workaround',       'type' => 'string', 'required' => false, 'desc' => 'Bekannter Workaround'],
                        ],
                        'example' => ['title' => 'Datenbank nicht erreichbar', 'priority' => 'critical', 'impact' => 'high', 'urgency' => 'high', 'category' => 'Database', 'affected_service' => 'CRM-Backend'],
                        'response' => ['success' => true, 'id' => 3, 'number' => 'INC-0003'],
                    ],
                    [
                        'id' => 'problems', 'method' => 'POST', 'path' => '/api/itil/problems',
                        'label' => 'Problem erstellen',
                        'permission_key' => 'itil.problems',
                        'desc' => 'Legt ein neues ITIL-Problem an, dem mehrere Incidents zugeordnet werden können.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string', 'required' => true,  'desc' => 'Kurzbeschreibung'],
                            ['name' => 'description',      'type' => 'string', 'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'priority',         'type' => 'string', 'required' => false, 'desc' => 'critical | high | medium | low'],
                            ['name' => 'impact',           'type' => 'string', 'required' => false, 'desc' => 'high | medium | low'],
                            ['name' => 'category',         'type' => 'string', 'required' => false, 'desc' => 'Kategorie'],
                            ['name' => 'affected_service', 'type' => 'string', 'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'root_cause',       'type' => 'string', 'required' => false, 'desc' => 'Bekannte Ursache'],
                            ['name' => 'workaround',       'type' => 'string', 'required' => false, 'desc' => 'Bekannter Workaround'],
                        ],
                        'example' => ['title' => 'Speicherüberlauf in Microservice X', 'priority' => 'high', 'impact' => 'high', 'category' => 'Application'],
                        'response' => ['success' => true, 'id' => 2, 'number' => 'PRB-0002'],
                    ],
                    [
                        'id' => 'changes', 'method' => 'POST', 'path' => '/api/itil/changes',
                        'label' => 'Change erstellen',
                        'permission_key' => 'itil.changes',
                        'desc' => 'Legt einen neuen ITIL-Change-Request an.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string', 'required' => true,  'desc' => 'Kurzbeschreibung'],
                            ['name' => 'description',      'type' => 'string', 'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'type',             'type' => 'string', 'required' => false, 'desc' => 'normal | standard | emergency'],
                            ['name' => 'priority',         'type' => 'string', 'required' => false, 'desc' => 'critical | high | medium | low'],
                            ['name' => 'impact',           'type' => 'string', 'required' => false, 'desc' => 'high | medium | low'],
                            ['name' => 'risk',             'type' => 'string', 'required' => false, 'desc' => 'high | medium | low'],
                            ['name' => 'category',         'type' => 'string', 'required' => false, 'desc' => 'Kategorie'],
                            ['name' => 'affected_service', 'type' => 'string', 'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'requested_by',     'type' => 'string', 'required' => false, 'desc' => 'Name/E-Mail des Anforderers'],
                        ],
                        'example' => ['title' => 'PHP-Update auf Version 8.3', 'type' => 'standard', 'priority' => 'medium', 'risk' => 'medium', 'requested_by' => 'devops@beispiel.de'],
                        'response' => ['success' => true, 'id' => 5, 'number' => 'CHG-0005'],
                    ],
                ],
            ],
        ];
    }
}
