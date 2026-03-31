<?php

namespace App\Mail;

use App\Models\MaintenanceEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MaintenanceReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly MaintenanceEvent $event,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔧 Wartung fällig: ' . $this->event->title
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.maintenance-reminder',
            with: ['event' => $this->event],
        );
    }
}
