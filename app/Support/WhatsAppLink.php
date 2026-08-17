<?php

namespace App\Support;

final class WhatsAppLink
{
    private const DEFAULT_COUNTRY_CODE = '55';

    /**
     * Número no formato aceito pelo wa.me (somente dígitos, com DDI).
     */
    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', (string) ($number ?? '')) ?? '';

        if ($digits === '') {
            return null;
        }

        // Números salvos com DDD (10 ou 11 dígitos) recebem o DDI do Brasil.
        if (strlen($digits) === 10 || strlen($digits) === 11) {
            $digits = self::DEFAULT_COUNTRY_CODE.$digits;
        }

        if (strlen($digits) < 12 || strlen($digits) > 15) {
            return null;
        }

        return $digits;
    }

    public static function url(?string $number, ?string $message = null): ?string
    {
        $normalized = self::normalize($number);

        if ($normalized === null) {
            return null;
        }

        $url = 'https://wa.me/'.$normalized;

        if (filled($message)) {
            $url .= '?text='.rawurlencode((string) $message);
        }

        return $url;
    }
}
