<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Survey extends Model
{
    protected $fillable = [
        'survey_template_id', 'customer_id', 'title',
        'token', 'max_responses', 'expires_at', 'is_active',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'is_active'    => 'boolean',
        'max_responses' => 'integer',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(SurveyTemplate::class, 'survey_template_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /** Prüft ob die Umfrage noch beantwortet werden kann. */
    public function isAcceptingResponses(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_responses !== null && $this->responses()->count() >= $this->max_responses) return false;
        return true;
    }

    /** Öffentliche URL der Umfrage. */
    public function getPublicUrlAttribute(): string
    {
        return route('survey.show', $this->token);
    }

    /** Generiert einen neuen eindeutigen Token. */
    public static function generateToken(): string
    {
        do {
            $token = Str::random(32);
        } while (static::where('token', $token)->exists());
        return $token;
    }

    // ── Auswertungs-Aggregate ────────────────────────────────────────────────

    public function getAvgScoreAttribute(): ?float
    {
        $scores = $this->responses->whereNotNull('total_score')->pluck('total_score');
        return $scores->isEmpty() ? null : round($scores->avg(), 1);
    }

    public function getVerdictCountsAttribute(): array
    {
        return [
            'good'    => $this->responses->where('verdict', 'good')->count(),
            'neutral' => $this->responses->where('verdict', 'neutral')->count(),
            'bad'     => $this->responses->where('verdict', 'bad')->count(),
        ];
    }
}
