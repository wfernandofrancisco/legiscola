<?php

use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Portal\PortalHomeController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Público — Marketing (apex) + portal do tenant (subdomínio)
|--------------------------------------------------------------------------
*/

Route::middleware([SetTenantContext::class])->group(function (): void {
    Route::get('/', [PortalHomeController::class, 'index'])->name('home');
    Route::post('/contact', [HomeController::class, 'contact'])->name('contact');
    Route::get('/certificados/validar/{hash}/download', [CertificateController::class, 'downloadByHash'])->name('certificados.download');
    Route::get('/certificados/validar/{hash}', [CertificateController::class, 'validateHash'])->name('certificados.validar.por-hash');

    // PWA da área do aluno (instalável no celular; nome usa a câmara do host).
    Route::get('/manifest.webmanifest', PwaManifestController::class)->name('pwa.manifest');
    Route::get('/sw.js', [PwaManifestController::class, 'serviceWorker'])->name('pwa.service-worker');
    Route::get('/offline.html', [PwaManifestController::class, 'offline'])->name('pwa.offline');
});

// Redirect: /login → /login/tenant (para clients que tentam acessar diretamente)
Route::redirect('/auth', '/login');
Route::redirect('/dashboard', '/login');
