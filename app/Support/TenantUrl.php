<?php

namespace App\Support;

use App\Models\Tenant;
use App\Models\User;

class TenantUrl
{
    public static function baseUrlForUser(?User $user): string
    {
        return self::baseUrlForTenant($user?->tenant);
    }

    public static function baseUrlForTenant(?Tenant $tenant): string
    {
        $appUrl = (string) config('app.url');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($appUrl, PHP_URL_HOST) ?: '';
        $port = parse_url($appUrl, PHP_URL_PORT);

        $appDomain = self::normalizeDomain((string) config('app.domain'));
        $isLocal = self::isLocal($host);

        // Porta do artisan serve (8000) só no Laragon. Em produção nunca entra no logout/e-mail.
        $urlPort = $isLocal ? ($port ?: 8000) : null;

        // Em ambiente local, sempre prioriza slug + APP_DOMAIN para evitar links em dominio de producao do tenant.
        if ($tenant && filled($tenant->slug) && filled($appDomain) && $isLocal) {
            return self::buildUrl($scheme, $tenant->slug.'.'.$appDomain, $urlPort);
        }

        if ($tenant && filled($tenant->domain)) {
            return self::buildUrl($scheme, self::normalizeDomain((string) $tenant->domain), $urlPort);
        }

        if ($tenant && filled($tenant->slug) && filled($appDomain)) {
            return self::buildUrl($scheme, $tenant->slug.'.'.$appDomain, $urlPort);
        }

        return self::appRoot();
    }

    /**
     * Raiz do APP_URL sem porta de desenvolvimento em produção.
     */
    public static function appRoot(): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        $scheme = parse_url($appUrl, PHP_URL_SCHEME) ?: 'https';
        $host = parse_url($appUrl, PHP_URL_HOST) ?: '';

        if (self::isLocal($host)) {
            return $appUrl;
        }

        return self::buildUrl($scheme, $host, null);
    }

    private static function isLocal(string $host): bool
    {
        $isLocalEnv = in_array((string) config('app.env'), ['local', 'testing'], true);
        $isLocalHost = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', 'lvh.me'], true);

        return $isLocalEnv || $isLocalHost;
    }

    public static function onTenant(?User $user, string $path): string
    {
        return rtrim(self::baseUrlForUser($user), '/') . '/' . ltrim($path, '/');
    }

    public static function tenantRoute(?User $user, string $routeName, array $parameters = []): string
    {
        $relativePath = route($routeName, $parameters, false);

        return self::onTenant($user, $relativePath);
    }

    private static function normalizeDomain(string $domain): string
    {
        $domain = preg_replace('#^https?://#i', '', trim($domain)) ?? '';

        return rtrim($domain, '/');
    }

    private static function buildUrl(string $scheme, string $host, ?int $port = null): string
    {
        if ($port) {
            return sprintf('%s://%s:%d', $scheme, $host, $port);
        }

        return sprintf('%s://%s', $scheme, $host);
    }
}