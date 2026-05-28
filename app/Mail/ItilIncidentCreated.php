<?php

namespace App\Mail;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItilIncidentCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Incident $incident) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->incident->priority_label . '] Neuer Incident: '
                . $this->incident->number . ' – ' . $this->incident->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.itil-incident-created',
            with: ['incident' => $this->incident],
        );
    }
}
