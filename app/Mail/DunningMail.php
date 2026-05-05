<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DunningMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param Invoice $invoice  Die betroffene Rechnung
     * @param int     $level    0 = Zahlungserinnerung, 1–3 = Mahnung 1–3
     * @param string  $newDueDate  Das neue Zahlungsziel (formatiert)
     */
    public function __construct(
        public readonly Invoice $invoice,
        public readonly int     $level,
        public readonly string  $newDueDate,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->level) {
            0       => 'Zahlungserinnerung – Rechnung ' . $this->invoice->invoice_number,
            1       => '1. Mahnung – Rechnung ' . $this->invoice->invoice_number,
            2       => '2. Mahnung – Rechnung ' . $this->invoice->invoice_number,
            default => '3. Mahnung – Rechnung ' . $this->invoice->invoice_number,
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.dunning-notice',
            with: [
                'invoice'    => $this->invoice,
                'level'      => $this->level,
                'newDueDate' => $this->newDueDate,
            ],
        );
    }
}
