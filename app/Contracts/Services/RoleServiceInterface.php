<?php

namespace App\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

interface RoleServiceInterface
{
    /**
     * Lista roles paginados com filtro.
     */
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    /**
     * Lista roles paginados com filtro e ordenacao.
     */
    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator;

    /**
     * Obtém detalhes de um role.
     */
    public function getRole(int $id): Role;

    /**
     * Cria novo role e vincula permissions.
     */
    public function createRole(array $data): Role;

    /**
     * Atualiza role.
     */
    public function updateRole(int $id, array $data): Role;

    /**
     * Deleta role (com proteção de roles do sistema).
     */
    public function deleteRole(int $id): bool;

    /**
     * Sincroniza permissions do role.
     */
    public function syncPermissions(int $roleId, array $permissionIds): void;
}
