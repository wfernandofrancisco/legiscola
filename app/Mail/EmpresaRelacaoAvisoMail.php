<?php

namespace App\Mail;

use App\Models\EmpresaRelacao;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class EmpresaRelacaoAvisoMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public EmpresaRelacao $relacao)
    {
    }

    public function build(): self
    {
        return $this->subject('Aviso de relação de empresa')
            ->view('emails.empresa-relacao-aviso');
    }
}
