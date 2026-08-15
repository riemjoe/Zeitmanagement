<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerApproval extends Model
{
    protected $fillable = [
        'customer_id', 'project_id', 'requested_by',
        'title', 'description', 'token', 'status',
        'expires_at', 'responded_at', 'response_comment', 'responder_ip',
    ];

    protected $casts = [
        'expires_at'   => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Ist der Freigabe-Link abgelaufen? Nur relevant, solange status = pending.
     */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /**
     * Kann der Kunde aktuell noch reagieren (offen und nicht abgelaufen)?
     */
    public function isOpen(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }

    /** Lesbarer Status inkl. Ablauf-Erkennung. */
    public function statusLabelGerman(): string
    {
        if ($this->status === 'pending' && $this->isExpired()) {
            return 'Abgelaufen';
        }

        return match ($this->status) {
            'pending'  => 'Ausstehend',
            'approved' => 'Erlaubt',
            'rejected' => 'Abgelehnt',
            default    => ucfirst($this->status),
        };
    }

    /** Tailwind-Farbklassen für den Status-Badge. */
    public function statusColorClasses(): string
    {
        if ($this->status === 'pending' && $this->isExpired()) {
            return 'bg-gray-100 text-gray-500';
        }

        return match ($this->status) {
            'pending'  => 'bg-amber-100 text-amber-700',
            'approved' => 'bg-green-100 text-green-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
