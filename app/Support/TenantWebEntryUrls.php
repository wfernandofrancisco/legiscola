<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;

/**
 * URLs de entrada do painel tenant (login global) e do portal (aluno / docente),
 * usadas após logout e em redirecionamentos de acesso negado (403) na área web.
 */
final class TenantWebEntryUrls
{
    /**
     * Login global do tenant (apex — sem subdomínio), ex.: /tenant/login.
     */
    public static function tenantPanelLoginAbsolute(): string
    {
        return rtrim((string) config('app.url'), '/').'/tenant/login';
    }

    /**
     * Portal do tenant: tela de login do aluno (área pública).
     */
    public static function portalAlunoLoginAbsolute(User $user): string
    {
        return TenantUrl::onTenant($user, 'acesso/entrar');
    }

    /**
     * Portal do tenant: tela de login do docente (área pública).
     */
    public static function portalDocenteLoginAbsolute(User $user): string
    {
        return TenantUrl::onTenant($user, 'acesso/docente/entrar');
    }

    /**
     * Destino após logout (ou 403 web) conforme perfil do usuário.
     */
    public static function afterTenantWebLogout(User $user): string
    {
        if ($user->isSuperAdmin() || $user->hasRole('central_super_admin')) {
            return route('central.login');
        }

        if ($user->user_type === User::TYPE_TENANT_ADMIN || $user->hasTenantRole(User::TYPE_TENANT_ADMIN)) {
            return self::tenantPanelLoginAbsolute();
        }

        if ($user->accessesDocentePortal()) {
            return self::portalDocenteLoginAbsolute($user);
        }

        return self::portalAlunoLoginAbsolute($user);
    }

    /**
     * Evita loop de redirecionamento em 403 já na tela de entrada.
     */
    public static function shouldSkipForbiddenRedirect(Request $request): bool
    {
        if ($request->expectsJson()) {
            return true;
        }

        if ($request->is('central', 'central/*')) {
            return true;
        }

        $path = ltrim($request->path(), '/');

        return in_array($path, [
            'tenant/login',
            'acesso/entrar',
            'acesso/docente/entrar',
        ], true);
    }
}
