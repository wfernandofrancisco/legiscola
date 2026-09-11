<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\PwaArea;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Manifest e arquivos estáticos do PWA (aluno, diretor, professor, portal).
 */
class PwaManifestController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $area = PwaArea::normalize($request->query('area'));
        $config = PwaArea::config($area);

        $tenant = TenantContext::getTenantId()
            ? Tenant::query()->find(TenantContext::getTenantId())
            : null;

        $label = $tenant?->display_name ?? $tenant?->name ?? config('app.name', 'Legiscola');

        // Diretor costuma estar no domínio principal, sem câmara no host.
        if ($area === PwaArea::DIRETOR && ! $tenant) {
            $label = config('app.name', 'Legiscola');
        }

        $short = $config['short_name'];
        if ($area !== PwaArea::DIRETOR && mb_strlen($label) <= 12) {
            $short = $label;
        }

        return response()->json([
            'id' => '/?pwa='.$area,
            'name' => $label.' — '.$config['name_suffix'],
            'short_name' => $short,
            'description' => $config['description'],
            'start_url' => $config['start_url'],
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'any',
            'background_color' => $config['background_color'],
            'theme_color' => $config['theme_color'],
            'lang' => 'pt-BR',
            'dir' => 'ltr',
            'icons' => [
                [
                    'src' => asset('img/pwa-icon-192.png'),
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('img/pwa-icon-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => asset('img/pwa-maskable-512.png'),
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/manifest+json; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function serviceWorker(): Response
    {
        $path = public_path('sw.js');
        abort_unless(File::isFile($path), 404);

        return response(File::get($path), 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=0, must-revalidate',
            'Service-Worker-Allowed' => '/',
        ]);
    }

    public function offline(): Response
    {
        $path = public_path('offline.html');
        abort_unless(File::isFile($path), 404);

        return response(File::get($path), 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
