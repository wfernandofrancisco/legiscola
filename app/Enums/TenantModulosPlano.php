<?php

namespace App\Enums;

enum TenantModulosPlano: string
{
    case ADMIN = 'admin';
    case ADMIN_WEB = 'admin_web';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Somente módulo admin',
            self::ADMIN_WEB => 'Módulo admin e web (portal)',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $out = [];
        foreach (self::cases() as $case) {
            $out[$case->value] = $case->label();
        }

        return $out;
    }
}
