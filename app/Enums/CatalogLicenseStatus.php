<?php

namespace App\Enums;

enum CatalogLicenseStatus: string
{
    case Rascunho = 'rascunho';
    case Ativa = 'ativa';
    case Suspensa = 'suspensa';
    case Expirada = 'expirada';
    case Cancelada = 'cancelada';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Ativa => 'Ativa',
            self::Suspensa => 'Suspensa',
            self::Expirada => 'Expirada',
            self::Cancelada => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Ativa => 'green',
            self::Rascunho => 'yellow',
            self::Suspensa => 'red',
            self::Expirada, self::Cancelada => 'gray',
        };
    }

    /**
     * Somente licença ativa aparece para o cliente e permite abrir turma.
     */
    public function isUsable(): bool
    {
        return $this === self::Ativa;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $s) => [$s->value => $s->label()])
            ->all();
    }
}
