<?php

namespace App\Enums;

enum EmpresaRelacaoPrioridade: string
{
    case BAIXA = 'baixa';
    case MEDIA = 'media';
    case ALTA = 'alta';
    case URGENTE = 'urgente';

    public function label(): string
    {
        return match ($this) {
            self::BAIXA => 'Baixa',
            self::MEDIA => 'Média',
            self::ALTA => 'Alta',
            self::URGENTE => 'Urgente',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::BAIXA => 'gray',
            self::MEDIA => 'blue',
            self::ALTA => 'yellow',
            self::URGENTE => 'red',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn (self $item) => [$item->value => $item->label()])->all();
    }
}
