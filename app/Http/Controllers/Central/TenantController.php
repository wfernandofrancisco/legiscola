<?php

namespace App\Http\Controllers\Central;

use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Services\TenantServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreTenantRequest;
use App\Http\Requests\Api\UpdateTenantRequest;
use App\Mail\PasswordResetMail;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class TenantController extends Controller
{
    public function __construct(
        private TenantServiceInterface $tenantService,
        private TenantRepositoryInterface $tenantRepository
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tenant::class);

        $currentUser = auth()->user();
        $filters = $request->only(['status', 'search']);

        $allowedSorts = ['name', 'slug', 'domain', 'users_count', 'status', 'cnpj', 'razao_social'];
        $sortBy = in_array($request->sort_by, $allowedSorts) ? $request->sort_by : null;
        $sortDir = in_array($request->sort_dir, ['asc', 'desc']) ? $request->sort_dir : 'asc';

        $tenants = $this->tenantRepository->paginateWithSort(
            perPage: 15,
            search: $request->search,
            sortBy: $sortBy,
            sortDir: $sortDir
        );

        // Aplicar filtro de status se necessário
        if ($request->status) {
            $tenants = $tenants->where('status', $request->status);
        }

        if ($currentUser instanceof User) {
            activity('central')
                ->causedBy($currentUser)
                ->log('Listagem de tenants (central) visualizada');
        }

        if ($request->ajax()) {
            return view('central.tenants.includes._table', compact('tenants'));
        }

        return view('central.tenants.index', compact('tenants', 'filters'));
    }

    public function create(): View
    {
        $this->authorize('create', Tenant::class);

        return view('central.tenants.create');
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $this->authorize('create', Tenant::class);

        $data = $request->validated();
        $invite = [
            'name' => $data['invite_name_1'] ?? null,
            'email' => $data['invite_email_1'] ?? null,
            'cargo' => $data['invite_cargo_1'] ?? null,
        ];
        unset($data['invite_name_1'], $data['invite_email_1'], $data['invite_cargo_1']);

        $invitedUser = null;

        $tenant = DB::transaction(function () use ($data, $invite, &$invitedUser) {
            $tenant = $this->tenantService->createTenant($data);

            if (filled($invite['email'] ?? null) && filled($invite['name'] ?? null)) {
                $invitedUser = $this->createInvitedUserForTenant($tenant, $invite);
            }

            return $tenant;
        });

        if ($invitedUser) {
            $this->sendPasswordSetupEmail($invitedUser);
        }

        $msg = "Cliente \"{$tenant->display_name}\" criado com sucesso.";
        if ($invitedUser) {
            $msg .= " Convite enviado para {$invitedUser->email}.";
        }

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', $msg);
    }

    public function show(Tenant $tenant): View
    {
        $this->authorize('view', $tenant);

        $tenant->load(['users']);

        if (auth()->user()) {
            activity('central')
                ->causedBy(auth()->user())
                ->performedOn($tenant)
                ->log('Detalhes do tenant visualizados');
        }

        return view('central.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant): View
    {
        $this->authorize('update', $tenant);

        $tenant->load(['users']);

        return view('central.tenants.edit', compact('tenant'));
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $this->tenantService->updateTenant($tenant->id, $request->validated());

        return redirect()
            ->route('central.tenants.show', $tenant)
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $this->authorize('delete', $tenant);

        try {
            $this->tenantService->deleteTenant($tenant->id);

            return redirect()
                ->route('central.tenants.index')
                ->with('success', "Cliente \"{$tenant->name}\" removido com sucesso.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update(['status' => Tenant::STATUS_ATIVO]);

        return back()->with('success', "Tenant \"{$tenant->name}\" ativado.");
    }

    public function deactivate(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update(['status' => Tenant::STATUS_INATIVO]);

        return back()->with('success', "Tenant \"{$tenant->name}\" desativado.");
    }

    public function suspend(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update(['status' => Tenant::STATUS_SUSPENSO]);

        return back()->with('success', "Tenant \"{$tenant->name}\" suspenso.");
    }

    public function activateCadastro(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update(['cadastro_status' => Tenant::CADASTRO_ATIVO]);

        return back()->with('success', 'Cadastro do cliente ativado.');
    }

    public function deactivateCadastro(Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $tenant->update(['cadastro_status' => Tenant::CADASTRO_INATIVO]);

        return back()->with('success', 'Cadastro do cliente desativado.');
    }

    public function linkUser(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'cargo' => ['nullable', 'string', 'max:100'],
        ]);

        $this->tenantService->linkUserToTenant($tenant->id, (int) $request->user_id, $request->cargo);

        $user = User::query()->find($request->user_id);

        return back()->with('success', "Usuário \"{$user->name}\" vinculado ao cliente.");
    }

    public function inviteUser(Request $request, Tenant $tenant): RedirectResponse
    {
        $this->authorize('update', $tenant);

        $inviteData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'cargo' => ['nullable', 'string', 'max:100'],
        ]);

        $user = DB::transaction(fn () => $this->createInvitedUserForTenant($tenant, $inviteData));

        $this->sendPasswordSetupEmail($user);

        return back()->with('success', "Convite enviado para \"{$user->name}\" ({$user->email}).");
    }

    private function createInvitedUserForTenant(Tenant $tenant, array $inviteData): User
    {
        $user = User::create([
            'tenant_id' => $tenant->id,
            'name' => $inviteData['name'],
            'email' => $inviteData['email'],
            'password' => Str::random(40),
            'user_type' => User::TYPE_TENANT_ADMIN,
            'status' => User::STATUS_PENDENTE,
        ]);

        $this->assignTenantRoles($user);

        return $user->fresh();
    }

    private function assignTenantRoles(User $user): void
    {
        if (! Role::query()->where('name', User::TYPE_TENANT_ADMIN)->where('guard_name', 'web')->exists()) {
            return;
        }

        $user->assignRole(User::TYPE_TENANT_ADMIN);
    }

    private function sendPasswordSetupEmail(User $user): void
    {
        $token = Password::createToken($user);
        $resetUrl = TenantUrl::tenantRoute($user, 'password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));
    }
}
