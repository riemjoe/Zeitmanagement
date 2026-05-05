<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id', 'invoice_number', 'date', 'due_date',
        'status', 'tax_rate', 'discount', 'notes', 'service_description', 'sender_snapshot',
        'reminder_sent_at', 'dunning1_sent_at', 'dunning2_sent_at', 'dunning3_sent_at',
        'dunning_due_date',
    ];

    protected $casts = [
        'date'              => 'date',
        'due_date'          => 'date',
        'dunning_due_date'  => 'date',
        'reminder_sent_at'  => 'datetime',
        'dunning1_sent_at'  => 'datetime',
        'dunning2_sent_at'  => 'datetime',
        'dunning3_sent_at'  => 'datetime',
        'tax_rate'          => 'decimal:2',
        'discount'          => 'decimal:2',
        'sender_snapshot'   => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function timeEntries(): BelongsToMany
    {
        // Tabelle explizit angeben – Migration hat 'invoice_time_entry' angelegt
        return $this->belongsToMany(TimeEntry::class, 'invoice_time_entry');
    }

    public function expenses(): BelongsToMany
    {
        // Tabelle explizit angeben – Migration hat 'invoice_expense' angelegt
        return $this->belongsToMany(Expense::class, 'invoice_expense');
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

    // ── Mahnwesen ─────────────────────────────────────────────────────────────

    /**
     * Gibt das aktuell gültige Zahlungsziel zurück:
     * das Mahndatum wenn gesetzt, sonst das ursprüngliche due_date.
     */
    public function getCurrentDueDateAttribute(): \Carbon\Carbon
    {
        return $this->dunning_due_date ?? $this->due_date;
    }

    /**
     * Ist die Rechnung überfällig (verschickt, aber aktuelles Zahlungsziel überschritten)?
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'sent' && $this->current_due_date->isPast();
    }

    /**
     * Wie viele Tage ist die Rechnung überfällig? Null wenn nicht überfällig.
     */
    public function getDaysOverdueAttribute(): int
    {
        if (! $this->is_overdue) return 0;
        return (int) $this->current_due_date->startOfDay()->diffInDays(now()->startOfDay());
    }

    /**
     * Nächster offener Mahnschritt (0 = Zahlungserinnerung, 1–3 = Mahnung 1–3, 4 = alle erledigt).
     */
    public function getNextDunningLevelAttribute(): int
    {
        if (! $this->reminder_sent_at) return 0;
        if (! $this->dunning1_sent_at) return 1;
        if (! $this->dunning2_sent_at) return 2;
        if (! $this->dunning3_sent_at) return 3;
        return 4; // alle Mahnstufen ausgeschöpft
    }

    /**
     * Scope: Rechnungen, die für das Mahnwesen relevant sind
     * (status = sent, aktuelles Zahlungsziel überschritten, nicht komplett abgemahnt).
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'sent')
            ->where(function ($q) {
                // Original-Zahlungsziel überschritten (kein Mahndatum gesetzt)
                $q->whereNull('dunning_due_date')
                  ->whereDate('due_date', '<', now()->toDateString());
            })
            ->orWhere(function ($q) {
                // Mahndatum gesetzt und dieses ist ebenfalls überschritten
                $q->where('status', 'sent')
                  ->whereNotNull('dunning_due_date')
                  ->whereDate('dunning_due_date', '<', now()->toDateString());
            });
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
