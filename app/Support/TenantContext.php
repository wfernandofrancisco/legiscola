<?php

namespace App\Support;

use App\Models\User;

/**
 * Contexto do tenant na requisição (usuário do painel do cliente / API).
 * getTenantId() alimenta o escopo global BelongsToTenant.
 */
class TenantContext
{
    private static ?int $tenantId = null;

    public static function set(?int $tenantId): void
    {
        self::$tenantId = $tenantId;
    }

    public static function syncFromUser(User $user): void
    {
        if ($user->isSuperAdmin()) {
            self::clear();

            return;
        }

        self::$tenantId = $user->tenant_id;
    }

    /**
     * @deprecated Use getTenantId()
     */
    public static function get(): ?int
    {
        return self::$tenantId;
    }

    public static function getTenantId(): ?int
    {
        return self::$tenantId;
    }

    public static function clear(): void
    {
        self::$tenantId = null;
    }

    public static function isSet(): bool
    {
        return self::$tenantId !== null;
    }
}
