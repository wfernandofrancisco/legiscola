<?php

namespace App\Contracts\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

interface PermissionServiceInterface
{
    /**
     * Lista permissions paginadas com filtro.
     */
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator;

    /**
     * Lista permissions paginadas com filtro e ordenacao.
     */
    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator;

    /**
     * Obtém detalhes de uma permission.
     */
    public function getPermission(int $id): Permission;

    /**
     * Cria nova permission.
     */
    public function createPermission(array $data): Permission;

    /**
     * Atualiza permission (com proteção de permissions do sistema).
     */
    public function updatePermission(int $id, array $data): Permission;

    /**
     * Deleta permission (com proteção de permissions do sistema).
     */
    public function deletePermission(int $id): bool;
}
