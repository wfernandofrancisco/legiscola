<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrUpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function __construct(
        private UserServiceInterface $userService,
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $currentUser = auth()->user();
        $tenantId = $currentUser->tenant_id;

        $allowedSorts = ['name', 'email', 'status', 'user_type', 'created_at'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : null;
        $sortDir = in_array($request->sort_dir, ['asc', 'desc']) ? $request->sort_dir : 'asc';

        $users = $this->userRepository->paginateByTenant(
            tenantId: $tenantId,
            perPage: 8,
            search: $request->search,
            status: $request->status,
            userType: $request->user_type,
            sortBy: $sortBy,
            sortDir: $sortDir
        );

        // Log activity
        activity('admin')
            ->causedBy($currentUser)
            ->log('Listagem de usuários visualizada');

        $filters = $request->only(['status', 'user_type', 'search']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Usuários']
        ];

        if ($request->ajax()) {
            return view('admin.users.includes._table', compact('users'));
        }

        return view('admin.users.index', compact('users', 'filters', 'breadcrumbs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Usuários', 'href' => route('admin.users.index')],
            ['label' => 'Novo usuário']
        ];

        $userTypes = \App\Enums\UserType::options();
        $statuses = \App\Enums\UserStatus::options();

        return view('admin.users.create', compact('breadcrumbs', 'userTypes', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreOrUpdateUserRequest $request)
    {
        $this->authorize('create', User::class);

        $this->userService->createUserAsAdmin(
            auth()->user()->tenant_id,
            $request->validated()
        );

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário criado com sucesso! Uma senha temporária foi enviada por e-mail.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        $this->authorize('tenant-access', $user);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Usuários', 'href' => route('admin.users.index')],
            ['label' => 'Editar usuário']
        ];

        $userTypes = \App\Enums\UserType::options();
        $statuses = \App\Enums\UserStatus::options();

        return view('admin.users.edit', compact('user', 'breadcrumbs', 'userTypes', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreOrUpdateUserRequest $request, User $user)
    {
        $this->authorize('tenant-access', $user);
        
        $this->userService->updateUser($user->id, $request->validated());

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário atualizado com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('tenant-access', $user);

        // Não permitir deletar o próprio usuário
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Você não pode deletar sua própria conta.');
        }

        $this->userService->deleteUser($user->id);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuário deletado com sucesso!');
    }

    /**
     * Show the form for editing the profile.
     */
    public function profileEdit(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Perfil']
        ];

        return view('admin.users.profile', compact('breadcrumbs'));
    }

    /**
     * Update user profile data.
     */
    public function profileUpdate(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . auth()->id()],
            'phone' => ['nullable', 'string', 'max:20'],
        ], [
            'name.required' => 'O nome é obrigatório.',
            'email.required' => 'O e-mail é obrigatório.',
            'email.email' => 'O e-mail deve ser válido.',
            'email.unique' => 'Este e-mail já está registrado.',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->fill([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ])->save();

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Seus dados foram atualizados com sucesso!');
    }

    /**
     * Change user password.
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ], [
            'current_password.required' => 'A senha atual é obrigatória.',
            'current_password.current_password' => 'A senha atual está incorreta.',
            'password.required' => 'A nova senha é obrigatória.',
            'password.confirmed' => 'As senhas não correspondem.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        ]);

        /** @var \App\Models\User $user */
        $user = auth()->user();
        $user->fill([
            'password' => Hash::make($request->password),
        ])->save();

        return redirect()->route('admin.profile.edit')
            ->with('success', 'Sua senha foi alterada com sucesso!');
    }
}
