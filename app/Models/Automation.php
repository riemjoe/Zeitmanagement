<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Automation extends Model
{
    protected $fillable = [
        'name', 'description', 'is_active', 'yaml',
        'trigger_type', 'trigger_model', 'webhook_token', 'webhook_id',
        'last_run_at', 'run_count',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'last_run_at' => 'datetime',
    ];

    // ── Trigger-Typen ────────────────────────────────────────────────────────

    public const TRIGGER_TYPES = [
        'model_created'      => 'Model erstellt',
        'model_updated'      => 'Model geändert',
        'model_deleted'      => 'Model gelöscht',
        'webhook'            => 'Webhook aufgerufen',
        'export_created'     => 'Export erstellt',
        'import_created'     => 'Import abgeschlossen',
        'budget_reached'     => 'Budget erreicht',
        'time_limit_reached' => 'Zeitlimit erreicht',
        'deadline_reached'   => 'Deadline erreicht',
        'scheduled'          => 'Zeitgesteuert (Cron)',
    ];

    // ── Verfügbare Modelle für Trigger ───────────────────────────────────────

    public const TRIGGER_MODELS = [
        'Task'             => 'Aufgabe',
        'Project'          => 'Projekt',
        'Customer'         => 'Kunde',
        'Invoice'          => 'Rechnung',
        'Ticket'           => 'Ticket',
        'Expense'          => 'Ausgabe',
        'TimeEntry'        => 'Zeiteintrag',
        'Quote'            => 'Angebot',
        'Contract'         => 'Vertrag',
        'Milestone'        => 'Meilenstein',
        // ITIL
        'Incident'         => 'ITIL Incident',
        'Problem'          => 'ITIL Problem',
        'ItilChange'       => 'ITIL Change',
    ];

    // ── Aktions-Typen ────────────────────────────────────────────────────────

    public const ACTION_TYPES = [
        'send_email'      => 'E-Mail versenden',
        'get_variables'   => 'Variablen laden',
        'create_model'    => 'Datensatz erstellen',
        'update_model'    => 'Datensatz ändern',
        'delete_model'    => 'Datensatz löschen',
        'add_message'     => 'Nachricht hinzufügen',
        'call_webhook'    => 'Webhook aufrufen',
        'set_variable'    => 'Variable setzen',
        'wait_until'      => 'Warten bis …',
    ];

    // ── Model-Felder (für Variablen-Panel) ───────────────────────────────────

    public const MODEL_FIELDS = [
        'Task'       => ['id', 'title', 'status', 'priority', 'description', 'due_date', 'project_id', 'user_id', 'created_at', 'updated_at'],
        'Project'    => ['id', 'name', 'status', 'description', 'customer_id', 'budget', 'deadline', 'created_at', 'updated_at'],
        'Customer'   => ['id', 'name', 'email', 'phone', 'company', 'address', 'created_at', 'updated_at'],
        'Invoice'    => ['id', 'number', 'status', 'total', 'customer_id', 'due_date', 'created_at', 'updated_at'],
        'Ticket'     => ['id', 'ticket_number', 'title', 'status', 'priority', 'customer_id', 'customer_email', 'description', 'source', 'sla_deadline', 'created_at', 'updated_at'],
        'Expense'    => ['id', 'title', 'amount', 'category', 'project_id', 'date', 'created_at', 'updated_at'],
        'TimeEntry'  => ['id', 'duration', 'description', 'project_id', 'user_id', 'started_at', 'created_at'],
        'Quote'      => ['id', 'number', 'status', 'total', 'customer_id', 'valid_until', 'created_at', 'updated_at'],
        'Contract'   => ['id', 'title', 'status', 'customer_id', 'start_date', 'end_date', 'created_at', 'updated_at'],
        'Milestone'  => ['id', 'title', 'status', 'project_id', 'due_date', 'created_at', 'updated_at'],
        // ITIL
        'Incident'   => ['id', 'number', 'title', 'status', 'priority', 'impact', 'urgency', 'category', 'affected_service', 'customer_id', 'ticket_id', 'problem_id', 'assigned_to', 'reported_by', 'response_due_at', 'resolve_due_at', 'resolved_at', 'created_at', 'updated_at'],
        'Problem'    => ['id', 'number', 'title', 'status', 'priority', 'impact', 'category', 'affected_service', 'customer_id', 'assigned_to', 'root_cause', 'workaround', 'resolved_at', 'created_at', 'updated_at'],
        'ItilChange' => ['id', 'number', 'title', 'status', 'type', 'priority', 'impact', 'risk', 'category', 'affected_service', 'customer_id', 'ticket_id', 'assigned_to', 'requested_by', 'planned_start_at', 'planned_end_at', 'completed_at', 'created_at', 'updated_at'],
    ];

    // ── Schritt-Typen (inkl. Kontrollfluss) ──────────────────────────────────

    public const STEP_TYPES = [
        'action'  => 'Aktion',
        'if'      => 'Wenn / Sonst',
        'foreach' => 'Für jeden (Schleife)',
    ];

    // ── Beziehungen ──────────────────────────────────────────────────────────

    public function logs(): HasMany
    {
        return $this->hasMany(AutomationLog::class)->latest();
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    // ── Hilfsmethoden ────────────────────────────────────────────────────────

    public function generateWebhookToken(): void
    {
        $this->webhook_token = Str::random(40);
    }

    public function getTriggerLabel(): string
    {
        return self::TRIGGER_TYPES[$this->trigger_type] ?? $this->trigger_type;
    }

    /**
     * Parst das YAML in ein PHP-Array.
     * Schlägt das Parsen fehl (z.B. wegen kaputzer Einrückung), wird automatisch
     * repairYaml() als Fallback versucht.
     */
    public function getParsedYaml(): array
    {
        try {
            return \Symfony\Component\Yaml\Yaml::parse($this->yaml) ?? [];
        } catch (\Throwable) {
            // Fallback: kaputte Einrückung reparieren und erneut parsen
            try {
                $repaired = static::repairYaml($this->yaml);
                return \Symfony\Component\Yaml\Yaml::parse($repaired) ?? [];
            } catch (\Throwable) {
                return [];
            }
        }
    }

    /**
     * Repariert die fehlerhafte Einrückung von YAML-Listen-Items.
     *
     * Der JS-Serializer erzeugte früher für Array-Items "indent+2" statt "indent+1",
     * wodurch Folgezeilen 2 Leerzeichen zu viel bekamen und das YAML ungültig wurde:
     *
     *   steps:
     *     - id: step_101
     *         type: action    ← 6 statt 4 Leerzeichen (kaputt)
     *
     * Diese Methode erkennt das Muster und subtrahiert den Überschuss.
     */
    public static function repairYaml(string $yaml): string
    {
        $lines = explode("\n", $yaml);
        $n     = count($lines);
        $result = [];
        $offset = 0;
        $listItemBaseIndent = -1;

        for ($i = 0; $i < $n; $i++) {
            $line    = $lines[$i];
            $trimmed = ltrim($line);

            if ($trimmed === '') {
                $result[] = $line;
                continue;
            }

            $indent = strlen($line) - strlen($trimmed);

            // Neue Listen-Item-Zeile erkennen: "  - key: value"
            if (preg_match('/^(\s*)- \w/', $line)) {
                $listItemBaseIndent = $indent;
                $offset = 0;
                $result[] = $line;

                // Nächste nicht-leere Zeile prüfen ob Einrückung falsch ist
                for ($j = $i + 1; $j < $n; $j++) {
                    $nextTrimmed = ltrim($lines[$j]);
                    if ($nextTrimmed === '') {
                        continue;
                    }
                    if (preg_match('/^\s*-/', $lines[$j])) {
                        break; // Nächstes Listen-Item → kein Fehler
                    }
                    $nextIndent      = strlen($lines[$j]) - strlen($nextTrimmed);
                    $expectedIndent  = $listItemBaseIndent + 2;
                    if ($nextIndent > $expectedIndent) {
                        $offset = $nextIndent - $expectedIndent;
                    }
                    break;
                }
                continue;
            }

            // Einrückung korrigieren wenn wir uns im kaputten Listen-Item befinden
            if ($offset > 0 && $listItemBaseIndent >= 0 && $indent > $listItemBaseIndent) {
                $corrected = max($listItemBaseIndent + 2, $indent - $offset);
                $result[]  = str_repeat(' ', $corrected) . $trimmed;
                continue;
            }

            // Zurück auf äußere Ebene → Reset
            if ($indent <= $listItemBaseIndent) {
                $listItemBaseIndent = -1;
                $offset = 0;
            }

            $result[] = $line;
        }

        return implode("\n", $result);
    }

    /**
     * Baut YAML aus einem strukturierten Array und speichert es.
     */
    public static function buildYaml(array $data): string
    {
        return \Symfony\Component\Yaml\Yaml::dump($data, 6, 2, \Symfony\Component\Yaml\Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
}
