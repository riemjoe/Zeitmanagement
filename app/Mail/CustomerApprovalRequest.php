<?php

namespace App\Mail;

use App\Models\CustomerApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerApprovalRequest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CustomerApproval $approval,
        public readonly string $url,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Freigabe angefragt: ' . $this->approval->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-approval-request',
            with: [
                'approval' => $this->approval,
                'url'      => $this->url,
            ],
        );
    }
}
