<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id', 'invoice_number', 'date', 'due_date',
        'status', 'tax_rate', 'discount', 'notes', 'sender_snapshot',
    ];

    protected $casts = [
        'date'            => 'date',
        'due_date'        => 'date',
        'tax_rate'        => 'decimal:2',
        'discount'        => 'decimal:2',
        'sender_snapshot' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function timeEntries(): BelongsToMany
    {
        return $this->belongsToMany(TimeEntry::class);
    }

    public function expenses(): BelongsToMany
    {
        return $this->belongsToMany(Expense::class);
    }

    // ── Berechnungen ─────────────────────────────────────────────────────────

    /**
     * Netto-Betrag Arbeitszeiten (nach Kategorie gruppiert bereits verrechnet).
     */
    public function getTimeEntriesNetAttribute(): float
    {
        return $this->timeEntries->sum(fn ($e) => $e->amount);
    }

    /**
     * Netto-Betrag Ausgaben.
     */
    public function getExpensesNetAttribute(): float
    {
        return (float) $this->expenses->sum('amount');
    }

    /**
     * Netto gesamt (vor Rabatt und Steuer).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->time_entries_net + $this->expenses_net;
    }

    /**
     * Betrag nach Rabatt.
     */
    public function getNetTotalAttribute(): float
    {
        return max(0, $this->subtotal - (float) $this->discount);
    }

    /**
     * Steuerbetrag.
     */
    public function getTaxAmountAttribute(): float
    {
        return $this->net_total * ((float) $this->tax_rate / 100);
    }

    /**
     * Brutto gesamt.
     */
    public function getGrossTotalAttribute(): float
    {
        return $this->net_total + $this->tax_amount;
    }

    /**
     * Arbeitszeiten pro Kategorie aggregiert (für Rechnungsausgabe).
     * Gibt Collection zurück: [category_name => [hours, amount, rate, color]]
     */
    public function getGroupedTimeEntriesAttribute()
    {
        return $this->timeEntries
            ->load('workCategory', 'project')
            ->groupBy('work_category_id')
            ->map(function ($entries) {
                $first = $entries->first();
                return [
                    'category' => $first->workCategory->name,
                    'color'    => $first->workCategory->color,
                    'hours'    => $entries->sum(fn ($e) => (float) $e->hours),
                    'amount'   => $entries->sum(fn ($e) => $e->amount),
                ];
            })
            ->values();
    }

    /**
     * Nächste Rechnungsnummer im Format R-Mär26-01 generieren.
     * Zähler wird pro Monat/Jahr separat geführt (anhand bestehender Rechnungen).
     */
    public static function generateNumber(): string
    {
        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mär', 4  => 'Apr',
            5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8  => 'Aug',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dez',
        ];

        $now       = now();
        $monthAbbr = $monthNames[$now->month];
        $year2     = $now->format('y');          // z.B. "26"
        $prefix    = 'R-' . $monthAbbr . $year2; // z.B. "R-Mär26"

        // Anzahl bestehender Rechnungen dieses Monats/Jahres zählen
        $count = static::where('invoice_number', 'like', $prefix . '-%')->count();
        $next  = str_pad($count + 1, 2, '0', STR_PAD_LEFT); // 01, 02, …

        return $prefix . '-' . $next;
    }
}
