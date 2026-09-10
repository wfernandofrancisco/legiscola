<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\TurnstileRule;
use App\Support\DirectorContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Autenticação do Diretor Regional — /login/diretor.
 *
 * Mesmo guard `web` da Central; o que separa as áreas é user_type + a role Spatie.
 */
class DirectorAuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.director-login');
    }

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

        $user = User::query()->where('email', $credentials['email'])->first();

        $isDirector = $user
            && $user->isTenantDirector()
            && $user->hasTenantRole(User::TYPE_TENANT_DIRECTOR);

        if (! $isDirector || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Credenciais inválidas ou permissão insuficiente para a área do diretor.');
        }

        if (! $user->isAtivo()) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Este acesso está inativo. Procure a Central.');
        }

        Auth::login($user, $request->boolean('remember'));

        activity('diretor')
            ->causedBy($user)
            ->log('Diretor regional autenticado');

        return redirect()->intended(route('diretor.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        activity('diretor')
            ->causedBy($request->user())
            ->log('Diretor regional desconectado');

        DirectorContext::forget();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('diretor.login');
    }
}
