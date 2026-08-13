<?php

namespace App\Enums;

enum CertificateTipoEmissao: string
{
    case Curso = 'curso';
    case Evento = 'evento';

    public function label(): string
    {
        return match ($this) {
            self::Curso => 'Curso (turmas)',
            self::Evento => 'Evento',
        };
    }
}
