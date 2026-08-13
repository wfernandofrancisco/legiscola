<?php

namespace App\Enums;

enum Escolaridade: string
{
    case SABE_LER_ESCREVE = 'sabe_ler_escreve';
    case ENSINO_FUNDAMENTAL_INCOMPLETO = 'ensino_fundamental_incompleto';
    case ENSINO_FUNDAMENTAL_COMPLETO = 'ensino_fundamental_completo';
    case ENSINO_MEDIO_INCOMPLETO = 'ensino_medio_incompleto';
    case ENSINO_MEDIO_COMPLETO = 'ensino_medio_completo';
    case SUPERIOR_INCOMPLETO = 'superior_incompleto';
    case SUPERIOR_COMPLETO = 'superior_completo';
    case POS_GRADUACAO = 'pos_graduacao';

    public function label(): string
    {
        return match ($this) {
            self::SABE_LER_ESCREVE => 'Sabe ler e escrever',
            self::ENSINO_FUNDAMENTAL_INCOMPLETO => 'Ensino fundamental incompleto',
            self::ENSINO_FUNDAMENTAL_COMPLETO => 'Ensino fundamental completo',
            self::ENSINO_MEDIO_INCOMPLETO => 'Ensino médio incompleto',
            self::ENSINO_MEDIO_COMPLETO => 'Ensino médio completo',
            self::SUPERIOR_INCOMPLETO => 'Superior incompleto',
            self::SUPERIOR_COMPLETO => 'Superior completo',
            self::POS_GRADUACAO => 'Pós-graduação',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $item) => [$item->value => $item->label()])
            ->toArray();
    }
}
