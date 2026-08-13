<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Pagina permissions com busca.
     */
    public function paginateWithSearch(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    /**
     * Pagina permissions com busca e ordenacao.
     */
    public function paginateWithSearchAndSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator;

    /**
     * Busca permission por nome.
     */
    public function findByName(string $name): ?Permission;

    /**
     * Lista permissions do sistema que não devem ser alteradas.
     */
    public function getSystemPermissions(): array;

    /**
     * Verifica se uma permission é do sistema.
     */
    public function isSystemPermission(Permission $permission): bool;
}
