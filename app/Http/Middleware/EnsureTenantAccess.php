<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware para proteger rotas do Tenant
 * Usuários do tenant não podem ser super-admin
 */
class EnsureTenantAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Se não autenticado, redireciona para login do tenant
        if (!auth()->check()) {
            return redirect()->route('tenant.login');
        }

        // Se é super-admin, redireciona para central
        if (auth()->user()->hasRole('central_super_admin')) {
            return redirect()->route('central.dashboard')
                ->with('error', 'Super Admin deve usar o painel Central.');
        }

        // Se não tem tenant, nega acesso
        if (!auth()->user()->tenant_id) {
            return redirect('/')
                ->with('error', 'Usuário não vinculado a nenhum tenant.');
        }

        return $next($request);
    }
}
