<?php

namespace App\Mail;

use App\Models\CustomerApproval;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerApprovalDecided extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly CustomerApproval $approval,
    ) {}

    public function envelope(): Envelope
    {
        $verb = $this->approval->status === 'approved' ? 'Freigabe erteilt' : 'Freigabe abgelehnt';
        $icon = $this->approval->status === 'approved' ? '✅' : '❌';

        return new Envelope(
            subject: "{$icon} {$verb}: {$this->approval->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.customer-approval-decided',
            with: ['approval' => $this->approval],
        );
    }
}
