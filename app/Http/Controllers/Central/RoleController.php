<?php

namespace App\Http\Controllers\Central;

use App\Contracts\Services\RoleServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * CRUD de Roles (Papéis/Funções) - Apenas Super Admin
 * Permite criar, editar, deletar roles e vincular permissions
 */
class RoleController extends Controller
{
    public function __construct(private RoleServiceInterface $roleService) {}

    /**
     * Lista todos os roles.
     * GET /central/roles
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Role::class);

        $filters = $request->only(['search']);

        $allowedSorts = ['name', 'description'];
        $sortBy  = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : null;
        $sortDir = in_array($request->sort_dir, ['asc', 'desc']) ? $request->sort_dir : 'asc';

        $roles = $this->roleService->paginateWithSort(15, $request->search, $sortBy, $sortDir);

        if ($request->ajax()) {
            return view('central.roles.includes._table', compact('roles'));
        }

        return view('central.roles.index', compact('roles', 'filters'));
    }

    /**
     * Exibe formulário de criação de role.
     * GET /central/roles/create
     */
    public function create(): View
    {
        $this->authorize('create', Role::class);

        $permissions = Permission::orderBy('name')->get();

        return view('central.roles.create', compact('permissions'));
    }

    /**
     * Armazena novo role no banco.
     * POST /central/roles
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'type'        => ['required', 'in:central,tenant'],
            'permissions' => ['nullable', 'array'],
        ]);

        $role = $this->roleService->createRole($validated);

        return redirect()
            ->route('central.roles.show', $role)
            ->with('success', "Role \"{$role->name}\" criado com sucesso.");
    }

    /**
     * Exibe detalhes de um role.
     * GET /central/roles/{role}
     */
    public function show(Request $request, Role $role): View
    {
        $this->authorize('view', $role);

        $role = $this->roleService->getRole($role->id);

        return view('central.roles.show', compact('role'));
    }

    /**
     * Exibe formulário de edição.
     * GET /central/roles/{role}/edit
     */
    public function edit(Role $role): View
    {
        $this->authorize('update', $role);

        $role = $this->roleService->getRole($role->id);
        $permissions = Permission::orderBy('name')->get();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('central.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Atualiza role.
     * PUT /central/roles/{role}
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', "unique:roles,name,{$role->id}"],
            'description' => ['nullable', 'string', 'max:500'],
            'type'        => ['required', 'in:central,tenant'],
            'permissions' => ['nullable', 'array'],
        ]);

        try {
            $role = $this->roleService->updateRole($role->id, $validated);

            return redirect()
                ->route('central.roles.show', $role)
                ->with('success', 'Role atualizado com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove role.
     * DELETE /central/roles/{role}
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('delete', $role);

        try {
            $role = $this->roleService->getRole($role->id);
            $this->roleService->deleteRole($role->id);

            return redirect()
                ->route('central.roles.index')
                ->with('success', "Role \"{$role->name}\" deletado com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Syncroniza permissions do role.
     * POST /central/roles/{role}/sync-permissions
     */
    public function syncPermissions(Request $request, Role $role): RedirectResponse
    {
        $this->authorize('update', $role);

        $request->validate([
            'permissions' => ['required', 'array'],
            'permissions.*' => ['exists:permissions,id'],
        ]);

        $this->roleService->syncPermissions($role->id, $request->permissions);

        return back()->with('success', 'Permissions sincronizadas com sucesso.');
    }
}
