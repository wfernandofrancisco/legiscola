<?php

namespace App\Support;

final class WhatsAppDeepLink
{
    /**
     * Normaliza para dígitos E.164 simples (Brasil: adiciona 55 se vier 10/11 dígitos locais).
     */
    public static function normalizeBrazil(?string $raw): ?string
    {
        $d = preg_replace('/\D+/', '', (string) $raw);
        if ($d === '') {
            return null;
        }

        if (str_starts_with($d, '55') && strlen($d) >= 12) {
            return $d;
        }

        if (strlen($d) >= 10 && strlen($d) <= 11) {
            return '55'.$d;
        }

        return $d;
    }

    public static function url(?string $phoneDigits, string $message): ?string
    {
        $digits = self::normalizeBrazil($phoneDigits);
        if ($digits === null) {
            return null;
        }

        return 'https://wa.me/'.$digits.'?text='.rawurlencode($message);
    }
}
