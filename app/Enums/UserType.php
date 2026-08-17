<?php

namespace App\Enums;

enum UserType: string
{
    case TENANT_ADMIN = 'tenant_admin';
    case TENANT_MANAGER = 'tenant_manager';
    case TENANT_RESPONSIBLE = 'tenant_responsible';
    case TENANT_USER = 'tenant_user';

    public function label(): string
    {
        return match ($this) {
            self::TENANT_ADMIN => 'Administrador',
            self::TENANT_MANAGER => 'Gerente',
            self::TENANT_RESPONSIBLE => 'Docente',
            self::TENANT_USER => 'Aluno',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TENANT_ADMIN => 'blue',
            self::TENANT_MANAGER => 'yellow',
            self::TENANT_RESPONSIBLE => 'teal',
            self::TENANT_USER => 'purple',
        };
    }

    /**
     * Role Spatie correspondente ao tipo (docente usa tenant_professor).
     */
    public function roleName(): string
    {
        return match ($this) {
            self::TENANT_RESPONSIBLE => 'tenant_professor',
            default => $this->value,
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->label()])
            ->toArray();
    }

    public static function colors(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [$type->value => $type->color()])
            ->toArray();
    }
}
