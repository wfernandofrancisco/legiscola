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
        $isLocalEnv = in_array((string) config('app.env'), ['local', 'testing'], true);
        $isLocalHost = in_array(strtolower($host), ['localhost', '127.0.0.1', '::1', 'lvh.me'], true);

        // Em ambiente local, sempre prioriza slug + APP_DOMAIN para evitar links em dominio de producao do tenant.
        if ($tenant && filled($tenant->slug) && filled($appDomain) && ($isLocalEnv || $isLocalHost)) {
            $localPort = $port ?: 8000;

            return self::buildUrl($scheme, $tenant->slug . '.' . $appDomain, $localPort);
        }

        if ($tenant && filled($tenant->domain)) {
            return self::buildUrl($scheme, self::normalizeDomain((string) $tenant->domain), $port);
        }

        if ($tenant && filled($tenant->slug) && filled($appDomain)) {
            return self::buildUrl($scheme, $tenant->slug . '.' . $appDomain, $port);
        }

        return rtrim($appUrl, '/');
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