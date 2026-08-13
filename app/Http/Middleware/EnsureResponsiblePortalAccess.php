<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * tenant-admin: acesso total ao módulo responsável do tenant.
 * tenant-manager: precisa estar vinculado a ao menos uma empresa (aprovação).
 */
class EnsureResponsiblePortalAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        if ($user->hasTenantRole(User::TYPE_TENANT_ADMIN)) {
            return $next($request);
        }

        if ($user->hasTenantRole(User::TYPE_TENANT_MANAGER) && $user->empresaResponsibleAssignments()->exists()) {
            return $next($request);
        }

        abort(403, 'Você ainda não está vinculado a uma empresa ou seu cadastro está em análise.');
    }
}
