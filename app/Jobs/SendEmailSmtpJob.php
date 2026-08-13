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
 * Job para envio de e-mail via SMTP configurado no .env (driver smtp/tls).
 * Use este job quando quiser usar o e-mail do próprio servidor/empresa.
 *
 * Configurar no .env:
 *   MAIL_MAILER=smtp
 *   MAIL_HOST=smtp.seuprovedor.com.br
 *   MAIL_PORT=587
 *   MAIL_USERNAME=seu@email.com.br
 *   MAIL_PASSWORD=suasenha
 *   MAIL_ENCRYPTION=tls
 *   MAIL_FROM_ADDRESS=noreply@seudominio.com.br
 */
class SendEmailSmtpJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly Mailable $mailable,
        private readonly string $to,
        private readonly ?string $toName = null
    ) {
    }

    public function handle(): void
    {
        Mail::mailer('smtp')
            ->to($this->to, $this->toName)
            ->send($this->mailable);
    }

    public function failed(\Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('Falha ao enviar e-mail via SMTP', [
            'to'        => $this->to,
            'mailable'  => $this->mailable::class,
            'exception' => $exception->getMessage(),
        ]);
    }
}
