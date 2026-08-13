<?php

namespace App\Enums;

enum UserStatus: string
{
    case ATIVO = 'ativo';
    case INATIVO = 'inativo';
    case PENDENTE = 'pendente';

    public function label(): string
    {
        return match ($this) {
            self::ATIVO => 'Ativo',
            self::INATIVO => 'Inativo',
            self::PENDENTE => 'Pendente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ATIVO => 'green',
            self::INATIVO => 'red',
            self::PENDENTE => 'yellow',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->toArray();
    }

    public static function colors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->color()])
            ->toArray();
    }
}