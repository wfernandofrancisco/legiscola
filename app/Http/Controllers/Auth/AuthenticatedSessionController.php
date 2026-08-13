<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $user = $request->user();

        $destination = match (true) {
            $user->user_type === User::TYPE_SUPER_ADMIN => route('central.dashboard'),
            $user->user_type === User::TYPE_TENANT_ADMIN => route('admin.dashboard'),
            $user->isTenantProfessor() => route('professor.dashboard'),
            $user->isTenantManager() || $user->hasTenantRole(User::TYPE_TENANT_MANAGER) => route('professor.dashboard'),
            default => route('app.dashboard'),
        };

        return redirect()->intended($destination);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
