<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Janela de disponibilidade do quiz na turma (pivot course_class_quiz).
 *
 * Regra simples:
 * - is_active no quiz e na pivot + matrícula válida continuam obrigatórios (validados no controller).
 * - opens_at e closes_at nulos em ambos: aluno pode responder a qualquer momento.
 * - Só opens_at: disponível a partir dessa data/hora.
 * - Só closes_at: disponível até essa data/hora (inclusive).
 * - Os dois: disponível somente com opens_at <= agora <= closes_at.
 */
final class CourseClassQuizAvailability
{
    public static function isOpenNow(mixed $opensAt, mixed $closesAt, ?CarbonInterface $now = null): bool
    {
        $now = $now ? Carbon::instance($now) : Carbon::now();

        if ($opensAt !== null && $opensAt !== '') {
            if ($now->lt(Carbon::parse($opensAt))) {
                return false;
            }
        }

        if ($closesAt !== null && $closesAt !== '') {
            if ($now->gt(Carbon::parse($closesAt))) {
                return false;
            }
        }

        return true;
    }

    public static function statusLabel(mixed $opensAt, mixed $closesAt, ?CarbonInterface $now = null): string
    {
        $now = $now ? Carbon::instance($now) : Carbon::now();

        if (self::isOpenNow($opensAt, $closesAt, $now)) {
            if (($opensAt === null || $opensAt === '') && ($closesAt === null || $closesAt === '')) {
                return 'Sempre aberto';
            }

            return 'Disponível agora';
        }

        if ($opensAt !== null && $opensAt !== '' && $now->lt(Carbon::parse($opensAt))) {
            return 'Abre em '.Carbon::parse($opensAt)->format('d/m/Y H:i');
        }

        return 'Prazo encerrado';
    }
}
