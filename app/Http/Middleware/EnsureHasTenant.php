<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;

/**
 * Garante que o usuário pertence a uma empresa antes de acessar
 * módulos que exigem contexto de tenant (Admin, Responsible).
 * Deve ser usado APÓS o SetTenantContext.
 */
class EnsureHasTenant
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (! TenantContext::isSet()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Você não está vinculado a nenhuma empresa.',
                ], 403);
            }

            return redirect()->route('app.dashboard')
                ->with('error', 'Você precisa estar vinculado a uma empresa para acessar esta área.');
        }

        return $next($request);
    }
}
