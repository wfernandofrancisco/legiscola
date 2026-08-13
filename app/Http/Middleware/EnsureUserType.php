<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe acesso por coluna users.user_type (valores aceitos separados por | ou vírgula).
 *
 * Ex.: user.type:tenant_admin | user.type:super_admin | user.type:tenant_responsible|tenant_manager
 */
class EnsureUserType
{
    public function handle(Request $request, Closure $next, string $allowedList): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Não autenticado.'], 401)
                : redirect()->guest(route('tenant.login'));
        }

        $allowed = array_values(array_filter(array_map(
            'trim',
            preg_split('/[|,]/', $allowedList) ?: []
        )));

        if ($allowed === [] || ! in_array($user->user_type, $allowed, true)) {
            abort(403, 'Tipo de usuário não autorizado para esta área.');
        }

        return $next($request);
    }
}
