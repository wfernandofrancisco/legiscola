<?php

use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Portal\PortalHomeController;
use App\Http\Controllers\Public\HomeController;
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
});

// Redirect: /login → /login/tenant (para clients que tentam acessar diretamente)
Route::redirect('/auth', '/login');
Route::redirect('/dashboard', '/login');
