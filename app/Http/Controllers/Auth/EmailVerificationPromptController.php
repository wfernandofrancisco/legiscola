<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request): RedirectResponse|View
    {
        if ($request->user()->hasVerifiedEmail()) {
            // Redirecionar para dashboard correto baseado na role
            if ($request->user()->hasRole('central_super_admin')) {
                return redirect()->intended(route('central.dashboard', absolute: false));
            }
            return redirect()->intended(route('app.dashboard', absolute: false));
        }

        // Usar view específica para tenant
        $view = $request->user() && $request->user()->tenant_id ? 'auth.tenant-verify-email' : 'auth.verify-email';

        return view($view);
    }
}
