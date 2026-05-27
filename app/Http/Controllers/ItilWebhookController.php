<?php

namespace App\Http\Controllers;

use App\Models\Incident;
use App\Models\ItilChange;
use App\Models\Problem;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Dedizierter öffentlicher Webhook-Endpunkt zum Erstellen von ITIL-Objekten.
 *
 * Authentifizierung über Bearer-Token oder Query-Parameter "token".
 * Token ist in den Einstellungen unter "itil_webhook_token" gespeichert.
 *
 * POST /api/itil/incidents   → Incident erstellen
 * POST /api/itil/problems    → Problem erstellen
 * POST /api/itil/changes     → Change erstellen
 *
 * Payload-Beispiel (Incident):
 * {
 *   "title": "Server nicht erreichbar",
 *   "description": "Produktion ausgefallen",
 *   "priority": "critical",
 *   "impact": "high",
 *   "urgency": "high",
 *   "category": "Infrastructure",
 *   "affected_service": "API Gateway",
 *   "reported_by": "monitoring@example.com"
 * }
 */
class ItilWebhookController extends Controller
{
    // ── Token-Authentifizierung ───────────────────────────────────────────────

    private function authenticate(Request $request): bool
    {
        $token = Setting::get('itil_webhook_token');

        if (empty($token)) {
            return false;
        }

        // Bearer-Token aus Authorization-Header
        $authHeader = $request->header('Authorization', '');
        if (Str::startsWith($authHeader, 'Bearer ')) {
            $provided = Str::after($authHeader, 'Bearer ');
            if (hash_equals($token, $provided)) {
                return true;
            }
        }

        // Query-Parameter ?token=…
        if (hash_equals($token, (string) $request->query('token', ''))) {
            return true;
        }

        return false;
    }

    private function unauthorized(): JsonResponse
    {
        return response()->json(['error' => 'Unauthorized. Bitte gültigen Token im Authorization-Header oder als ?token= übergeben.'], 401);
    }

    // ── Endpoints ─────────────────────────────────────────────────────────────

    public function createIncident(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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
        ]);

        $priority = $data['priority'] ?? 'medium';
        $sla      = Incident::calcSla($priority);

        $incident = Incident::create(array_merge($data, [
            'number'          => Incident::generateNumber(),
            'status'          => $data['status'] ?? 'open',
            'priority'        => $priority,
            'impact'          => $data['impact'] ?? 'medium',
            'urgency'         => $data['urgency'] ?? 'medium',
            'response_due_at' => $sla['response_due_at'],
            'resolve_due_at'  => $sla['resolve_due_at'],
        ]));

        return response()->json([
            'success' => true,
            'id'      => $incident->id,
            'number'  => $incident->number,
        ], 201);
    }

    public function createProblem(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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
        ]);

        $problem = Problem::create(array_merge($data, [
            'number'   => Problem::generateNumber(),
            'status'   => $data['status'] ?? 'open',
            'priority' => $data['priority'] ?? 'medium',
            'impact'   => $data['impact'] ?? 'medium',
        ]));

        return response()->json([
            'success' => true,
            'id'      => $problem->id,
            'number'  => $problem->number,
        ], 201);
    }

    public function createChange(Request $request): JsonResponse
    {
        if (!$this->authenticate($request)) {
            return $this->unauthorized();
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
        ]);

        $change = ItilChange::create(array_merge($data, [
            'number'   => ItilChange::generateNumber(),
            'status'   => $data['status'] ?? 'draft',
            'type'     => $data['type'] ?? 'normal',
            'priority' => $data['priority'] ?? 'medium',
            'impact'   => $data['impact'] ?? 'medium',
            'risk'     => $data['risk'] ?? 'medium',
        ]));

        return response()->json([
            'success' => true,
            'id'      => $change->id,
            'number'  => $change->number,
        ], 201);
    }

    /**
     * Aktuellen Webhook-Token anzeigen und neu generieren (authenticated, intern).
     */
    public static function ensureToken(): string
    {
        $token = Setting::get('itil_webhook_token');
        if (empty($token)) {
            $token = Str::random(48);
            Setting::set('itil_webhook_token', $token);
        }
        return $token;
    }
}
