<?php

namespace App\Enums;

enum CatalogVideoProvider: string
{
    case Youtube = 'youtube';
    case Vimeo = 'vimeo';
    case Externo = 'externo';

    /** Arquivo hospedado pelo próprio sistema. */
    case Upload = 'upload';

    public function label(): string
    {
        return match ($this) {
            self::Youtube => 'YouTube',
            self::Vimeo => 'Vimeo',
            self::Externo => 'Link externo',
            self::Upload => 'Arquivo enviado',
        };
    }

    public function isEmbeddable(): bool
    {
        return $this === self::Youtube || $this === self::Vimeo;
    }
}
