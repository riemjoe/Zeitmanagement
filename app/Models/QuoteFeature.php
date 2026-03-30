<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuoteFeature extends Model
{
    protected $fillable = [
        'quote_id', 'name', 'description',
        'lines_of_code', 'hours_override', 'sort_order',
    ];

    protected $casts = [
        'lines_of_code'  => 'integer',
        'hours_override' => 'decimal:2',
        'sort_order'     => 'integer',
    ];

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * Effektive Stunden: manuelle Überschreibung ODER LoC ÷ lines_per_hour.
     */
    public function getEffectiveHoursAttribute(): float
    {
        if ($this->hours_override !== null && (float) $this->hours_override > 0) {
            return (float) $this->hours_override;
        }
        $loc = (int) ($this->lines_of_code ?? 0);
        $lph = max(1, (int) ($this->quote->lines_per_hour ?? 50));
        return round($loc / $lph, 2);
    }
}
