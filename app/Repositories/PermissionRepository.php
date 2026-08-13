<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{
    public function __construct(Permission $model)
    {
        parent::__construct($model);
    }

    /**
     * Pagina permissions com busca.
     */
    public function paginateWithSearch(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Pagina permissions com busca e ordenação.
     */
    public function paginateWithSearchAndSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->when($sortBy, fn($q) => $q->orderBy($sortBy, $sortDir), fn($q) => $q->orderBy('name'))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Busca permission por nome.
     */
    public function findByName(string $name): ?Permission
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Lista permissions do sistema que não devem ser alteradas.
     */
    public function getSystemPermissions(): array
    {
        return [
            'view-users',
            'create-users',
            'edit-users',
            'delete-users',
            'view-companies',
            'create-companies',
            'edit-companies',
            'delete-companies',
            'view-budgets',
            'create-budgets',
            'edit-budgets',
            'delete-budgets',
            'view-reports',
            'export-reports',
            'view-logs',
            'view-settings',
            'edit-settings',
            'view-activity',
        ];
    }

    /**
     * Verifica se uma permission é do sistema.
     */
    public function isSystemPermission(Permission $permission): bool
    {
        return in_array($permission->name, $this->getSystemPermissions());
    }
}
