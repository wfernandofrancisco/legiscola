<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Abrangência do diretor regional na request atual.
 *
 * A área /diretor roda no domínio raiz e sem o middleware `tenant`, então o TenantScope
 * não filtra nada: uma query solta enxergaria todos os clientes do sistema. Todo acesso a
 * dado de cliente precisa ser restringido pelos ids resolvidos aqui.
 *
 * Falha fechada: sem diretor autenticado ou sem UF atribuída, a lista de tenants é vazia.
 */
class DirectorContext
{
    private static ?int $userId = null;

    /** @var list<string>|null */
    private static ?array $ufs = null;

    /** @var list<int>|null */
    private static ?array $tenantIds = null;

    public static function director(): ?User
    {
        $user = Auth::user();

        return $user instanceof User && $user->isTenantDirector() ? $user : null;
    }

    /**
     * UFs sob responsabilidade do diretor autenticado.
     *
     * @return list<string>
     */
    public static function ufs(): array
    {
        $director = self::director();

        if (! $director) {
            return [];
        }

        self::prime($director);

        return self::$ufs ?? [];
    }

    /**
     * Ids dos tenants que o diretor autenticado pode enxergar.
     *
     * @return list<int>
     */
    public static function tenantIds(): array
    {
        $director = self::director();

        if (! $director) {
            return [];
        }

        self::prime($director);

        return self::$tenantIds ?? [];
    }

    /**
     * Query de tenants já restrita à abrangência.
     */
    public static function tenants(): Builder
    {
        return Tenant::query()->whereIn('id', self::tenantIds());
    }

    public static function allows(?int $tenantId): bool
    {
        return $tenantId !== null && in_array($tenantId, self::tenantIds(), true);
    }

    public static function hasScope(): bool
    {
        return self::ufs() !== [];
    }

    public static function forget(): void
    {
        self::$userId = null;
        self::$ufs = null;
        self::$tenantIds = null;
    }

    private static function prime(User $director): void
    {
        if (self::$userId === (int) $director->id && self::$tenantIds !== null) {
            return;
        }

        self::$userId = (int) $director->id;
        self::$ufs = $director->directorUfCodes();

        self::$tenantIds = self::$ufs === []
            ? []
            : Tenant::query()
                ->whereIn('estado', self::$ufs)
                ->pluck('id')
                ->map(static fn ($id): int => (int) $id)
                ->all();
    }
}
