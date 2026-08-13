<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\TurnstileRule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Autenticação para Super Admin (Central)
 * Login exclusivo para proprietário do sistema
 * Rota: /login/central
 */
class CentralAuthController extends Controller
{
    /**
     * Exibe formulário de login da Central
     * GET /login/central
     */
    public function showLoginForm(): View
    {
        return view('auth.central-login');
    }

    /**
     * Autentica usuário super-admin
     * POST /login/central
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

        // Buscar usuário
        $user = User::where('email', $credentials['email'])->first();

        // Validar se é super-admin
        if (!$user || !$user->hasRole('central_super_admin')) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas ou permissão insuficiente para Central.');
        }

        // Validar senha
        if (!\Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Senha incorreta.');
        }

        // Fazer login
        Auth::login($user, $request->boolean('remember'));

        // Log
        activity('central')
            ->causedBy($user)
            ->log('Super Admin autenticado no painel Central');

        return redirect()->intended(route('central.dashboard'));
    }

    /**
     * Logout do usuário
     * POST /logout/central
     */
    public function logout(Request $request): RedirectResponse
    {
        activity('central')
            ->causedBy(auth()->user())
            ->log('Super Admin desconectado do painel Central');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('central.login');
    }
}
