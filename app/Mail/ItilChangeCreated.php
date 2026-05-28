<?php

namespace App\Mail;

use App\Models\ItilChange;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItilChangeCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly ItilChange $change) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->change->type_label . '] Neuer Change: '
                . $this->change->number . ' – ' . $this->change->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.itil-change-created',
            with: ['change' => $this->change],
        );
    }
}
