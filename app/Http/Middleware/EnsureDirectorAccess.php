<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\DirectorContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protege a área /diretor.
 *
 * Exige um diretor regional com pelo menos uma UF atribuída — sem UF a abrangência é vazia
 * e todas as telas ficariam em branco sem explicação.
 */
class EnsureDirectorAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()->guest(route('diretor.login'));
        }

        if (! $user->isTenantDirector()) {
            return redirect('/')
                ->with('error', 'Acesso negado. Esta área é exclusiva dos diretores regionais.');
        }

        // O contexto é estático: limpar na entrada evita herdar abrangência de outra request.
        DirectorContext::forget();

        if (! DirectorContext::hasScope()) {
            abort(403, 'Nenhuma UF atribuída a este diretor. Solicite a atribuição na Central.');
        }

        $response = $next($request);

        DirectorContext::forget();

        return $response;
    }
}
