<?php

namespace App\Http\Middleware;

use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica TenantContext com base no usuário Sanctum para o escopo global por tenant_id.
 */
class ApplyTenantContextFromAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if ($request->user()) {
                TenantContext::syncFromUser($request->user());
            }

            return $next($request);
        } finally {
            TenantContext::clear();
        }
    }
}
