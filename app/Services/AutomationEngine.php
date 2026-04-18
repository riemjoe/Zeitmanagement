<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\AutomationLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AutomationEngine
{
    private array $logLines  = [];
    private array $variables = [];
    private bool  $dryRun    = false;

    // ── Öffentliche Einstiegspunkte ──────────────────────────────────────────

    /**
     * Alle aktiven Automationen eines Trigger-Typs ausführen.
     */
    public static function dispatch(string $triggerType, array $context = [], ?string $triggerModel = null): void
    {
        $query = Automation::where('is_active', true)
                           ->where('trigger_type', $triggerType);

        if ($triggerModel) {
            $query->where('trigger_model', $triggerModel);
        }

        foreach ($query->get() as $automation) {
            (new self())->run($automation, $context);
        }
    }

    /**
     * Eine bestimmte Automation ausführen (auch als Trockentest).
     */
    public function run(Automation $automation, array $context = [], bool $dryRun = false): AutomationLog
    {
        $this->logLines  = [];
        $this->variables = [];
        $this->dryRun    = $dryRun;

        $startMs = microtime(true);
        $status  = 'success';
        $errorMsg = null;

        $this->log("▶ Starte Automation: «{$automation->name}»" . ($dryRun ? ' [TEST]' : ''));
        $this->log("  Trigger: {$automation->trigger_type}");
        $this->log("  Kontext-Keys: " . implode(', ', array_keys($context)));

        try {
            $definition = $automation->getParsedYaml();
            $steps = $definition['steps'] ?? [];
            $this->executeSteps($steps, $context);
            $this->log("✓ Erfolgreich abgeschlossen.");
        } catch (\Throwable $e) {
            $status   = 'error';
            $errorMsg = $e->getMessage();
            $this->log("✗ Fehler: {$errorMsg}");
            Log::error("AutomationEngine error [{$automation->id}]: {$errorMsg}");
        }

        $durationMs = round((microtime(true) - $startMs) * 1000, 2);

        $logEntry = AutomationLog::create([
            'automation_id' => $automation->id,
            'status'        => $status,
            'context'       => json_encode($context),
            'log'           => implode("\n", $this->logLines),
            'error_message' => $errorMsg,
            'duration_ms'   => $durationMs,
        ]);

        if (!$dryRun) {
            $automation->increment('run_count');
            $automation->update(['last_run_at' => now()]);
        }

        return $logEntry;
    }

    // ── Schrittausführung ────────────────────────────────────────────────────

    private function executeSteps(array $steps, array $context): void
    {
        foreach ($steps as $step) {
            $type = $step['type'] ?? 'action';

            match ($type) {
                'action'  => $this->executeAction($step, $context),
                'if'      => $this->executeIf($step, $context),
                'foreach' => $this->executeForeach($step, $context),
                default   => $this->log("  ⚠ Unbekannter Schritt-Typ: {$type}"),
            };
        }
    }

    // ── Aktionen ─────────────────────────────────────────────────────────────

    private function executeAction(array $step, array $context): void
    {
        $action = $step['action'] ?? '';
        $params = $step['params'] ?? [];

        // Template-Variablen ersetzen
        $params = $this->resolveParams($params, $context);

        $this->log("  → Aktion: {$action}");

        if ($this->dryRun) {
            $this->log("    [TEST] Parameter: " . json_encode($params, JSON_UNESCAPED_UNICODE));
            return;
        }

        match ($action) {
            'send_email'    => $this->actionSendEmail($params),
            'get_variables' => $this->actionGetVariables($params),
            'create_model'  => $this->actionCreateModel($params),
            'update_model'  => $this->actionUpdateModel($params),
            'delete_model'  => $this->actionDeleteModel($params),
            'add_message'   => $this->actionAddMessage($params),
            'call_webhook'  => $this->actionCallWebhook($params),
            'set_variable'  => $this->actionSetVariable($params),
            default         => $this->log("    ⚠ Unbekannte Aktion: {$action}"),
        };
    }

    // ── Kontrollfluss ────────────────────────────────────────────────────────

    private function executeIf(array $step, array $context): void
    {
        $condition = $step['condition'] ?? 'false';
        $result    = $this->evaluateCondition($condition, $context);

        $this->log("  → Wenn ({$condition}): " . ($result ? 'wahr → then' : 'falsch → else'));

        $branch = $result ? ($step['then'] ?? []) : ($step['else'] ?? []);
        $this->executeSteps($branch, $context);
    }

    private function executeForeach(array $step, array $context): void
    {
        $collectionKey = $step['collection'] ?? '';
        $variable      = $step['variable'] ?? 'item';
        $innerSteps    = $step['steps'] ?? [];

        $collection = $this->resolveValue($collectionKey, $context);

        if (!is_array($collection)) {
            $this->log("  ⚠ foreach: «{$collectionKey}» ist keine Liste.");
            return;
        }

        $this->log("  → Für jeden in «{$collectionKey}» (" . count($collection) . " Einträge)");

        foreach ($collection as $i => $item) {
            $this->log("    Iteration {$i}:");
            $innerContext = array_merge($context, [$variable => $item]);
            $this->executeSteps($innerSteps, $innerContext);
        }
    }

    // ── Einzel-Aktionen ──────────────────────────────────────────────────────

    private function actionSendEmail(array $params): void
    {
        $to      = $params['to']      ?? '';
        $subject = $params['subject'] ?? '(kein Betreff)';
        $body    = $params['body']    ?? '';

        if (!$to) {
            $this->log("    ⚠ Kein Empfänger angegeben.");
            return;
        }

        try {
            Mail::raw($body, function ($msg) use ($to, $subject) {
                $msg->to($to)->subject($subject);
            });
            $this->log("    ✓ E-Mail an {$to} gesendet.");
        } catch (\Throwable $e) {
            $this->log("    ✗ E-Mail-Fehler: " . $e->getMessage());
        }
    }

    private function actionCreateModel(array $params): void
    {
        $modelName = $params['model'] ?? '';
        $data      = $params['data'] ?? [];

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class)) {
            $this->log("    ✗ Model {$modelName} nicht gefunden.");
            return;
        }

        $record = $class::create($data);
        $this->log("    ✓ {$modelName} #{$record->id} erstellt.");
    }

    private function actionUpdateModel(array $params): void
    {
        $modelName = $params['model'] ?? '';
        $id        = $params['id']    ?? null;
        $data      = $params['data']  ?? [];

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class) || !$id) {
            $this->log("    ✗ Model/ID fehlt.");
            return;
        }

        $record = $class::find($id);
        if (!$record) {
            $this->log("    ✗ {$modelName} #{$id} nicht gefunden.");
            return;
        }

        $record->update($data);
        $this->log("    ✓ {$modelName} #{$id} aktualisiert.");
    }

    private function actionDeleteModel(array $params): void
    {
        $modelName = $params['model'] ?? '';
        $id        = $params['id']    ?? null;

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class) || !$id) {
            $this->log("    ✗ Model/ID fehlt.");
            return;
        }

        $record = $class::find($id);
        if (!$record) {
            $this->log("    ✗ {$modelName} #{$id} nicht gefunden.");
            return;
        }

        $record->delete();
        $this->log("    ✓ {$modelName} #{$id} gelöscht.");
    }

    private function actionAddMessage(array $params): void
    {
        $projectId = $params['project_id'] ?? null;
        $message   = $params['message']    ?? '';

        if ($projectId) {
            \App\Models\ProjectMessage::create([
                'project_id' => $projectId,
                'user_id'    => auth()->id() ?? 1,
                'body'       => $message,
            ]);
            $this->log("    ✓ Nachricht zu Projekt #{$projectId} hinzugefügt.");
        } else {
            $this->log("    ⚠ Keine project_id angegeben.");
        }
    }

    private function actionCallWebhook(array $params): void
    {
        $url     = $params['url']     ?? '';
        $method  = strtoupper($params['method'] ?? 'POST');
        $payload = $params['payload'] ?? [];

        if (!$url) {
            $this->log("    ✗ Keine URL angegeben.");
            return;
        }

        try {
            $response = Http::timeout(10)->$method($url, $payload);
            $this->log("    ✓ Webhook {$method} {$url} → HTTP {$response->status()}");
        } catch (\Throwable $e) {
            $this->log("    ✗ Webhook-Fehler: " . $e->getMessage());
        }
    }

    private function actionSetVariable(array $params): void
    {
        $name  = $params['name']  ?? '';
        $value = $params['value'] ?? null;

        if ($name) {
            $this->variables[$name] = $value;
            $this->log("    ✓ Variable \${$name} = " . json_encode($value));
        }
    }

    /**
     * Lädt alle Felder eines Model-Datensatzes als Variablen unter einem Alias.
     * Beispiel: model=Task, id=5, as=task → {{ task.title }}, {{ task.status }} …
     */
    private function actionGetVariables(array $params): void
    {
        $modelName = $params['model'] ?? '';
        $id        = $params['id']    ?? null;
        $alias     = $params['as']    ?? strtolower($modelName);

        if (!$modelName || !$id) {
            $this->log("    ✗ get_variables: Model oder ID fehlt.");
            return;
        }

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class)) {
            $this->log("    ✗ get_variables: Model {$modelName} nicht gefunden.");
            return;
        }

        $record = $class::find($id);
        if (!$record) {
            $this->log("    ✗ get_variables: {$modelName} #{$id} nicht gefunden.");
            return;
        }

        $count = 0;
        foreach ($record->toArray() as $field => $value) {
            $this->variables["{$alias}.{$field}"] = $value;
            $count++;
        }

        $this->log("    ✓ {$count} Variablen von {$modelName} #{$id} als \${{ {$alias}.* }} geladen.");
    }

    // ── Ausdruck-Auswertung ──────────────────────────────────────────────────

    /**
     * Einfache Bedingungsauswertung: "field == 'value'", "field > 5", etc.
     */
    private function evaluateCondition(string $condition, array $context): bool
    {
        // Template-Variablen zuerst ersetzen
        $condition = $this->resolveTemplate($condition, $context);

        // Einfache Vergleiche: ==, !=, >, <, >=, <=
        $patterns = [
            '/^(.+?)\s*==\s*[\'"](.+?)[\'"]\s*$/'  => fn($m) => trim($m[1]) == $m[2],
            '/^(.+?)\s*!=\s*[\'"](.+?)[\'"]\s*$/'  => fn($m) => trim($m[1]) != $m[2],
            '/^(.+?)\s*==\s*(\d+\.?\d*)\s*$/'      => fn($m) => trim($m[1]) == $m[2],
            '/^(.+?)\s*!=\s*(\d+\.?\d*)\s*$/'      => fn($m) => trim($m[1]) != $m[2],
            '/^(.+?)\s*>\s*(\d+\.?\d*)\s*$/'       => fn($m) => (float)trim($m[1]) > (float)$m[2],
            '/^(.+?)\s*<\s*(\d+\.?\d*)\s*$/'       => fn($m) => (float)trim($m[1]) < (float)$m[2],
            '/^(.+?)\s*>=\s*(\d+\.?\d*)\s*$/'      => fn($m) => (float)trim($m[1]) >= (float)$m[2],
            '/^(.+?)\s*<=\s*(\d+\.?\d*)\s*$/'      => fn($m) => (float)trim($m[1]) <= (float)$m[2],
        ];

        foreach ($patterns as $pattern => $eval) {
            if (preg_match($pattern, $condition, $m)) {
                return (bool)$eval($m);
            }
        }

        // Boolesche Literale
        if (in_array(strtolower(trim($condition)), ['true', '1', 'yes'])) return true;
        if (in_array(strtolower(trim($condition)), ['false', '0', 'no', ''])) return false;

        return !empty(trim($condition));
    }

    /**
     * Rekursiv Template-Platzhalter {{ key }} in Params ersetzen.
     */
    private function resolveParams(array $params, array $context): array
    {
        array_walk_recursive($params, function (&$value) use ($context) {
            if (is_string($value)) {
                $value = $this->resolveTemplate($value, $context);
            }
        });
        return $params;
    }

    private function resolveTemplate(string $template, array $context): string
    {
        $merged = array_merge($context, $this->variables);

        return preg_replace_callback('/\{\{\s*(.+?)\s*\}\}/', function ($matches) use ($merged) {
            return $this->resolveValue($matches[1], $merged) ?? $matches[0];
        }, $template);
    }

    private function resolveValue(string $key, array $context): mixed
    {
        // Punkt-Notation: "trigger.title" → $context['trigger']['title']
        $parts = explode('.', $key);
        $value = $context;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } elseif (is_object($value) && isset($value->$part)) {
                $value = $value->$part;
            } else {
                return null;
            }
        }

        return $value;
    }

    private function log(string $line): void
    {
        $this->logLines[] = now()->format('H:i:s') . ' ' . $line;
    }
}
