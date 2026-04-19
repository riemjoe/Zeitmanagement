<?php

namespace App\Http\Controllers;

use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\Webhook;
use App\Services\AutomationEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AutomationController extends Controller
{
    // ── Liste ─────────────────────────────────────────────────────────────────

    public function index()
    {
        $automations = Automation::withCount('logs')
            ->orderByDesc('updated_at')
            ->get();

        return view('automations.index', compact('automations'));
    }

    // ── Erstellen ─────────────────────────────────────────────────────────────

    public function create()
    {
        $automation = new Automation([
            'is_active'    => true,
            'trigger_type' => 'model_created',
            'yaml'         => $this->defaultYaml(),
        ]);

        $parsed = $automation->getParsedYaml();

        return view('automations.edit', [
            'automation'    => $automation,
            'triggerTypes'  => Automation::TRIGGER_TYPES,
            'triggerModels' => Automation::TRIGGER_MODELS,
            'actionTypes'   => Automation::ACTION_TYPES,
            'stepTypes'     => Automation::STEP_TYPES,
            'modelFields'   => Automation::MODEL_FIELDS,
            'parsedSteps'   => $parsed['steps'] ?? [],
            'parsedTrigger' => $parsed['trigger'] ?? [],
            'webhooks'      => Webhook::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        // YAML aus dem Flow-Builder zusammensetzen
        $data['yaml'] = $this->buildYamlFromRequest($request, $data);

        $automation = Automation::create($data);

        return redirect()->route('automations.index')
            ->with('success', "Automation «{$automation->name}» wurde erstellt.");
    }

    // ── Bearbeiten ────────────────────────────────────────────────────────────

    public function edit(Automation $automation)
    {
        $parsed = $automation->getParsedYaml();

        return view('automations.edit', [
            'automation'    => $automation,
            'triggerTypes'  => Automation::TRIGGER_TYPES,
            'triggerModels' => Automation::TRIGGER_MODELS,
            'actionTypes'   => Automation::ACTION_TYPES,
            'stepTypes'     => Automation::STEP_TYPES,
            'modelFields'   => Automation::MODEL_FIELDS,
            'parsedSteps'   => $parsed['steps'] ?? [],
            'parsedTrigger' => $parsed['trigger'] ?? [],
            'webhooks'      => Webhook::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Automation $automation)
    {
        $data = $this->validated($request);
        $data['yaml'] = $this->buildYamlFromRequest($request, $data);

        $automation->update($data);

        return redirect()->route('automations.index')
            ->with('success', "Automation «{$automation->name}» wurde gespeichert.");
    }

    // ── Löschen ───────────────────────────────────────────────────────────────

    public function destroy(Automation $automation)
    {
        $name = $automation->name;
        $automation->delete();

        return redirect()->route('automations.index')
            ->with('success', "Automation «{$name}» wurde gelöscht.");
    }

    // ── Aktiv/Inaktiv umschalten ─────────────────────────────────────────────

    public function toggle(Automation $automation)
    {
        $automation->update(['is_active' => !$automation->is_active]);
        $label = $automation->is_active ? 'aktiviert' : 'deaktiviert';

        return back()->with('success', "Automation «{$automation->name}» wurde {$label}.");
    }

    // ── Test-Ausführung ───────────────────────────────────────────────────────

    public function test(Request $request, Automation $automation)
    {
        $context = [];

        // Beispiel-Kontext aus dem Formular (JSON)
        $rawContext = $request->input('test_context', '{}');
        try {
            $context = json_decode($rawContext, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return response()->json([
                'success' => false,
                'error'   => 'Ungültiger JSON-Kontext.',
            ]);
        }

        $engine = new AutomationEngine();
        $log    = $engine->run($automation, $context, dryRun: true);

        return response()->json([
            'success'     => $log->status === 'success',
            'status'      => $log->status,
            'log'         => $log->log,
            'duration_ms' => $log->duration_ms,
            'error'       => $log->error_message,
        ]);
    }

    // ── YAML-Export ───────────────────────────────────────────────────────────

    public function exportYaml(Automation $automation)
    {
        $filename = Str::slug($automation->name) . '.yaml';

        return response($automation->yaml, 200, [
            'Content-Type'        => 'text/yaml',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Ausführungsprotokoll ──────────────────────────────────────────────────

    public function logs(Automation $automation)
    {
        $logs = $automation->logs()->paginate(20);

        return view('automations.logs', compact('automation', 'logs'));
    }

    // ── Webhook-Endpunkt (öffentlich) ─────────────────────────────────────────

    public function webhook(string $token)
    {
        // ── Neues System: zentraler Webhook mit n Automationen ─────────────
        $webhook = Webhook::where('token', $token)
            ->where('is_active', true)
            ->first();

        if ($webhook) {
            // HMAC-Signatur prüfen wenn Secret gesetzt
            if (!empty($webhook->secret)) {
                $signature = request()->header('X-Hub-Signature-256', '');
                $payload   = request()->getContent();

                if (!$webhook->verifySignature($payload, $signature)) {
                    return response()->json(['error' => 'Invalid signature.'], 403);
                }
            }

            $automations = $webhook->automations()
                ->where('is_active', true)
                ->where('trigger_type', 'webhook')
                ->get();

            if ($automations->isEmpty()) {
                return response()->json(['success' => true, 'message' => 'No active automations.']);
            }

            $context = request()->all();
            $engine  = new AutomationEngine();
            $logs    = [];

            foreach ($automations as $automation) {
                $log    = $engine->run($automation, $context);
                $logs[] = ['automation_id' => $automation->id, 'log_id' => $log->id, 'success' => $log->status === 'success'];
            }

            return response()->json(['success' => true, 'logs' => $logs]);
        }

        // ── Altes System (Rückwärtskompatibilität): webhook_token auf Automation ─
        $automation = Automation::where('webhook_token', $token)
            ->where('is_active', true)
            ->where('trigger_type', 'webhook')
            ->firstOrFail();

        $context = request()->all();
        $engine  = new AutomationEngine();
        $log     = $engine->run($automation, $context);

        return response()->json([
            'success' => $log->status === 'success',
            'log_id'  => $log->id,
        ]);
    }

    // ── Hilfsmethoden ─────────────────────────────────────────────────────────

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'          => 'required|string|max:200',
            'description'   => 'nullable|string|max:1000',
            'is_active'     => 'boolean',
            'trigger_type'  => 'required|string',
            'trigger_model' => 'nullable|string',
            'webhook_id'    => 'nullable|exists:webhooks,id',
        ]);
    }

    /**
     * Baut das YAML aus den übertragenen Schritt-Daten (JSON in steps_json).
     */
    private function buildYamlFromRequest(Request $request, array $data): string
    {
        // Wenn der Nutzer das YAML direkt bearbeitet hat, dieses nutzen
        if ($request->filled('yaml_raw')) {
            return trim($request->input('yaml_raw'));
        }

        $stepsJson = $request->input('steps_json', '[]');
        $steps = [];
        try {
            $steps = json_decode($stepsJson, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {}

        $definition = [
            'name'        => $data['name'],
            'description' => $data['description'] ?? '',
            'trigger'     => [
                'type'  => $data['trigger_type'],
                'model' => $data['trigger_model'] ?? null,
            ],
            'steps' => $steps,
        ];

        return Automation::buildYaml($definition);
    }

    private function defaultYaml(): string
    {
        return Automation::buildYaml([
            'name'        => 'Neue Automation',
            'description' => '',
            'trigger'     => [
                'type'  => 'model_created',
                'model' => 'Task',
            ],
            'steps' => [
                [
                    'id'     => 'step_1',
                    'type'   => 'action',
                    'action' => 'send_email',
                    'params' => [
                        'to'      => 'admin@example.com',
                        'subject' => 'Neue Aufgabe: {{ trigger.title }}',
                        'body'    => 'Eine neue Aufgabe wurde erstellt.',
                    ],
                ],
            ],
        ]);
    }
}
