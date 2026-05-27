<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// Project wird über die Relation aufgelöst (kein direkter Import nötig, da gleicher Namespace)

class Ticket extends Model
{
    protected $fillable = [
        'ticket_number', 'customer_id', 'project_id', 'customer_email',
        'support_category_id', 'title', 'description',
        'source', 'status', 'priority', 'sla_deadline', 'sla_risk_notified_at',
        'waiting_reminder_sent_at', 'closed_at',
    ];

    protected $casts = [
        'sla_deadline'             => 'datetime',
        'sla_risk_notified_at'     => 'datetime',
        'waiting_reminder_sent_at' => 'datetime',
        'closed_at'                => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supportCategory(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at');
    }

    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'Niedrig',
            'medium' => 'Mittel',
            'high'   => 'Hoch',
            'urgent' => 'Dringend',
            default  => ucfirst($this->priority ?? 'medium'),
        };
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'low'    => 'gray',
            'medium' => 'blue',
            'high'   => 'orange',
            'urgent' => 'red',
            default  => 'blue',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open'        => 'Offen',
            'in_progress' => 'In Bearbeitung',
            'waiting'     => 'Wartet auf Kunde',
            'closed'      => 'Geschlossen',
            default       => ucfirst($this->status),
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open'        => 'blue',
            'in_progress' => 'yellow',
            'waiting'     => 'purple',
            'closed'      => 'gray',
            default       => 'gray',
        };
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->sla_deadline
            && $this->sla_deadline->isPast()
            && $this->status !== 'closed';
    }

    /**
     * Wie viel Prozent der SLA-Zeit bereits verstrichen ist (0–100+).
     * Gibt null zurück, wenn kein SLA gesetzt ist.
     */
    public function getSlaPercentElapsedAttribute(): ?int
    {
        if (! $this->sla_deadline) {
            return null;
        }
        $totalSeconds   = $this->created_at->diffInSeconds($this->sla_deadline);
        if ($totalSeconds <= 0) {
            return 100;
        }
        $elapsedSeconds = $this->created_at->diffInSeconds(now());
        return (int) min(round($elapsedSeconds / $totalSeconds * 100), 999);
    }

    /**
     * Scope: Tickets im Status "Wartet auf Kunde", bei denen eine Erinnerungsmail fällig ist.
     * Kriterien: Status = waiting UND (noch nie erinnert ODER letzte Erinnerung > 3 Tage her).
     */
    public function scopeNeedsWaitingReminder(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('status', 'waiting')
            ->where(fn ($q) => $q
                ->whereNull('waiting_reminder_sent_at')
                ->orWhere('waiting_reminder_sent_at', '<=', now()->subDays(3))
            );
    }

    /**
     * Scope: Tickets, die seit mehr als 180 Tagen geschlossen sind → automatisch löschen.
     */
    public function scopePurgeable(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->where('status', 'closed')
            ->where('closed_at', '<=', now()->subDays(180));
    }

    /**
     * Scope: Tickets, bei denen die SLA-Risk-Benachrichtigung gesendet werden soll.
     * Kriterien:
     *  - SLA-Frist gesetzt
     *  - Ticket nicht geschlossen
     *  - Noch keine SLA-Risk-Benachrichtigung gesendet
     *  - 75 % der SLA-Zeit verstrichen (d. h. now() >= created_at + 0.75 * (sla_deadline - created_at))
     *  - Kein Admin hat bisher im Ticket geschrieben
     */
    public function scopeAtSlaRisk(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query
            ->whereNotNull('sla_deadline')
            ->whereNot('status', 'closed')
            ->whereNull('sla_risk_notified_at')
            // 75%-Schwelle: now >= created_at + 0.75 * (sla_deadline - created_at)
            // äquivalent: now >= sla_deadline - 0.25 * (sla_deadline - created_at)
            // In SQL: NOW() >= tickets.created_at + 0.75 * (TIMESTAMPDIFF(SECOND, tickets.created_at, tickets.sla_deadline))
            ->whereRaw('NOW() >= DATE_ADD(created_at, INTERVAL FLOOR(0.75 * TIMESTAMPDIFF(SECOND, created_at, sla_deadline)) SECOND)')
            ->whereDoesntHave('messages', fn ($q) => $q->where('sender_type', 'admin')->where('is_worknote', false));
    }

    /**
     * Generate a unique ticket number in format XXX-XXX-XXX
     */
    public static function generateTicketNumber(): string
    {
        do {
            $part1 = strtoupper(substr(md5(uniqid()), 0, 3));
            $part2 = strtoupper(substr(md5(uniqid()), 0, 3));
            $part3 = strtoupper(substr(md5(uniqid()), 0, 3));
            $number = "{$part1}-{$part2}-{$part3}";
        } while (static::where('ticket_number', $number)->exists());

        return $number;
    }
}
