<?php

namespace App\Mail;

use App\Models\Problem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ItilProblemCreated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Problem $problem) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[' . $this->problem->priority_label . '] Neues Problem: '
                . $this->problem->number . ' – ' . $this->problem->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.itil-problem-created',
            with: ['problem' => $this->problem],
        );
    }
}
