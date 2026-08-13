<?php

namespace App\Services;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Services\RoleServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService implements RoleServiceInterface
{
    public function __construct(private RoleRepositoryInterface $roleRepository) {}

    /**
     * Lista roles paginados com filtro.
     */
    public function paginate(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $roles = $this->roleRepository->paginateWithPermissions($perPage, $search);

        // Log de visualização
        activity('central')
            ->causedBy(auth()->user())
            ->log('Listagem de roles (Central) visualizada');

        return $roles;
    }

    /**
     * Lista roles paginados com filtro e ordenação.
     */
    public function paginateWithSort(int $perPage = 15, ?string $search = null, ?string $sortBy = null, string $sortDir = 'asc'): LengthAwarePaginator
    {
        $roles = $this->roleRepository->paginateWithPermissionsAndSort($perPage, $search, $sortBy, $sortDir);

        // Log de visualização
        activity('central')
            ->causedBy(auth()->user())
            ->log('Listagem de roles (Central) visualizada');

        return $roles;
    }

    /**
     * Obtém detalhes de um role.
     */
    public function getRole(int $id): Role
    {
        return $this->roleRepository->findWithPermissions($id);
    }

    /**
     * Cria novo role e vincula permissions.
     */
    public function createRole(array $data): Role
    {
        // Criar role
        $role = $this->roleRepository->create([
            'name'       => $data['name'],
            'type'       => $data['type'] ?? 'tenant',
            'guard_name' => 'web',
        ]);

        // Vincular permissions
        if (isset($data['permissions']) && !empty($data['permissions'])) {
            $this->roleRepository->syncPermissions($role, $data['permissions']);
        }

        // Log
        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log('Novo role criado');

        return $role;
    }

    /**
     * Atualiza role.
     */
    public function updateRole(int $id, array $data): Role
    {
        $role = $this->roleRepository->findOrFail($id);

        // Proteção contra edição de roles do sistema
        if ($this->roleRepository->isSystemRole($role)) {
            throw new \Exception('Não é permitido editar roles do sistema.');
        }

        // Atualizar
        $this->roleRepository->update($id, [
            'name' => $data['name'],
            'type' => $data['type'] ?? $role->type,
        ]);

        // Sincronizar permissions
        if (isset($data['permissions'])) {
            $this->roleRepository->syncPermissions($role, $data['permissions'] ?? []);
        }

        // Log
        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log("Role \"{$role->name}\" atualizado");

        return $this->roleRepository->findWithPermissions($id);
    }

    /**
     * Deleta role (com proteção de roles do sistema).
     */
    public function deleteRole(int $id): bool
    {
        $role = $this->roleRepository->findOrFail($id);

        // Proteção contra deleção de roles do sistema
        if ($this->roleRepository->isSystemRole($role)) {
            throw new \Exception('Roles do sistema não podem ser deletados.');
        }

        // Log antes de deletar
        activity('central')
            ->causedBy(auth()->user())
            ->log("Role \"{$role->name}\" deletado");

        return $this->roleRepository->delete($id);
    }

    /**
     * Sincroniza permissions do role.
     */
    public function syncPermissions(int $roleId, array $permissionIds): void
    {
        $role = $this->roleRepository->findOrFail($roleId);

        $this->roleRepository->syncPermissions($role, $permissionIds);

        activity('central')
            ->causedBy(auth()->user())
            ->performedOn($role)
            ->log("Permissions do role \"{$role->name}\" sincronizadas");
    }

    /**
     * Retorna roles disponíveis para tenants (excluindo super_admin).
     */
    public static function getTenantRoles(): array
    {
        return \Spatie\Permission\Models\Role::where('type', 'tenant')
            ->where('guard_name', 'web')
            ->pluck('description', 'description')
            
            ->toArray();
    }
}
