<?php

namespace App\Enums;

enum OrcamentoCanceladoPor: string
{
    case Morador = 'morador';
    case Empresa = 'empresa';

    public function label(): string
    {
        return match ($this) {
            self::Morador => 'Cancelado pelo cliente',
            self::Empresa => 'Cancelado pela empresa',
        };
    }
}
