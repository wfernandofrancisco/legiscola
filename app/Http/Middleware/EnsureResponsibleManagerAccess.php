<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Painel /professor (Responsible): gestores do tenant (user_type ou role Spatie).
 * Conta só como docente → redireciona para /docente.
 */
class EnsureResponsibleManagerAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $isManagerByColumn = $user->isTenantManager();
        $isManagerByRole = $user->hasTenantRole(User::TYPE_TENANT_MANAGER);

        if (! $isManagerByColumn && ! $isManagerByRole && $user->isTenantProfessor()) {
            return redirect()->route('professor.dashboard');
        }

        abort_unless($isManagerByColumn || $isManagerByRole, 403, 'Sem permissão para o painel do gestor (responsável).');

        return $next($request);
    }
}
