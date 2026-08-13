<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        $users = $tenant->users()
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->when($request->status, fn($query) => $query->where('status', $request->status))
            ->when($request->user_type, fn($query) => $query->where('user_type', $request->user_type))
            ->with('tenant')
            ->paginate(15);

        return view('central.tenants.users.index', compact('tenant', 'users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tenant $tenant, User $user)
    {
        $this->authorize('update', $tenant);

        if ((int) $user->tenant_id !== (int) $tenant->id) {
            abort(404);
        }

        $request->validate([
            'status' => 'required|in:ativo,inativo'
        ]);

        $user->update(['status' => $request->status]);

        return redirect()
            ->back()
            ->with('success', "Usuário {$user->name} foi " . ($request->status === 'ativo' ? 'ativado' : 'desativado') . " com sucesso.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
