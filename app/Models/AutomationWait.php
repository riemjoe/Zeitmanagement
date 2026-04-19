<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationWait extends Model
{
    protected $fillable = [
        'automation_id',
        'trigger_context',
        'accumulated_variables',
        'remaining_steps',
        'condition_model',
        'condition_id',
        'condition_field',
        'condition_operator',
        'condition_value',
        'check_interval_minutes',
        'next_check_at',
        'expires_at',
    ];

    protected $casts = [
        'next_check_at' => 'datetime',
        'expires_at'    => 'datetime',
    ];

    // ── Beziehungen ──────────────────────────────────────────────────────────

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    // ── Decoded Accessors ────────────────────────────────────────────────────

    public function getTriggerContextArray(): array
    {
        return json_decode($this->trigger_context, true) ?? [];
    }

    public function getAccumulatedVariablesArray(): array
    {
        return json_decode($this->accumulated_variables, true) ?? [];
    }

    public function getRemainingStepsArray(): array
    {
        return json_decode($this->remaining_steps, true) ?? [];
    }

    // ── Bedingungsprüfung ────────────────────────────────────────────────────

    /**
     * Prüft ob die gespeicherte Bedingung aktuell erfüllt ist.
     */
    public function checkCondition(): bool
    {
        $class = "\\App\\Models\\{$this->condition_model}";
        if (!class_exists($class)) {
            return false;
        }

        $record = $class::find($this->condition_id);
        if (!$record) {
            return false;
        }

        $actual = $record->{$this->condition_field} ?? null;
        $target = $this->condition_value;

        return match ($this->condition_operator) {
            '='          , '=='  => $actual == $target,
            '!='                 => $actual != $target,
            '>'                  => (float)$actual >  (float)$target,
            '<'                  => (float)$actual <  (float)$target,
            '>='                 => (float)$actual >= (float)$target,
            '<='                 => (float)$actual <= (float)$target,
            'contains'           => str_contains((string)$actual, (string)$target),
            'not_contains'       => !str_contains((string)$actual, (string)$target),
            default              => false,
        };
    }

    /**
     * Verschiebt den nächsten Prüfzeitpunkt um check_interval_minutes.
     */
    public function postponeCheck(): void
    {
        $this->update([
            'next_check_at' => now()->addMinutes($this->check_interval_minutes),
        ]);
    }
}
