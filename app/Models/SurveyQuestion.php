<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyQuestion extends Model
{
    protected $fillable = [
        'survey_section_id', 'survey_template_id',
        'title', 'description', 'type', 'is_required', 'weight', 'position', 'settings',
    ];

    protected $casts = [
        'settings'    => 'array',
        'is_required' => 'boolean',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(SurveySection::class, 'survey_section_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(SurveyOption::class)->orderBy('position');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /**
     * Berechnet den normierten Score (0–100) für einen gegebenen Rohwert.
     * Gibt null zurück, wenn die Frage nicht bewertet wird (type = text).
     */
    public function calculateScore(mixed $rawValue): ?float
    {
        if ($this->type === 'text') {
            return null;
        }

        if ($this->type === 'select') {
            // Rohwert ist die Option-ID
            $option = $this->options->firstWhere('id', (int) $rawValue);
            return $option ? (float) $option->score : null;
        }

        // range / number: lineare Interpolation zwischen bad_to und good_from
        $settings  = $this->settings ?? [];
        $goodFrom  = (float) ($settings['good_from'] ?? $settings['max'] ?? 100);
        $badTo     = (float) ($settings['bad_to']   ?? $settings['min'] ?? 0);
        $value     = (float) $rawValue;

        if ($goodFrom <= $badTo) {
            // Konfigurationsfehler: alle Punkte neutral
            return 50.0;
        }

        if ($value >= $goodFrom) return 100.0;
        if ($value <= $badTo)   return 0.0;

        return round(($value - $badTo) / ($goodFrom - $badTo) * 100, 2);
    }
}
