<?php

namespace App\Mail;

use App\Models\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerPortalInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Customer $customer,
        public readonly string $invitationUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Einladung zum Kundenportal',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.portal-invitation',
            with: [
                'customer'      => $this->customer,
                'invitationUrl' => $this->invitationUrl,
            ],
        );
    }
}
