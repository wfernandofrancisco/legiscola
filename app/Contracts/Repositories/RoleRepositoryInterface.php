<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Pagina roles com contagem de permissions.
     */
    public function paginateWithPermissions(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    /**
     * Pagina roles com contagem de permissions e ordenacao.
     */
    public function paginateWithPermissionsAndSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator;

    /**
     * Encontra role com suas permissions carregadas.
     */
    public function findWithPermissions(int $id): ?Role;

    /**
     * Busca role por nome.
     */
    public function findByName(string $name): ?Role;

    /**
     * Lista roles do sistema que não devem ser editados.
     */
    public function getSystemRoles(): array;

    /**
     * Verifica se um role é do sistema.
     */
    public function isSystemRole(Role $role): bool;

    /**
     * Sincroniza permissions do role.
     */
    public function syncPermissions(Role $role, array $permissionIds): void;
}
