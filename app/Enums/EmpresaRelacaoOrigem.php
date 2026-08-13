<?php

namespace App\Enums;

enum EmpresaRelacaoOrigem: string
{
    case INTERNO = 'interno';
    case EMPRESA = 'empresa';
    case SECRETARIA = 'secretaria';
    case EXTERNO = 'externo';

    public function label(): string
    {
        return match ($this) {
            self::INTERNO => 'Interno',
            self::EMPRESA => 'Empresa',
            self::SECRETARIA => 'Secretaria',
            self::EXTERNO => 'Externo',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $item) => [$item->value => $item->label()])->all();
    }
}
