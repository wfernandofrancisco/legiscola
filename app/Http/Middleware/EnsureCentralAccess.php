<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rotas da Central
 * Apenas super-admin pode acessar
 */
class EnsureCentralAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não autenticado, redireciona para login da central
        if (!auth()->check()) {
            return redirect()->route('central.login');
        }

        /** @var User $user */
        $user = auth()->user();

        // Se não é super-admin, nega acesso
        if (!$user->isSuperAdmin()) {
            return redirect('/')
                ->with('error', 'Acesso negado. Esta área é exclusiva da Central.');
        }

        return $next($request);
    }
}
