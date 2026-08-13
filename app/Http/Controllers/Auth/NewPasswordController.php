<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Scopes\TenantScope;
use App\Support\TenantWebEntryUrls;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        $user = $this->findUserByEmail((string) $request->query('email', ''));
        $context = TenantWebEntryUrls::loginContext($user);

        return view('auth.reset-password', [
            'request' => $request,
            'loginUrl' => $user ? TenantWebEntryUrls::loginEntryUrl($user) : url('/tenant/login'),
            'contextLabel' => $context['label'],
            'contextHint' => $context['hint'],
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status != Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __($status)]);
        }

        $user = $this->findUserByEmail((string) $request->input('email'));
        $loginUrl = $user ? TenantWebEntryUrls::loginEntryUrl($user) : url('/tenant/login');

        return redirect()->away($loginUrl)->with('status', 'Senha redefinida. Entre com a nova senha.');
    }

    private function findUserByEmail(string $email): ?User
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            return null;
        }

        return User::query()
            ->withoutGlobalScope(TenantScope::class)
            ->whereRaw('LOWER(TRIM(email)) = ?', [$normalized])
            ->first();
    }
}
