<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Leistungsbericht extends Model
{
    protected $table = 'leistungsberichte';

    protected $fillable = [
        'customer_id',
        'invoice_id',
        'date_from',
        'date_to',
        'description',
        'sender_snapshot',
    ];

    protected $casts = [
        'date_from'       => 'date',
        'date_to'         => 'date',
        'sender_snapshot' => 'array',
    ];

    // ── Relationen ───────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // ── ITIL-Daten für den Berichtszeitraum ─────────────────────────────────

    /**
     * Incidents dieses Kunden im Berichtszeitraum.
     */
    public function getIncidentsAttribute()
    {
        return Incident::where('customer_id', $this->customer_id)
            ->where(function ($q) {
                $q->whereBetween('created_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()])
                  ->orWhereBetween('resolved_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()]);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Problems dieses Kunden im Berichtszeitraum.
     */
    public function getProblemsAttribute()
    {
        return Problem::where('customer_id', $this->customer_id)
            ->where(function ($q) {
                $q->whereBetween('created_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()])
                  ->orWhereBetween('resolved_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()]);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Changes dieses Kunden im Berichtszeitraum.
     */
    public function getChangesAttribute()
    {
        return ItilChange::where('customer_id', $this->customer_id)
            ->where(function ($q) {
                $q->whereBetween('created_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()])
                  ->orWhereBetween('planned_start_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()])
                  ->orWhereBetween('planned_end_at', [$this->date_from->startOfDay(), $this->date_to->copy()->endOfDay()]);
            })
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Zeiteinträge dieses Kunden im Berichtszeitraum (wenn kein Invoice verknüpft).
     */
    public function getTimeEntriesInPeriodAttribute()
    {
        if ($this->invoice_id) {
            return $this->invoice->timeEntries()->with(['workCategory', 'project'])->get();
        }

        return \App\Models\TimeEntry::with(['workCategory', 'project'])
            ->whereHas('project', fn ($q) => $q->where('customer_id', $this->customer_id))
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->orderBy('date')
            ->get();
    }
}
