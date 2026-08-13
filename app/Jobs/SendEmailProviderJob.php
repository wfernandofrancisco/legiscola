<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

/**
 * Job para envio de e-mail via serviço terceiro (Mailgun, SendGrid, Amazon SES, etc).
 * Use este job para alta volumetria ou quando quiser rastreamento de envios.
 *
 * Configurar no .env (exemplo Mailgun):
 *   MAIL_MAILER=mailgun
 *   MAILGUN_DOMAIN=mg.seudominio.com.br
 *   MAILGUN_SECRET=key-xxxxxxxxxxxxx
 *   MAILGUN_ENDPOINT=api.mailgun.net
 *
 * Alternativa SendGrid:
 *   MAIL_MAILER=sendgrid
 *   SENDGRID_API_KEY=SG.xxxxxxxxxxxxx
 *
 * Alternativa Amazon SES:
 *   MAIL_MAILER=ses
 *   AWS_ACCESS_KEY_ID=...
 *   AWS_SECRET_ACCESS_KEY=...
 *   AWS_DEFAULT_REGION=us-east-1
 */
class SendEmailProviderJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 5;
    public int $backoff = 120;

    public function __construct(
        private readonly Mailable $mailable,
        private readonly string $to,
        private readonly ?string $toName = null,
        private readonly string $mailer = 'mailgun'
    ) {
    }

    public function handle(): void
    {
        Mail::mailer($this->mailer)
            ->to($this->to, $this->toName)
            ->send($this->mailable);
    }

    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error("Falha ao enviar e-mail via {$this->mailer}", [
            'to'        => $this->to,
            'mailable'  => $this->mailable::class,
            'mailer'    => $this->mailer,
            'exception' => $exception->getMessage(),
        ]);
    }
}
