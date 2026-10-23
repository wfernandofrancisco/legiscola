<?php

namespace App\Support;

/**
 * Mascaramento parcial para relatórios (ex.: PDF de listagem).
 */
final class SensitiveMask
{
    /**
     * CPF com aproximadamente metade oculta (5 primeiros + ***** + 2 últimos quando há 11 dígitos).
     */
    /**
     * CPF completo com pontos e hífen: 123.123.123-12.
     */
    public static function cpfFormatted(?string $cpf): string
    {
        $digits = preg_replace('/\D/', '', (string) ($cpf ?? '')) ?? '';

        if ($digits === '') {
            return '—';
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 3).'.'.substr($digits, 3, 3).'.'.substr($digits, 6, 3).'-'.substr($digits, 9, 2);
        }

        return $digits;
    }

    public static function cpfHalfMasked(?string $cpf): string
    {
        $digits = preg_replace('/\D/', '', (string) ($cpf ?? '')) ?? '';

        if ($digits === '') {
            return '—';
        }

        if (strlen($digits) === 11) {
            return substr($digits, 0, 5).'*****'.substr($digits, -2);
        }

        $len = strlen($digits);
        $head = (int) max(1, floor($len / 3));

        return substr($digits, 0, $head).str_repeat('*', min(8, $len - $head));
    }
}
