<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetMail;
use App\Models\Tenant;
use App\Models\TenantAdminSetting;
use App\Models\User;
use App\Rules\TurnstileRule;
use App\Support\TenantContext;
use App\Support\TenantUrl;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        $tenantId = TenantContext::getTenantId();
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        $settings = $tenantId ? TenantAdminSetting::query()->where('tenant_id', $tenantId)->first() : null;

        return view('auth.forgot-password', compact('tenant', 'settings'));
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'cf-turnstile-response' => [new TurnstileRule],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Não encontramos uma conta com este endereço de e-mail.']);
        }

        // Create password reset token
        $token = Password::createToken($user);
        $resetUrl = TenantUrl::tenantRoute($user, 'password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);

        // Send custom email
        Mail::to($user->email)->send(new PasswordResetMail($user, $resetUrl));

        return back()->with('status', 'Enviamos um link de recuperação de senha para seu e-mail!');
    }
}
