<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Zeigt die Webhook-Bibliothek mit allen verfügbaren Endpunkten und deren Dokumentation.
 */
class WebhookLibraryController extends Controller
{
    public function index()
    {
        $token = Setting::get('webhook_token');
        if (empty($token)) {
            $token = Str::random(48);
            Setting::set('webhook_token', $token);
        }

        $baseUrl = url('/');

        $groups = [
            [
                'label' => 'Helpdesk',
                'color' => 'blue',
                'icon'  => 'ph-headset',
                'endpoints' => [
                    [
                        'id'     => 'tickets',
                        'method' => 'POST',
                        'path'   => '/api/webhooks/tickets',
                        'label'  => 'Ticket erstellen',
                        'desc'   => 'Legt ein neues Support-Ticket an. Wird automatisch einem Kunden zugeordnet, wenn die E-Mail-Adresse bekannt ist.',
                        'fields' => [
                            ['name' => 'title',               'type' => 'string',  'required' => true,  'desc' => 'Betreff des Tickets'],
                            ['name' => 'description',         'type' => 'string',  'required' => true,  'desc' => 'Beschreibung / Nachricht'],
                            ['name' => 'customer_email',      'type' => 'string',  'required' => true,  'desc' => 'E-Mail-Adresse des Kunden'],
                            ['name' => 'support_category_id', 'type' => 'integer', 'required' => false, 'desc' => 'ID der Support-Kategorie'],
                            ['name' => 'source',              'type' => 'string',  'required' => false, 'desc' => 'Eingangskanal (default: "webhook")'],
                            ['name' => 'priority',            'type' => 'string',  'required' => false, 'desc' => 'low | medium | high | urgent (default: medium)'],
                        ],
                        'example' => [
                            'title'          => 'Login funktioniert nicht',
                            'description'    => 'Seit heute Morgen kann ich mich nicht mehr anmelden.',
                            'customer_email' => 'kunde@beispiel.de',
                            'source'         => 'website',
                            'priority'       => 'high',
                        ],
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
                        'id'     => 'customers',
                        'method' => 'POST',
                        'path'   => '/api/webhooks/customers',
                        'label'  => 'Kunden erstellen',
                        'desc'   => 'Legt einen neuen Kunden an und vergibt automatisch eine eindeutige Kundennummer.',
                        'fields' => [
                            ['name' => 'name',           'type' => 'string',  'required' => true,  'desc' => 'Name des Unternehmens oder der Person'],
                            ['name' => 'email',          'type' => 'string',  'required' => false, 'desc' => 'E-Mail-Adresse'],
                            ['name' => 'contact_person', 'type' => 'string',  'required' => false, 'desc' => 'Ansprechpartner'],
                            ['name' => 'phone',          'type' => 'string',  'required' => false, 'desc' => 'Telefonnummer'],
                            ['name' => 'street',         'type' => 'string',  'required' => false, 'desc' => 'Straße und Hausnummer'],
                            ['name' => 'zip',            'type' => 'string',  'required' => false, 'desc' => 'Postleitzahl'],
                            ['name' => 'city',           'type' => 'string',  'required' => false, 'desc' => 'Stadt'],
                            ['name' => 'country',        'type' => 'string',  'required' => false, 'desc' => 'Land (default: "Deutschland")'],
                            ['name' => 'notes',          'type' => 'string',  'required' => false, 'desc' => 'Interne Notizen'],
                        ],
                        'example' => [
                            'name'           => 'Muster GmbH',
                            'email'          => 'info@muster-gmbh.de',
                            'contact_person' => 'Max Mustermann',
                            'phone'          => '+49 30 123456',
                            'street'         => 'Musterstraße 1',
                            'zip'            => '10115',
                            'city'           => 'Berlin',
                        ],
                        'response' => ['success' => true, 'id' => 7, 'customer_number' => 'ABCD-1234'],
                    ],
                    [
                        'id'     => 'projects',
                        'method' => 'POST',
                        'path'   => '/api/webhooks/projects',
                        'label'  => 'Projekt erstellen',
                        'desc'   => 'Legt ein neues Projekt an, optional einem bestehenden Kunden zugeordnet.',
                        'fields' => [
                            ['name' => 'name',          'type' => 'string',  'required' => true,  'desc' => 'Projektname'],
                            ['name' => 'customer_id',   'type' => 'integer', 'required' => false, 'desc' => 'ID des zugeordneten Kunden'],
                            ['name' => 'description',   'type' => 'string',  'required' => false, 'desc' => 'Projektbeschreibung'],
                            ['name' => 'status',        'type' => 'string',  'required' => false, 'desc' => 'active | paused | completed | cancelled (default: active)'],
                            ['name' => 'hourly_rate',   'type' => 'number',  'required' => false, 'desc' => 'Stundensatz in Euro'],
                            ['name' => 'budget_hours',  'type' => 'number',  'required' => false, 'desc' => 'Stundenbudget'],
                            ['name' => 'budget_amount', 'type' => 'number',  'required' => false, 'desc' => 'Geldliches Budget in Euro'],
                            ['name' => 'deadline',      'type' => 'string',  'required' => false, 'desc' => 'Deadline im Format YYYY-MM-DD'],
                            ['name' => 'notes',         'type' => 'string',  'required' => false, 'desc' => 'Interne Notizen'],
                        ],
                        'example' => [
                            'name'        => 'Website-Relaunch 2025',
                            'customer_id' => 7,
                            'description' => 'Kompletter Neuaufbau der Unternehmenswebsite.',
                            'status'      => 'active',
                            'hourly_rate' => 95,
                            'deadline'    => '2025-12-31',
                        ],
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
                        'id'     => 'incidents',
                        'method' => 'POST',
                        'path'   => '/api/itil/incidents',
                        'label'  => 'Incident erstellen',
                        'desc'   => 'Legt einen neuen ITIL-Incident an. SLA-Fristen werden automatisch anhand der Priorität berechnet.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string',  'required' => true,  'desc' => 'Kurzbeschreibung des Incidents'],
                            ['name' => 'description',      'type' => 'string',  'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'priority',         'type' => 'string',  'required' => false, 'desc' => 'critical | high | medium | low (default: medium)'],
                            ['name' => 'impact',           'type' => 'string',  'required' => false, 'desc' => 'high | medium | low (default: medium)'],
                            ['name' => 'urgency',          'type' => 'string',  'required' => false, 'desc' => 'high | medium | low (default: medium)'],
                            ['name' => 'category',         'type' => 'string',  'required' => false, 'desc' => 'Kategorie (z. B. "Infrastructure")'],
                            ['name' => 'affected_service', 'type' => 'string',  'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'reported_by',      'type' => 'string',  'required' => false, 'desc' => 'Name oder E-Mail des Melders'],
                            ['name' => 'workaround',       'type' => 'string',  'required' => false, 'desc' => 'Bekannter Workaround'],
                        ],
                        'example' => [
                            'title'            => 'Datenbank nicht erreichbar',
                            'description'      => 'Die Produktionsdatenbank antwortet seit 14:30 Uhr nicht mehr.',
                            'priority'         => 'critical',
                            'impact'           => 'high',
                            'urgency'          => 'high',
                            'category'         => 'Database',
                            'affected_service' => 'CRM-Backend',
                            'reported_by'      => 'monitoring@beispiel.de',
                        ],
                        'response' => ['success' => true, 'id' => 3, 'number' => 'INC-0003'],
                    ],
                    [
                        'id'     => 'problems',
                        'method' => 'POST',
                        'path'   => '/api/itil/problems',
                        'label'  => 'Problem erstellen',
                        'desc'   => 'Legt ein neues ITIL-Problem an, das mehreren Incidents zugeordnet werden kann.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string',  'required' => true,  'desc' => 'Kurzbeschreibung des Problems'],
                            ['name' => 'description',      'type' => 'string',  'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'priority',         'type' => 'string',  'required' => false, 'desc' => 'critical | high | medium | low (default: medium)'],
                            ['name' => 'impact',           'type' => 'string',  'required' => false, 'desc' => 'high | medium | low (default: medium)'],
                            ['name' => 'category',         'type' => 'string',  'required' => false, 'desc' => 'Kategorie'],
                            ['name' => 'affected_service', 'type' => 'string',  'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'root_cause',       'type' => 'string',  'required' => false, 'desc' => 'Bekannte Ursache'],
                            ['name' => 'workaround',       'type' => 'string',  'required' => false, 'desc' => 'Bekannter Workaround'],
                        ],
                        'example' => [
                            'title'       => 'Speicherüberlauf in Microservice X',
                            'description' => 'Wiederkehrender Absturz verursacht durch Memory Leak.',
                            'priority'    => 'high',
                            'impact'      => 'high',
                            'category'    => 'Application',
                        ],
                        'response' => ['success' => true, 'id' => 2, 'number' => 'PRB-0002'],
                    ],
                    [
                        'id'     => 'changes',
                        'method' => 'POST',
                        'path'   => '/api/itil/changes',
                        'label'  => 'Change erstellen',
                        'desc'   => 'Legt einen neuen ITIL-Change-Request an.',
                        'fields' => [
                            ['name' => 'title',            'type' => 'string',  'required' => true,  'desc' => 'Kurzbeschreibung des Changes'],
                            ['name' => 'description',      'type' => 'string',  'required' => false, 'desc' => 'Detaillierte Beschreibung'],
                            ['name' => 'type',             'type' => 'string',  'required' => false, 'desc' => 'normal | standard | emergency (default: normal)'],
                            ['name' => 'priority',         'type' => 'string',  'required' => false, 'desc' => 'critical | high | medium | low (default: medium)'],
                            ['name' => 'impact',           'type' => 'string',  'required' => false, 'desc' => 'high | medium | low (default: medium)'],
                            ['name' => 'risk',             'type' => 'string',  'required' => false, 'desc' => 'high | medium | low (default: medium)'],
                            ['name' => 'category',         'type' => 'string',  'required' => false, 'desc' => 'Kategorie'],
                            ['name' => 'affected_service', 'type' => 'string',  'required' => false, 'desc' => 'Betroffener Dienst'],
                            ['name' => 'requested_by',     'type' => 'string',  'required' => false, 'desc' => 'Name oder E-Mail des Anforderers'],
                        ],
                        'example' => [
                            'title'        => 'PHP-Update auf Version 8.3',
                            'description'  => 'Aktualisierung aller Server auf PHP 8.3 LTS.',
                            'type'         => 'standard',
                            'priority'     => 'medium',
                            'risk'         => 'medium',
                            'requested_by' => 'devops@beispiel.de',
                        ],
                        'response' => ['success' => true, 'id' => 5, 'number' => 'CHG-0005'],
                    ],
                ],
            ],
        ];

        return view('webhooks.library', compact('token', 'baseUrl', 'groups'));
    }
}
