<?php

namespace App\Services;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Services\PermissionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

class PermissionService implements PermissionServiceInterface
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository) {}

    /**
     * Lista permissions paginadas com filtro.
     */
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $permissions = $this->permissionRepository->paginateWithSearch($perPage, $search);

        // Log de visualização
        activity('central')
            ->causedBy(auth()->user())
            ->log('Listagem de permissions (Central) visualizada');

        return $permissions;
    }

    /**
     * Lista permissions paginadas com filtro e ordenação.
     */
    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        $permissions = $this->permissionRepository->paginateWithSearchAndSort($perPage, $search, $sortBy, $sortDir);

        // Log de visualização
        activity('central')
            ->causedBy(auth()->user())
            ->log('Listagem de permissions (Central) visualizada');

        return $permissions;
    }

    /**
     * Obtém detalhes de uma permission.
     */
    public function getPermission(int $id): Permission
    {
        return $this->permissionRepository->findOrFail($id);
    }

    /**
     * Cria nova permission.
     */
    public function createPermission(array $data): Permission
    {
        $permission = $this->permissionRepository->create([
            'name'       => $data['name'],
            'guard_name' => 'web',
        ]);

        // Log
        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($permission)
            ->log('Nova permission criada');

        return $permission;
    }

    /**
     * Atualiza permission (com proteção de permissions do sistema).
     */
    public function updatePermission(int $id, array $data): Permission
    {
        $permission = $this->permissionRepository->findOrFail($id);

        // Proteção contra edição de permissions do sistema
        if ($this->permissionRepository->isSystemPermission($permission)) {
            throw new \Exception('Não é permitido editar permissions do sistema.');
        }

        // Atualizar
        $this->permissionRepository->update($id, ['name' => $data['name']]);

        // Log
        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($permission)
            ->log("Permission \"{$permission->name}\" atualizada");

        return $this->permissionRepository->findOrFail($id);
    }

    /**
     * Deleta permission (com proteção de permissions do sistema).
     */
    public function deletePermission(int $id): bool
    {
        $permission = $this->permissionRepository->findOrFail($id);

        // Proteção contra deleção de permissions do sistema
        if ($this->permissionRepository->isSystemPermission($permission)) {
            throw new \Exception('Permissions do sistema não podem ser deletadas.');
        }

        // Log antes de deletar
        activity('central')
            ->causedBy(auth()->user())
            ->log("Permission \"{$permission->name}\" deletada");

        return $this->permissionRepository->delete($id);
    }
}
