<?php

namespace App\Enums;

enum CatalogItemTipo: string
{
    case Curso = 'curso';
    case Palestra = 'palestra';

    public function label(): string
    {
        return match ($this) {
            self::Curso => 'Curso',
            self::Palestra => 'Palestra',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Curso => 'blue',
            self::Palestra => 'cyan',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $tipo) => [$tipo->value => $tipo->label()])
            ->all();
    }
}
