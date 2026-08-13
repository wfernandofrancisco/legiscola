<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Detecta o tenant da requisição e registra em TenantContext.
 *
 * 1) Subdomínio (ex.: cliente1.app.com) → slug do tenant
 * 2) Usuário autenticado → tenant_id do usuário
 *
 * Não aplicar no módulo Central.
 */
class SetTenantContext
{
    private const RESERVED_SUBDOMAINS = ['www', 'api', 'mail', 'smtp', 'ftp'];

    public function handle(Request $request, Closure $next): mixed
    {
        $this->resolveFromSubdomain($request) || $this->resolveFromUser($request);

        /*
         * Garantir que route() / url()->route() montem URLs no mesmo host da requisição
         * (evita localhost / APP_URL fixo quando o usuário navega pelo subdomínio do tenant).
         */
        if (TenantContext::isSet()) {
            URL::forceRootUrl($request->getSchemeAndHttpHost());
        }

        $this->abortIfAuthenticatedUserTenantMismatch($request);

        $response = $next($request);

        TenantContext::clear();

        return $response;
    }

    private function resolveFromSubdomain(Request $request): bool
    {
        $appDomain = config('app.domain');

        if (! $appDomain) {
            return false;
        }

        $host = $request->getHost();
        $subdomain = $this->extractSubdomain($host, $appDomain);

        if (! $subdomain || in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) {
            return false;
        }

        $tenant = Tenant::query()
            ->where('slug', $subdomain)
            ->where('status', Tenant::STATUS_ATIVO)
            ->first();

        if (! $tenant) {
            return false;
        }

        TenantContext::set($tenant->id);

        return true;
    }

    private function resolveFromUser(Request $request): bool
    {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        TenantContext::syncFromUser($user);

        return TenantContext::isSet();
    }

    private function extractSubdomain(string $host, string $appDomain): ?string
    {
        if (! str_ends_with($host, '.'.$appDomain)) {
            return null;
        }

        $subdomain = substr($host, 0, strlen($host) - strlen('.'.$appDomain));

        return str_contains($subdomain, '.') ? null : $subdomain;
    }

    /**
     * Impede usuário logado num tenant de navegar pelo subdomínio de outro.
     *
     * O subdomínio tem prioridade no TenantContext; sem esta checagem, o usuário poderia ver
     * dados de outro cliente enquanto ainda está autenticado no seu.
     */
    private function abortIfAuthenticatedUserTenantMismatch(Request $request): void
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->tenant_id || ! TenantContext::isSet()) {
            return;
        }

        if ($user->isSuperAdmin() || $user->hasRole('central_super_admin')) {
            return;
        }

        if ((int) $user->tenant_id !== (int) TenantContext::getTenantId()) {
            abort(403, 'Este endereço não corresponde à sua empresa.');
        }
    }
}
