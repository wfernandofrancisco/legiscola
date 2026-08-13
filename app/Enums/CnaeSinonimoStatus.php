<?php

namespace App\Enums;

enum CnaeSinonimoStatus: int
{
    case AGUARDANDO = 1;
    case APROVADO = 2;
    case REJEITADO = 3;

    public function label(): string
    {
        return match ($this) {
            self::AGUARDANDO => 'Aguardando Análise',
            self::APROVADO => 'Aprovado',
            self::REJEITADO => 'Rejeitado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::AGUARDANDO => 'yellow',
            self::APROVADO => 'green',
            self::REJEITADO => 'red',
        };
    }

    public function bgColor(): string
    {
        return match ($this) {
            self::AGUARDANDO => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
            self::APROVADO => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            self::REJEITADO => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        };
    }
}
