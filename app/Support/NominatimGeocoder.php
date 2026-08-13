<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Geocodificação via Nominatim (OSM). Respeitar política de uso (~1 req/s, User-Agent identificável).
 *
 * @see https://operations.osmfoundation.org/policies/nominatim/
 */
final class NominatimGeocoder
{
    /** @var array<string, array{latitude: float, longitude: float}|null> */
    private static array $memoryCache = [];

    private static ?float $lastRequestAt = null;

    /**
     * @param  array{cep?: string|null, logradouro?: string|null, numero?: string|null, bairro?: string|null, cidade?: string|null, uf?: string|null}  $parts
     */
    public static function hasSufficientAddress(array $parts): bool
    {
        $cidade = trim((string) ($parts['cidade'] ?? ''));
        $uf = trim((string) ($parts['uf'] ?? ''));
        $cep = preg_replace('/\D/', '', (string) ($parts['cep'] ?? '')) ?? '';

        if ($cidade !== '' && strlen($uf) === 2) {
            return true;
        }

        return strlen($cep) >= 8;
    }

    /**
     * @param  array{cep?: string|null, logradouro?: string|null, numero?: string|null, bairro?: string|null, cidade?: string|null, uf?: string|null}  $parts
     * @return array{latitude: float, longitude: float}|null
     */
    public static function geocode(array $parts): ?array
    {
        if (! self::hasSufficientAddress($parts)) {
            return null;
        }

        $key = self::cacheKey($parts);
        if (array_key_exists($key, self::$memoryCache)) {
            return self::$memoryCache[$key];
        }

        self::throttle();

        $baseUrl = rtrim((string) config('services.nominatim.url', 'https://nominatim.openstreetmap.org'), '/');
        $url = $baseUrl.'/search';

        $logradouro = trim((string) ($parts['logradouro'] ?? ''));
        $numero = trim((string) ($parts['numero'] ?? ''));
        $street = trim($logradouro.' '.$numero);
        $postalcode = preg_replace('/\D/', '', (string) ($parts['cep'] ?? '')) ?? '';

        $query = [
            'format' => 'json',
            'limit' => 1,
            'addressdetails' => 0,
            'countrycodes' => 'br',
        ];

        if ($street !== '') {
            $query['street'] = $street;
        }
        $cidade = trim((string) ($parts['cidade'] ?? ''));
        if ($cidade !== '') {
            $query['city'] = $cidade;
        }
        $uf = strtoupper(trim((string) ($parts['uf'] ?? '')));
        if (strlen($uf) === 2) {
            $query['state'] = $uf;
        }
        if (strlen($postalcode) >= 8) {
            $query['postalcode'] = $postalcode;
        }

        try {
            $response = Http::timeout(15)
                ->withHeaders(self::headers())
                ->get($url, $query);
        } catch (\Throwable $e) {
            Log::warning('nominatim.request_failed', ['message' => $e->getMessage()]);

            return self::$memoryCache[$key] = null;
        }

        if (! $response->successful()) {
            Log::warning('nominatim.http_error', ['status' => $response->status()]);

            return self::$memoryCache[$key] = null;
        }

        $rows = $response->json();
        if (! is_array($rows) || $rows === [] || ! isset($rows[0]['lat'], $rows[0]['lon'])) {
            return self::$memoryCache[$key] = null;
        }

        return self::$memoryCache[$key] = [
            'latitude' => (float) $rows[0]['lat'],
            'longitude' => (float) $rows[0]['lon'],
        ];
    }

    /**
     * @param  array{cep?: string|null, logradouro?: string|null, numero?: string|null, bairro?: string|null, cidade?: string|null, uf?: string|null}  $parts
     */
    private static function cacheKey(array $parts): string
    {
        $cep = preg_replace('/\D/', '', (string) ($parts['cep'] ?? '')) ?? '';

        return strtolower(implode('|', [
            $cep,
            trim((string) ($parts['logradouro'] ?? '')),
            trim((string) ($parts['numero'] ?? '')),
            trim((string) ($parts['bairro'] ?? '')),
            trim((string) ($parts['cidade'] ?? '')),
            strtoupper(trim((string) ($parts['uf'] ?? ''))),
        ]));
    }

    /**
     * @return array<string, string>
     */
    private static function headers(): array
    {
        $ua = (string) config('services.nominatim.user_agent', 'Legiscola/1.0 (geocoding)');
        $email = (string) config('services.nominatim.email', '');
        if ($email !== '' && ! str_contains($ua, '@')) {
            $ua .= ' ('.$email.')';
        }

        return [
            'User-Agent' => $ua,
            'Accept-Language' => 'pt-BR,pt;q=0.9',
        ];
    }

    private static function throttle(): void
    {
        $minMs = max(0, (int) config('services.nominatim.min_request_interval_ms', 1100));
        if ($minMs <= 0) {
            self::$lastRequestAt = microtime(true);

            return;
        }

        if (self::$lastRequestAt !== null) {
            $elapsed = (microtime(true) - self::$lastRequestAt) * 1000;
            if ($elapsed < $minMs) {
                usleep((int) (($minMs - $elapsed) * 1000));
            }
        }

        self::$lastRequestAt = microtime(true);
    }
}
