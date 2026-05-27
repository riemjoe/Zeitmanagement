<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Ticket;
use App\Models\WebhookToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Öffentliche API-Webhook-Endpunkte für die Erstellung von Tickets, Kunden und Projekten.
 *
 * Authentifizierung über Bearer-Token (Authorization: Bearer <token>) oder ?token=.
 * Token wird in der webhook_tokens-Tabelle verwaltet mit je konfigurierten Endpunkt-Rechten.
 *
 * POST /api/webhooks/tickets    → Ticket erstellen   (Permission: webhooks.tickets)
 * POST /api/webhooks/customers  → Kunden erstellen   (Permission: webhooks.customers)
 * POST /api/webhooks/projects   → Projekt erstellen  (Permission: webhooks.projects)
 */
class PublicWebhookController extends Controller
{
    private function unauthorized(string $reason = 'Ungültiger oder fehlender Token.'): JsonResponse
    {
        return response()->json(['error' => "Unauthorized. {$reason}"], 401);
    }

    // ── Endpoints ────────────────────────────────────────────────────────────

    /** POST /api/webhooks/tickets */
    public function createTicket(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'webhooks.tickets')) {
            return $this->unauthorized('Kein Zugriff auf Endpoint "webhooks.tickets".');
        }

        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'required|string',
            'customer_email'      => 'required|email|max:255',
            'support_category_id' => 'nullable|exists:support_categories,id',
            'source'              => 'nullable|string|max:100',
            'priority'            => 'nullable|in:low,medium,high,urgent',
        ]);

        $customer    = Customer::where('email', $data['customer_email'])->first();
        $slaDeadline = null;

        if ($customer && !empty($data['support_category_id'])) {
            $slaSetting = $customer->slaSettings()
                ->where('support_category_id', $data['support_category_id'])
                ->first();
            if ($slaSetting) {
                $slaDeadline = now()->addHours($slaSetting->sla_hours);
            }
        }

        $ticket = Ticket::create([
            'ticket_number'       => Ticket::generateTicketNumber(),
            'customer_id'         => $customer?->id,
            'customer_email'      => $data['customer_email'],
            'support_category_id' => $data['support_category_id'] ?? null,
            'title'               => $data['title'],
            'description'         => $data['description'],
            'source'              => $data['source'] ?? 'webhook',
            'status'              => 'open',
            'priority'            => $data['priority'] ?? 'medium',
            'sla_deadline'        => $slaDeadline,
        ]);

        return response()->json([
            'success'       => true,
            'id'            => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
        ], 201);
    }

    /** POST /api/webhooks/customers */
    public function createCustomer(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'webhooks.customers')) {
            return $this->unauthorized('Kein Zugriff auf Endpoint "webhooks.customers".');
        }

        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone'          => 'nullable|string|max:100',
            'street'         => 'nullable|string|max:255',
            'zip'            => 'nullable|string|max:20',
            'city'           => 'nullable|string|max:100',
            'country'        => 'nullable|string|max:100',
            'notes'          => 'nullable|string',
        ]);

        $customer = Customer::create(array_merge($data, [
            'customer_number' => Customer::generateNumber(),
            'country'         => $data['country'] ?? 'Deutschland',
        ]));

        return response()->json([
            'success'         => true,
            'id'              => $customer->id,
            'customer_number' => $customer->customer_number,
        ], 201);
    }

    /** POST /api/webhooks/projects */
    public function createProject(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'webhooks.projects')) {
            return $this->unauthorized('Kein Zugriff auf Endpoint "webhooks.projects".');
        }

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'customer_id'   => 'nullable|exists:customers,id',
            'description'   => 'nullable|string',
            'status'        => 'nullable|in:active,paused,completed,cancelled',
            'hourly_rate'   => 'nullable|numeric|min:0',
            'budget_hours'  => 'nullable|numeric|min:0',
            'budget_amount' => 'nullable|numeric|min:0',
            'deadline'      => 'nullable|date',
            'notes'         => 'nullable|string',
        ]);

        $project = Project::create(array_merge($data, [
            'status' => $data['status'] ?? 'active',
        ]));

        return response()->json([
            'success' => true,
            'id'      => $project->id,
            'name'    => $project->name,
        ], 201);
    }
}
