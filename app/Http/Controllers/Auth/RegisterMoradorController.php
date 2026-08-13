<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\RegisterMoradorRequest;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisterMoradorController extends Controller
{
    public function create(): View
    {
        if (TenantContext::getTenantId() === null) {
            abort(404);
        }

        $tenantId = TenantContext::getTenantId();
        $tenant = Tenant::find($tenantId);
        $settings = TenantAdminSetting::query()->where('tenant_id', $tenantId)->first();

        return view('auth.register-morador', compact('tenant', 'settings'));
    }

    public function store(RegisterMoradorRequest $request): RedirectResponse
    {
        if (TenantContext::getTenantId() === null) {
            throw ValidationException::withMessages([
                'email' => 'Cadastro disponível apenas no portal do município (subdomínio).',
            ]);
        }

        $complemento = trim((string) $request->complemento);
        $enderecoCompleto = implode(', ', array_filter([
            $request->logradouro.', '.$request->numero.($complemento ? ' '.$complemento : ''),
            $request->bairro,
            $request->cidade.' - '.strtoupper($request->uf),
            'CEP '.preg_replace('/(\d{5})(\d{3})/', '$1-$2', $request->cep),
        ]));

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'cpf' => $request->cpf,
            'phone' => $request->phone,
            'endereco_completo' => $enderecoCompleto,
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
            'email_verified_at' => now(),
        ]);

        $user->assignRole(User::TYPE_TENANT_USER);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('app.dashboard')
            ->with('success', 'Cadastro concluído. Você já pode solicitar orçamentos nas páginas das empresas.');
    }
}
