<?php

namespace App\Enums;

enum CatalogItemStatus: string
{
    case Rascunho = 'rascunho';
    case Publicado = 'publicado';
    case Arquivado = 'arquivado';

    public function label(): string
    {
        return match ($this) {
            self::Rascunho => 'Rascunho',
            self::Publicado => 'Publicado',
            self::Arquivado => 'Arquivado',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Rascunho => 'yellow',
            self::Publicado => 'green',
            self::Arquivado => 'gray',
        };
    }

    /**
     * Só item publicado pode ser licenciado para um cliente.
     */
    public function isLicensable(): bool
    {
        return $this === self::Publicado;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $status) => [$status->value => $status->label()])
            ->all();
    }
}
