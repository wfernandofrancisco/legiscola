<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Support\TenantContext;
use App\Rules\TurnstileRule;
use App\Support\TenantWebEntryUrls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Autenticação para Tenant (Admin/Responsible/User)
 * Login para clientes do SaaS
 * Rota: /login (ou /login/tenant)
 */
class TenantAuthController extends Controller
{
    /**
     * Exibe formulário de login do Tenant
     * GET /login
     */
    public function showLoginForm(): View
    {
        $tenantId = TenantContext::getTenantId();
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        $settings = $tenantId ? TenantAdminSetting::query()->where('tenant_id', $tenantId)->first() : null;

        return view('auth.tenant-login', compact('tenant', 'settings'));
    }

    /**
     * Autentica usuário do tenant
     * POST /login
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'cf-turnstile-response' => [new TurnstileRule],
        ], [
            'email.required' => 'E-mail é obrigatório.',
            'email.email' => 'Digite um e-mail válido.',
            'password.required' => 'Senha é obrigatória.',
        ]);

        $emailNormalized = strtolower(trim((string) $credentials['email']));

        // Buscar por e-mail sem TenantScope: no subdomínio do portal o contexto filtra users.tenant_id
        // e o admin do município deixava de ser encontrado (falso "credencial inválida").
        // Comparação case-insensitive + trim evita divergência com o que está gravado no banco.
        $user = User::query()
            ->withoutGlobalScope(TenantScope::class)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$emailNormalized])
            ->first();

        if (! $user) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas.');
        }

        // Super-admin usa login central; não usar hasRole aqui (roles Spatie podem estar legadas/erradas).
        if ($user->isSuperAdmin()) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas. Se é proprietário do sistema, use o login específico.');
        }

        if ($user->status !== User::STATUS_ATIVO) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Conta inativa ou aguardando aprovação.');
        }

        // Validar se tem tenant
        if (! $user->tenant_id) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Usuário não vinculado a nenhum tenant.');
        }

        if (TenantContext::isSet()
            && (int) $user->tenant_id !== (int) TenantContext::getTenantId()) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Este e-mail não pertence a este portal/município. Acesse o login pelo endereço correto da sua escola ou use /tenant/login.');
        }

        // Hash armazenado: usar valor bruto do banco (evita efeitos colaterais do cast "hashed" na leitura).
        $passwordHash = (string) ($user->getRawOriginal('password') ?? '');

        if ($passwordHash === '' || ! Hash::check($credentials['password'], $passwordHash)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas.');
        }

        // Fazer login
        Auth::login($user, $request->boolean('remember'));

        // Log
        activity('admin')
            ->causedBy($user)
            ->log('Usuário autenticado no painel Tenant');

        // Redireciona para painel correto baseado na role
        return $this->redirectToMissedDashboard($user);
    }

    /**
     * Redireciona para o painel correto baseado na role
     */
    private function redirectToMissedDashboard(User $user): RedirectResponse
    {
        // Fonte de verdade para redirecionamento é user_type (mais confiável que role em cenários legados)
        if ($user->user_type === User::TYPE_TENANT_ADMIN) {
            return redirect(route('admin.dashboard'));
        }

        if ($user->user_type === User::TYPE_TENANT_MANAGER || $user->user_type === User::TYPE_TENANT_USER) {
            if ($user->isTenantProfessor()) {
                return redirect(route('professor.dashboard'));
            }

            if ($user->user_type === User::TYPE_TENANT_MANAGER) {
                return redirect(route('professor.dashboard'));
            }

            return redirect(route('app.dashboard'));
        }

        // Fallback defensivo por role, caso user_type venha inconsistente
        if ($user->hasRole('tenant_admin') || $user->hasRole('tenant-admin')) {
            return redirect(route('admin.dashboard'));
        }

        if ($user->isTenantProfessor()) {
            return redirect(route('professor.dashboard'));
        }

        if ($user->hasRole('tenant_manager') || $user->hasRole('tenant-manager')) {
            return redirect(route('professor.dashboard'));
        }

        // Usuário normal
        return redirect(route('app.dashboard'));
    }

    /**
     * Logout do usuário
     * POST /logout
     */
    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();

        activity('admin')
            ->causedBy($user)
            ->log('Usuário desconectado do painel Tenant');

        $target = $user ? TenantWebEntryUrls::afterTenantWebLogout($user) : TenantWebEntryUrls::tenantPanelLoginAbsolute();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->away($target);
    }
}
