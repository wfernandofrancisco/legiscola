<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Throwable;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route($request->user()->tenantHomeRouteName(), absolute: false));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'email' => 'Não foi possível enviar o e-mail. Confirme se o endereço está correto e se a caixa existe; em ambiente de testes use um e-mail real ou o driver «log» / Mailpit. Se usa SMTP, verifique também usuário, senha e remetente no .env.',
            ]);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
