<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * Horários de aula (TIME) para input HTML e comparação, sem misturar HH:MM com HH:MM:SS.
 */
final class ClockTime
{
    public static function toHi(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_object($value) && method_exists($value, 'format')) {
            try {
                return $value->format('H:i');
            } catch (\Throwable) {
                // continua no parse de string
            }
        }

        $raw = trim((string) $value);
        if (preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?/', $raw, $m) === 1) {
            return sprintf('%02d:%02d', (int) $m[1], (int) $m[2]);
        }

        try {
            return Carbon::parse($raw)->format('H:i');
        } catch (\Throwable) {
            return null;
        }
    }

    public static function toHis(mixed $value): ?string
    {
        $hi = self::toHi($value);

        return $hi === null ? null : $hi.':00';
    }

    public static function minutes(mixed $value): ?int
    {
        $hi = self::toHi($value);
        if ($hi === null) {
            return null;
        }

        [$h, $m] = array_map('intval', explode(':', $hi));

        return ($h * 60) + $m;
    }
}
