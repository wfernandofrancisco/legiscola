<?php

namespace App\Mail;

use App\Models\PortalContactMessage;
use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalContactReplyMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Tenant $tenant,
        public PortalContactMessage $contactMessage,
        public string $replyBody,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = $this->tenant->display_name ?: config('mail.from.name');

        $replyTo = [];
        $institution = trim((string) ($this->tenant->contact_email ?? ''));
        if ($institution !== '' && filter_var($institution, FILTER_VALIDATE_EMAIL)) {
            $replyTo[] = new Address($institution, $this->tenant->display_name);
        }

        return new Envelope(
            from: new Address((string) $fromAddress, (string) $fromName),
            replyTo: $replyTo,
            subject: 'Resposta — '.$this->tenant->display_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.portal-contact-reply',
            with: [
                'tenant' => $this->tenant,
                'contactMessage' => $this->contactMessage,
                'replyBody' => $this->replyBody,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
