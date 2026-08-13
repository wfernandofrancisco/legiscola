<?php

namespace App\Services;

use App\Contracts\TransactionalSmsSenderInterface;
use Illuminate\Support\Facades\Log;

/**
 * SMS transacional simulado: grava no log (ambiente local / testes ou quando não há gateway).
 */
class LogTransactionalSmsSender implements TransactionalSmsSenderInterface
{
    public function send(string $digits, string $message): void
    {
        $masked = $this->maskDigits($digits);

        Log::info('[SMS/Simulado] Mensagem transacional da turma', [
            'to_masked' => $masked,
            'length' => mb_strlen($message),
            'preview' => mb_substr($message, 0, 120),
        ]);
    }

    private function maskDigits(string $digits): string
    {
        $digits = preg_replace('/\D/', '', $digits) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) <= 4) {
            return str_repeat('*', strlen($digits));
        }

        return str_repeat('*', strlen($digits) - 4).substr($digits, -4);
    }
}
