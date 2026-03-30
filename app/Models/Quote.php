<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quote extends Model
{
    protected $fillable = [
        'customer_id', 'quote_number', 'title', 'date', 'valid_until',
        'status', 'hourly_rate', 'lines_per_hour', 'tax_rate', 'discount',
        'buffer_percent', 'notes', 'sender_snapshot',
    ];

    protected $casts = [
        'date'            => 'date',
        'valid_until'     => 'date',
        'tax_rate'        => 'decimal:2',
        'discount'        => 'decimal:2',
        'hourly_rate'     => 'decimal:2',
        'buffer_percent'  => 'decimal:2',
        'sender_snapshot' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function features(): HasMany
    {
        return $this->hasMany(QuoteFeature::class)->orderBy('sort_order');
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    // ── Berechnungen ─────────────────────────────────────────────────────────

    public function getEffectiveHourlyRateAttribute(): float
    {
        return (float) ($this->hourly_rate ?? Setting::get('hourly_rate', 80));
    }

    /** Rohstunden aller Features (ohne Puffer). */
    public function getRawHoursAttribute(): float
    {
        return $this->features->sum(fn ($f) => $f->effective_hours);
    }

    /** Gesamtstunden inkl. Puffer. */
    public function getTotalHoursAttribute(): float
    {
        $buffer = (float) ($this->buffer_percent ?? 0);
        return round($this->raw_hours * (1 + $buffer / 100), 2);
    }

    /** Netto-Gesamtbetrag vor Rabatt. */
    public function getSubtotalAttribute(): float
    {
        return round($this->total_hours * $this->effective_hourly_rate, 2);
    }

    /** Netto nach Rabatt. */
    public function getNetTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) $this->discount);
    }

    /** Steuerbetrag. */
    public function getTaxAmountAttribute(): float
    {
        return round($this->net_total * ((float) $this->tax_rate / 100), 2);
    }

    /** Brutto gesamt. */
    public function getGrossTotalAttribute(): float
    {
        return $this->net_total + $this->tax_amount;
    }

    /** Angebotsnummer generieren: A-Mär26-01 */
    public static function generateNumber(): string
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mär', 4  => 'Apr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8  => 'Aug',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dez',
        ];
        $now    = now();
        $prefix = 'A-' . $monthNames[$now->month] . $now->format('y');
        $count  = static::where('quote_number', 'like', $prefix . '-%')->count();
        return $prefix . '-' . str_pad($count + 1, 2, '0', STR_PAD_LEFT);
    }
}
