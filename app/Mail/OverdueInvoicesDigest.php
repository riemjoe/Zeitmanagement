<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OverdueInvoicesDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Collection $invoices,
    ) {}

    public function envelope(): Envelope
    {
        $count = $this->invoices->count();
        return new Envelope(
            subject: "⚠️ {$count} überfällige Rechnung" . ($count !== 1 ? 'en' : '') . ' – Mahnwesen'
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.overdue-invoices-digest',
            with: ['invoices' => $this->invoices],
        );
    }
}
