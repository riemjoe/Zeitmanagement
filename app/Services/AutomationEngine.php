<?php

namespace App\Services;

use App\Exceptions\AutomationWaitingException;
use App\Models\Automation;
use App\Models\AutomationLog;
use App\Models\AutomationWait;
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
     * Eine bestimmte Automation von Anfang an ausführen (auch als Trockentest).
     */
    public function run(Automation $automation, array $context = [], bool $dryRun = false): AutomationLog
    {
        $this->logLines  = [];
        $this->variables = [];
        $this->dryRun    = $dryRun;

        $definition = $automation->getParsedYaml();
        $steps      = $definition['steps'] ?? [];

        return $this->execute($automation, $context, $steps);
    }

    /**
     * Eine pausierte Automation (wait_until) ab den gespeicherten Restschritten fortsetzen.
     */
    public function runFromWait(AutomationWait $wait): AutomationLog
    {
        $this->logLines  = [];
        $this->variables = $wait->getAccumulatedVariablesArray();
        $this->dryRun    = false;

        $this->log("▶ Fortsetzung nach 'Warten bis' (Wait-ID #{$wait->id})");

        $log = $this->execute(
            $wait->automation,
            $wait->getTriggerContextArray(),
            $wait->getRemainingStepsArray(),
            resuming: true,
        );

        // Wait-Eintrag löschen – Automation läuft durch oder hat einen neuen Wait angelegt
        $wait->delete();

        return $log;
    }

    // ── Interne Ausführungs-Pipeline ─────────────────────────────────────────

    private function execute(
        Automation $automation,
        array      $context,
        array      $steps,
        bool       $resuming = false,
    ): AutomationLog {
        $startMs  = microtime(true);
        $status   = 'success';
        $errorMsg = null;

        if (!$resuming) {
            $this->log("▶ Starte Automation: «{$automation->name}»" . ($this->dryRun ? ' [TEST]' : ''));
            $this->log("  Trigger: {$automation->trigger_type}");
            $this->log("  Kontext-Keys: " . implode(', ', array_keys($context)));
        }

        try {
            $this->executeSteps($steps, $context);
            $this->log("✓ Erfolgreich abgeschlossen.");
        } catch (AutomationWaitingException $waitEx) {
            // Automation pausiert – Zustand persistieren
            $status = 'waiting';
            $this->log("⏸ Warten bis Bedingung erfüllt: {$waitEx->conditionModel}.{$waitEx->conditionField} {$waitEx->conditionOperator} '{$waitEx->conditionValue}'");
            $this->log("  Nächste Prüfung in {$waitEx->checkIntervalMinutes} Min. · Timeout nach {$waitEx->timeoutMinutes} Min.");

            if (!$this->dryRun) {
                AutomationWait::create([
                    'automation_id'         => $automation->id,
                    'trigger_context'       => json_encode($context),
                    'accumulated_variables' => json_encode($this->variables),
                    'remaining_steps'       => json_encode($waitEx->remainingSteps),
                    'condition_model'       => $waitEx->conditionModel,
                    'condition_id'          => $waitEx->conditionId,
                    'condition_field'       => $waitEx->conditionField,
                    'condition_operator'    => $waitEx->conditionOperator,
                    'condition_value'       => $waitEx->conditionValue,
                    'check_interval_minutes'=> $waitEx->checkIntervalMinutes,
                    'next_check_at'         => now()->addMinutes($waitEx->checkIntervalMinutes),
                    'expires_at'            => now()->addMinutes($waitEx->timeoutMinutes),
                ]);
            }
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

        if (!$this->dryRun && $status !== 'waiting') {
            $automation->increment('run_count');
            $automation->update(['last_run_at' => now()]);
        }

        return $logEntry;
    }

    // ── Schrittausführung ────────────────────────────────────────────────────

    /**
     * Führt eine Liste von Schritten sequentiell aus.
     * Bei wait_until: sammelt die verbleibenden Schritte und wirft AutomationWaitingException.
     */
    private function executeSteps(array $steps, array $context): void
    {
        foreach ($steps as $index => $step) {
            $type   = $step['type']   ?? 'action';
            $action = $step['action'] ?? '';

            // wait_until vor dem normalen Dispatching abfangen,
            // damit wir die verbleibenden Schritte kennen
            if ($type === 'action' && $action === 'wait_until') {
                $params   = $this->resolveParams($step['params'] ?? [], $context);
                $this->log("  → Warten bis: {$params['model']}.{$params['field']} {$params['operator']} '{$params['value']}'");

                if ($this->dryRun) {
                    $this->log("    [TEST] Bedingungsprüfung übersprungen.");
                    continue;
                }

                if (!$this->checkWaitCondition($params)) {
                    // Bedingung nicht erfüllt → restliche Schritte sichern und pausieren
                    throw new AutomationWaitingException(
                        remainingSteps:        array_slice($steps, $index + 1),
                        conditionModel:        $params['model']    ?? '',
                        conditionId:           (string)($params['id'] ?? ''),
                        conditionField:        $params['field']    ?? '',
                        conditionOperator:     $params['operator'] ?? '=',
                        conditionValue:        $params['value']    ?? '',
                        checkIntervalMinutes:  (int)($params['check_interval_minutes'] ?? 5),
                        timeoutMinutes:        (int)($params['timeout_minutes']        ?? 1440),
                    );
                }

                $this->log("    ✓ Bedingung bereits erfüllt – kein Warten nötig.");
                continue;
            }

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
        $variable      = $step['variable']   ?? 'item';
        $innerSteps    = $step['steps']       ?? [];

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
        $data      = $params['data']  ?? [];
        $alias     = $params['as']    ?? null;

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class)) {
            $this->log("    ✗ Model {$modelName} nicht gefunden.");
            return;
        }

        $record = $class::create($data);
        $this->log("    ✓ {$modelName} #{$record->id} erstellt.");

        // Felder als Variablen bereitstellen wenn 'as' angegeben
        if ($alias) {
            $this->storeRecordAsVariables($record, $alias);
            $this->log("    ✓ Felder von {$modelName} #{$record->id} als \${{ {$alias}.* }} verfügbar.");
        }
    }

    private function actionUpdateModel(array $params): void
    {
        $modelName = $params['model'] ?? '';
        $id        = $params['id']    ?? null;
        $data      = $params['data']  ?? [];
        $alias     = $params['as']    ?? null;

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
        $record->refresh(); // aktuelle DB-Werte laden
        $this->log("    ✓ {$modelName} #{$id} aktualisiert.");

        // Aktualisierte Felder als Variablen bereitstellen
        if ($alias) {
            $this->storeRecordAsVariables($record, $alias);
            $this->log("    ✓ Aktualisierte Felder von {$modelName} #{$id} als \${{ {$alias}.* }} verfügbar.");
        }
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

        $count = $this->storeRecordAsVariables($record, $alias);
        $this->log("    ✓ {$count} Variablen von {$modelName} #{$id} als \${{ {$alias}.* }} geladen.");
    }

    // ── Warten-bis Hilfsmethode ───────────────────────────────────────────────

    /**
     * Prüft eine wait_until-Bedingung gegen den aktuellen DB-Zustand.
     */
    private function checkWaitCondition(array $params): bool
    {
        $modelName = $params['model']    ?? '';
        $id        = $params['id']       ?? null;
        $field     = $params['field']    ?? '';
        $operator  = $params['operator'] ?? '=';
        $value     = $params['value']    ?? '';

        if (!$modelName || !$id || !$field) {
            return false;
        }

        $class = "\\App\\Models\\{$modelName}";
        if (!class_exists($class)) {
            return false;
        }

        $record = $class::find($id);
        if (!$record) {
            return false;
        }

        $actual = $record->$field ?? null;

        return match ($operator) {
            '=', '==' => $actual == $value,
            '!='      => $actual != $value,
            '>'       => (float)$actual >  (float)$value,
            '<'       => (float)$actual <  (float)$value,
            '>='      => (float)$actual >= (float)$value,
            '<='      => (float)$actual <= (float)$value,
            'contains'     => str_contains((string)$actual, (string)$value),
            'not_contains' => !str_contains((string)$actual, (string)$value),
            default        => false,
        };
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
        // Oder flacher Schlüssel: "task.title" direkt in $context
        if (array_key_exists($key, $context)) {
            return $context[$key];
        }

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

    // ── Hilfsmethoden ────────────────────────────────────────────────────────

    /**
     * Speichert alle Felder eines Eloquent-Records als "{alias}.{feld}" Variablen.
     * Gibt die Anzahl gespeicherter Felder zurück.
     */
    private function storeRecordAsVariables($record, string $alias): int
    {
        $count = 0;
        foreach ($record->toArray() as $field => $value) {
            $this->variables["{$alias}.{$field}"] = $value;
            $count++;
        }
        return $count;
    }

    private function log(string $line): void
    {
        $this->logLines[] = now()->format('H:i:s') . ' ' . $line;
    }
}
