<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Project;
use App\Models\Setting;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Öffentliche API-Webhook-Endpunkte für die Erstellung von Tickets, Kunden und Projekten.
 *
 * Authentifizierung über Bearer-Token (Authorization: Bearer <token>)
 * oder Query-Parameter (?token=<token>).
 * Token wird unter "webhook_token" in den Einstellungen gespeichert.
 *
 * POST /api/webhooks/tickets    → Ticket erstellen
 * POST /api/webhooks/customers  → Kunden erstellen
 * POST /api/webhooks/projects   → Projekt erstellen
 */
class PublicWebhookController extends Controller
{
    // ── Token-Authentifizierung ──────────────────────────────────────────────

    private function authenticate(Request $request): bool
    {
        $token = Setting::get('webhook_token');

        if (empty($token)) {
            return false;
        }

        $authHeader = $request->header('Authorization', '');
        if (Str::startsWith($authHeader, 'Bearer ')) {
            $provided = Str::after($authHeader, 'Bearer ');
            if (hash_equals($token, $provided)) {
                return true;
            }
        }

        if (hash_equals($token, (string) $request->query('token', ''))) {
            return true;
        }

        return false;
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json([
            'error' => 'Unauthorized. Bitte gültigen Token im Authorization-Header (Bearer) oder als ?token= übergeben.',
        ], 401);
    }

    // ── Endpoints ────────────────────────────────────────────────────────────

    /** POST /api/webhooks/tickets */
    public function createTicket(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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

    // ── Token-Verwaltung (intern) ────────────────────────────────────────────

    public static function ensureToken(): string
    {
        $token = Setting::get('webhook_token');
        if (empty($token)) {
            $token = Str::random(48);
            Setting::set('webhook_token', $token);
        }
        return $token;
    }

    public function regenerateToken(): \Illuminate\Http\RedirectResponse
    {
        $token = Str::random(48);
        Setting::set('webhook_token', $token);

        return back()->with('success', 'Webhook-Token wurde neu generiert. Bitte alle externen Systeme aktualisieren.');
    }
}
