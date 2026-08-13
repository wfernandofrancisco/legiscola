<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Painel /docente: docentes (tenant_professor / teachers) e gestores escolares (tenant_manager).
 */
class EnsureDocentePortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        abort_unless($user && $user->accessesDocentePortal(), 403);

        return $next($request);
    }
}
