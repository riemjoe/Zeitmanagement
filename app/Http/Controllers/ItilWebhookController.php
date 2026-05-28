<?php

namespace App\Http\Controllers;

use App\Mail\ItilChangeCreated;
use App\Mail\ItilIncidentCreated;
use App\Mail\ItilProblemCreated;
use App\Models\EmailLog;
use App\Models\Incident;
use App\Models\ItilChange;
use App\Models\Problem;
use App\Models\User;
use App\Models\WebhookToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Öffentliche Webhook-Endpunkte für ITIL-Objekte.
 *
 * Authentifizierung über Bearer-Token (webhook_tokens-Tabelle) mit Endpoint-Permission-Check.
 *
 * POST /api/itil/incidents  → Incident erstellen  (Permission: itil.incidents)
 * POST /api/itil/problems   → Problem erstellen   (Permission: itil.problems)
 * POST /api/itil/changes    → Change erstellen    (Permission: itil.changes)
 */
class ItilWebhookController extends Controller
{
    private function unauthorized(string $endpoint): JsonResponse
    {
        return response()->json(['error' => "Unauthorized. Kein Zugriff auf Endpoint \"{$endpoint}\"."], 401);
    }

    /**
     * Sendet eine Benachrichtigungs-Mail an alle aktiven Admins.
     */
    private function notifyAdmins(object $mailable, string $type, string $subject): void
    {
        $admins = User::where('role', 'admin')
            ->where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($admins as $admin) {
            try {
                Mail::to($admin->email)->send($mailable);
                EmailLog::record($type, $admin->email, $subject, 'sent');
            } catch (\Throwable $e) {
                Log::error("ITIL-Admin-Mail fehlgeschlagen ({$type}) an {$admin->email}: " . $e->getMessage());
                EmailLog::record($type, $admin->email, $subject, 'failed', $e->getMessage());
            }
        }
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    public function createIncident(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'itil.incidents')) {
            return $this->unauthorized('itil.incidents');
        }

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:' . implode(',', array_keys(Incident::STATUSES)),
            'priority'         => 'nullable|in:' . implode(',', array_keys(Incident::PRIORITIES)),
            'impact'           => 'nullable|in:high,medium,low',
            'urgency'          => 'nullable|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'reported_by'      => 'nullable|string|max:255',
            'workaround'       => 'nullable|string',
            'customer_id'      => 'nullable|exists:customers,id',
        ]);

        $priority   = $data['priority'] ?? 'medium';
        $customerId = isset($data['customer_id']) ? (int) $data['customer_id'] : null;
        $sla        = Incident::calcSla($priority, $customerId);

        $incident = Incident::create(array_merge($data, [
            'number'          => Incident::generateNumber(),
            'status'          => $data['status'] ?? 'open',
            'priority'        => $priority,
            'impact'          => $data['impact'] ?? 'medium',
            'urgency'         => $data['urgency'] ?? 'medium',
            'response_due_at' => $sla['response_due_at'],
            'resolve_due_at'  => $sla['resolve_due_at'],
        ]));

        $incident->loadMissing('customer');

        $subject = '[' . $incident->priority_label . '] Neuer Incident: ' . $incident->number . ' – ' . $incident->title;
        $this->notifyAdmins(new ItilIncidentCreated($incident), 'itil_incident_created', $subject);

        return response()->json([
            'success' => true,
            'id'      => $incident->id,
            'number'  => $incident->number,
        ], 201);
    }

    public function createProblem(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'itil.problems')) {
            return $this->unauthorized('itil.problems');
        }

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:' . implode(',', array_keys(Problem::STATUSES)),
            'priority'         => 'nullable|in:' . implode(',', array_keys(Problem::PRIORITIES)),
            'impact'           => 'nullable|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'root_cause'       => 'nullable|string',
            'workaround'       => 'nullable|string',
            'customer_id'      => 'nullable|exists:customers,id',
        ]);

        $problem = Problem::create(array_merge($data, [
            'number'   => Problem::generateNumber(),
            'status'   => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'impact'   => $data['impact'] ?? 'medium',
        ]));

        $problem->loadMissing('customer');

        $subject = '[' . $problem->priority_label . '] Neues Problem: ' . $problem->number . ' – ' . $problem->title;
        $this->notifyAdmins(new ItilProblemCreated($problem), 'itil_problem_created', $subject);

        return response()->json([
            'success' => true,
            'id'      => $problem->id,
            'number'  => $problem->number,
        ], 201);
    }

    public function createChange(Request $request): JsonResponse
    {
        if (!WebhookToken::authenticate($request, 'itil.changes')) {
            return $this->unauthorized('itil.changes');
        }

        $data = $request->validate([
            'title'            => 'required|string|max:255',
            'description'      => 'nullable|string',
            'status'           => 'nullable|in:' . implode(',', array_keys(ItilChange::STATUSES)),
            'type'             => 'nullable|in:' . implode(',', array_keys(ItilChange::TYPES)),
            'priority'         => 'nullable|in:' . implode(',', array_keys(ItilChange::PRIORITIES)),
            'impact'           => 'nullable|in:high,medium,low',
            'risk'             => 'nullable|in:high,medium,low',
            'category'         => 'nullable|string|max:100',
            'affected_service' => 'nullable|string|max:255',
            'requested_by'     => 'nullable|string|max:255',
            'customer_id'      => 'nullable|exists:customers,id',
            'planned_start_at' => 'nullable|date',
            'planned_end_at'   => 'nullable|date',
        ]);

        $change = ItilChange::create(array_merge($data, [
            'number'   => ItilChange::generateNumber(),
            'status'   => $data['status'] ?? 'draft',
            'type'     => $data['type'] ?? 'normal',
            'priority' => $data['priority'] ?? 'medium',
            'impact'   => $data['impact'] ?? 'medium',
            'risk'     => $data['risk'] ?? 'medium',
        ]));

        $change->loadMissing('customer');

        $subject = '[' . $change->type_label . '] Neuer Change: ' . $change->number . ' – ' . $change->title;
        $this->notifyAdmins(new ItilChangeCreated($change), 'itil_change_created', $subject);

        return response()->json([
            'success' => true,
            'id'      => $change->id,
            'number'  => $change->number,
        ], 201);
    }
}
