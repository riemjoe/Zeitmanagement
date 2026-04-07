<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailLog extends Model
{
    protected $fillable = [
        'type',
        'recipient_email',
        'subject',
        'status',
        'error_message',
        'ticket_id',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    /**
     * Einen E-Mail-Versand protokollieren.
     */
    public static function record(
        string  $type,
        string  $recipient,
        string  $subject,
        string  $status = 'sent',
        ?string $error  = null,
        ?int    $ticketId = null,
    ): self {
        return static::create([
            'type'            => $type,
            'recipient_email' => $recipient,
            'subject'         => $subject,
            'status'          => $status,
            'error_message'   => $error,
            'ticket_id'       => $ticketId,
        ]);
    }

    /** Lesbare Typbezeichnung */
    public function typeLabelGerman(): string
    {
        return match ($this->type) {
            'ticket_created_customer' => 'Ticket erstellt (Kunde)',
            'ticket_created_admin'    => 'Ticket erstellt (Admin)',
            'ticket_replied_customer' => 'Antwort (Kunde)',
            'ticket_replied_admin'    => 'Antwort (Admin)',
            'ticket_closed'           => 'Ticket geschlossen',
            'ticket_sla_risk'         => 'SLA-Risiko',
            'ticket_waiting_reminder' => 'Erinnerung',
            'maintenance_reminder'    => 'Wartungserinnerung',
            'test_mail'               => 'Test-Mail',
            default                   => $this->type,
        };
    }
}
