<?php

namespace App\Http\Controllers\Central;

use App\Contracts\Services\PermissionServiceInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

/**
 * CRUD de Permissions (Permissões) - Apenas Super Admin
 * Permite criar, editar, deletar permissões
 */
class PermissionController extends Controller
{
    public function __construct(private PermissionServiceInterface $permissionService) {}

    /**
     * Lista todas as permissions.
     * GET /central/permissions
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Permission::class);

        $filters = $request->only(['search']);

        $allowedSorts = ['name', 'description'];
        $sortBy  = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : null;
        $sortDir = in_array($request->sort_dir, ['asc', 'desc']) ? $request->sort_dir : 'asc';

        $permissions = $this->permissionService->paginateWithSort(15, $request->search, $sortBy, $sortDir);

        if ($request->ajax()) {
            return view('central.permissions.includes._table', compact('permissions'));
        }

        return view('central.permissions.index', compact('permissions', 'filters'));
    }

    /**
     * Exibe formulário de criação de permission.
     * GET /central/permissions/create
     */
    public function create(): View
    {
        $this->authorize('create', Permission::class);

        return view('central.permissions.create');
    }

    /**
     * Armazena nova permission.
     * POST /central/permissions
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Permission::class);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $permission = $this->permissionService->createPermission($validated);

        return redirect()
            ->route('central.permissions.show', $permission)
            ->with('success', "Permission \"{$permission->name}\" criada com sucesso.");
    }

    /**
     * Exibe detalhes de uma permission.
     * GET /central/permissions/{permission}
     */
    public function show(Permission $permission): View
    {
        $this->authorize('view', $permission);

        $permission = $this->permissionService->getPermission($permission->id);

        return view('central.permissions.show', compact('permission'));
    }

    /**
     * Exibe formulário de edição.
     * GET /central/permissions/{permission}/edit
     */
    public function edit(Permission $permission): View
    {
        $this->authorize('update', $permission);

        $permission = $this->permissionService->getPermission($permission->id);

        return view('central.permissions.edit', compact('permission'));
    }

    /**
     * Atualiza permission.
     * PUT /central/permissions/{permission}
     */
    public function update(Request $request, Permission $permission): RedirectResponse
    {
        $this->authorize('update', $permission);

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', "unique:permissions,name,{$permission->id}"],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $permission = $this->permissionService->updatePermission($permission->id, $validated);

            return redirect()
                ->route('central.permissions.show', $permission)
                ->with('success', 'Permission atualizada com sucesso.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Remove permission.
     * DELETE /central/permissions/{permission}
     */
    public function destroy(Permission $permission): RedirectResponse
    {
        $this->authorize('delete', $permission);

        try {
            $permission = $this->permissionService->getPermission($permission->id);
            $this->permissionService->deletePermission($permission->id);

            return redirect()
                ->route('central.permissions.index')
                ->with('success', "Permission \"{$permission->name}\" deletada com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
