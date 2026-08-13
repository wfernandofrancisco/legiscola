<?php

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model)
    {
        parent::__construct($model);
    }

    /**
     * Pagina roles com contagem de permissions.
     */
    public function paginateWithPermissions(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->withCount('permissions')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Pagina roles com contagem de permissions e ordenação.
     */
    public function paginateWithPermissionsAndSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        return $this->model
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->withCount('permissions')
            ->when($sortBy, fn($q) => $q->orderBy($sortBy, $sortDir), fn($q) => $q->orderBy('name'))
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Encontra role com suas permissions carregadas.
     */
    public function findWithPermissions(int $id): ?Role
    {
        return $this->model->with('permissions')->find($id);
    }

    /**
     * Busca role por nome.
     */
    public function findByName(string $name): ?Role
    {
        return $this->model->where('name', $name)->first();
    }

    /**
     * Lista roles do sistema que não devem ser editados.
     */
    public function getSystemRoles(): array
    {
        return ['super-admin', 'tenant-admin', 'tenant-manager', 'tenant-user'];
    }

    /**
     * Verifica se um role é do sistema.
     */
    public function isSystemRole(Role $role): bool
    {
        return in_array($role->name, $this->getSystemRoles());
    }

    /**
     * Sincroniza permissions do role.
     */
    public function syncPermissions(Role $role, array $permissionIds): void
    {
        // Buscar permissões pelos IDs e obter os nomes
        $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

        $role->syncPermissions($permissions);
    }
}
