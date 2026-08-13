<?php

namespace App\Mail;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PortalContactSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name:string,email:string,phone?:string|null,message:string}  $payload
     */
    public function __construct(
        public Tenant $tenant,
        public array $payload,
    ) {}

    public function envelope(): Envelope
    {
        $reply = [];
        $email = (string) ($this->payload['email'] ?? '');
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $reply[] = new Address($email, (string) ($this->payload['name'] ?? ''));
        }

        return new Envelope(
            replyTo: $reply,
            subject: '[Portal] Contato — '.$this->tenant->display_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.portal-contact',
            with: [
                'tenant' => $this->tenant,
                'payload' => $this->payload,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
