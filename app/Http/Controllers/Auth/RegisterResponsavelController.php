<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RegisterResponsavelRequest;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Models\User;
use App\Services\EmpresaResponsibleClaimService;
use App\Support\TenantContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterResponsavelController extends Controller
{
    public function __construct(
        private EmpresaResponsibleClaimService $claimService
    ) {}

    public function create(): View
    {
        if (TenantContext::getTenantId() === null) {
            abort(404);
        }

        $tenantId = TenantContext::getTenantId();
        $tenant = Tenant::find($tenantId);
        $settings = TenantAdminSetting::query()->where('tenant_id', $tenantId)->first();

        return view('auth.register-responsavel', compact('tenant', 'settings'));
    }

    public function store(RegisterResponsavelRequest $request): RedirectResponse
    {
        if (TenantContext::getTenantId() === null) {
            throw ValidationException::withMessages([
                'email' => 'Cadastro disponível apenas no portal do município (subdomínio).',
            ]);
        }

        $user = User::create([
            'tenant_id' => TenantContext::getTenantId(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(User::TYPE_TENANT_USER);

        try {
            $this->claimService->createForUser(
                $user,
                $request->cnpj_empresa,
                $request->razao_social_informada,
                $request->mensagem
            );
        } catch (\InvalidArgumentException $e) {
            $user->forceDelete();

            throw ValidationException::withMessages(['cnpj_empresa' => $e->getMessage()]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('app.dashboard')
            ->with('success', 'Cadastro recebido. Sua solicitação de vínculo com a empresa será analisada pela administração do portal.');
    }
}
