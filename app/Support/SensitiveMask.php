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
