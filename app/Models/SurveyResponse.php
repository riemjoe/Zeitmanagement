<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyResponse extends Model
{
    protected $fillable = [
        'survey_id', 'respondent_name', 'respondent_email',
        'total_score', 'verdict', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'total_score'  => 'float',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class);
    }

    /** Berechnet und speichert Gesamtscore + Verdict anhand der Antworten. */
    public function computeAndSaveScore(): void
    {
        $template = $this->survey->template;

        // Nur bewertete Antworten einbeziehen (score != null)
        $scored = $this->answers->filter(fn ($a) => $a->score !== null);

        if ($scored->isEmpty()) {
            $this->update(['total_score' => null, 'verdict' => null]);
            return;
        }

        // Gewichteter Durchschnitt
        $totalWeight  = 0;
        $weightedSum  = 0.0;

        foreach ($scored as $answer) {
            $question     = $answer->question;
            $w            = (int) ($question->weight ?? 1);
            $totalWeight += $w;
            $weightedSum += $answer->score * $w;
        }

        $score = $totalWeight > 0 ? round($weightedSum / $totalWeight, 2) : null;

        $verdict = null;
        if ($score !== null) {
            if ($score >= $template->good_threshold) {
                $verdict = 'good';
            } elseif ($score <= $template->bad_threshold) {
                $verdict = 'bad';
            } else {
                $verdict = 'neutral';
            }
        }

        $this->update(['total_score' => $score, 'verdict' => $verdict]);
    }

    /** Farbklasse passend zum Verdict. */
    public function getVerdictColorAttribute(): string
    {
        return match ($this->verdict) {
            'good'    => 'green',
            'bad'     => 'red',
            'neutral' => 'yellow',
            default   => 'gray',
        };
    }

    /** Lesbares Label für das Verdict. */
    public function getVerdictLabelAttribute(): string
    {
        return match ($this->verdict) {
            'good'    => 'Gut',
            'bad'     => 'Schlecht',
            'neutral' => 'Neutral',
            default   => '–',
        };
    }
}
