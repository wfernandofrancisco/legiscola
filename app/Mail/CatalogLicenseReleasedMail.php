<?php

namespace App\Mail;

use App\Models\CatalogLicense;
use App\Support\TenantUrl;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Avisa a câmara que a direção regional liberou um curso ou palestra para ela.
 */
class CatalogLicenseReleasedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CatalogLicense $license,
        public string $recipientName,
    ) {}

    public function envelope(): Envelope
    {
        $this->license->loadMissing('catalogItem');

        return new Envelope(
            subject: 'Novo conteúdo liberado: '.($this->license->catalogItem?->titulo ?? 'conteúdo regional'),
        );
    }

    public function content(): Content
    {
        $this->license->loadMissing(['catalogItem', 'tenant', 'director']);

        return new Content(
            view: 'emails.catalog-license-released',
            with: [
                'license' => $this->license,
                'recipientName' => $this->recipientName,
                'item' => $this->license->catalogItem,
                'painelUrl' => TenantUrl::baseUrlForTenant($this->license->tenant).'/admin/escola/catalogo-regional',
            ],
        );
    }
}
