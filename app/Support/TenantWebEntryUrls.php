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
        return rtrim(TenantUrl::appRoot(), '/').'/tenant/login';
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
        return self::loginEntryUrl($user);
    }

    /**
     * Login certo para o perfil: admin → /tenant/login, docente → /acesso/docente/entrar, aluno → /acesso/entrar.
     */
    public static function loginEntryUrl(User $user): string
    {
        if ($user->isSuperAdmin() || $user->hasRole('central_super_admin')) {
            return route('central.login');
        }

        if ($user->user_type === User::TYPE_TENANT_ADMIN || $user->hasTenantRole(User::TYPE_TENANT_ADMIN)) {
            return url('/tenant/login');
        }

        if ($user->accessesDocentePortal()) {
            return TenantContext::isSet()
                ? url('/acesso/docente/entrar')
                : self::portalDocenteLoginAbsolute($user);
        }

        return TenantContext::isSet()
            ? url('/acesso/entrar')
            : self::portalAlunoLoginAbsolute($user);
    }

    /**
     * @return array{label: string, hint: string}
     */
    public static function loginContext(?User $user): array
    {
        if ($user && ($user->user_type === User::TYPE_TENANT_ADMIN || $user->hasTenantRole(User::TYPE_TENANT_ADMIN))) {
            return [
                'label' => 'Área da gestão',
                'hint' => 'Painel administrativo da câmara: cursos, alunos, presença e certificação.',
            ];
        }

        if ($user && $user->accessesDocentePortal()) {
            return [
                'label' => 'Área do docente',
                'hint' => 'Acesso para professores e equipe pedagógica cadastrados pela coordenação.',
            ];
        }

        return [
            'label' => 'Área do aluno',
            'hint' => 'Cadastro e acesso seguros à área do aluno, cursos e comunicações da escola.',
        ];
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
