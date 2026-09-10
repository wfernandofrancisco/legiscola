<?php

namespace App\Enums;

enum CatalogLicenseModalidade: string
{
    case Presencial = 'presencial';
    case Online = 'online';

    public function label(): string
    {
        return match ($this) {
            self::Presencial => 'Presencial',
            self::Online => 'Online',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $m) => [$m->value => $m->label()])
            ->all();
    }
}
