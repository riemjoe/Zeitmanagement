<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutomationLog extends Model
{
    protected $fillable = [
        'automation_id', 'status', 'context', 'log',
        'error_message', 'duration_ms',
    ];

    protected $casts = [
        'duration_ms' => 'float',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(Automation::class);
    }

    /**
     * Dekodierter Kontext als Array.
     */
    public function getContextArray(): array
    {
        return $this->context ? json_decode($this->context, true) : [];
    }

    /**
     * Log-Zeilen als Array.
     */
    public function getLogLines(): array
    {
        return $this->log ? explode("\n", $this->log) : [];
    }
}
