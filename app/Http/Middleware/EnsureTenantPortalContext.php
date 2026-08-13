<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rotas do portal público do tenant exigem subdomínio (TenantContext definido).
 */
class EnsureTenantPortalContext
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! TenantContext::isSet()) {
            abort(404);
        }

        return $next($request);
    }
}
