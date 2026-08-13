<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;

/**
 * Cloudflare Turnstile — validação server-side.
 * Sem chaves no .env, o sistema ignora a verificação (útil em local).
 */
final class Turnstile
{
    public static function isConfigured(): bool
    {
        $secret = config('services.turnstile.secret');

        return is_string($secret) && $secret !== '';
    }

    public static function siteKey(): ?string
    {
        $key = config('services.turnstile.key');

        return is_string($key) && $key !== '' ? $key : null;
    }

    public static function verify(?string $token, ?string $remoteIp): bool
    {
        if (! self::isConfigured()) {
            return true;
        }

        if (! is_string($token) || $token === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(10)
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                'secret' => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $remoteIp ?? '',
            ]);

        if (! $response->successful()) {
            return false;
        }

        return (bool) ($response->json('success') ?? false);
    }
}
