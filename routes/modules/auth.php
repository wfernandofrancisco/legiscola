<?php

use App\Http\Controllers\Auth\CentralAuthController;
use App\Http\Controllers\Auth\GlobalPrivacyTermAcceptanceController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\RegisterMoradorController;
use App\Http\Controllers\Auth\RegisterResponsavelController;
use App\Http\Controllers\Auth\TenantAuthController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Autenticação — Separada por tipo de usuário
|--------------------------------------------------------------------------
*/

// =====================================================================
// CENTRAL LOGIN — /login/central — Super Admin (Dono do Sistema)
// =====================================================================
Route::prefix('login')
    ->name('central.')
    ->middleware('guest')
    ->group(function () {
        Route::get('central', [CentralAuthController::class, 'showLoginForm'])->name('login');
        Route::post('central', [CentralAuthController::class, 'login'])->name('login.store');
    });

// Central Logout
Route::post('logout/central', [CentralAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('central.logout');

// Aceite do termo LGPD global (após publicação de nova versão na Central)
Route::middleware(['auth'])->group(function (): void {
    Route::get('obrigacao/termo-privacidade', [GlobalPrivacyTermAcceptanceController::class, 'show'])
        ->name('privacy-term.show');
    Route::post('obrigacao/termo-privacidade', [GlobalPrivacyTermAcceptanceController::class, 'store'])
        ->middleware('throttle:12,1')
        ->name('privacy-term.accept');
});

// Tenant Logout
Route::post('logout', [TenantAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// =====================================================================
// TENANT LOGIN — /tenant/login — Admin/Responsible/User (Clientes do SaaS)
// =====================================================================
Route::prefix('tenant')
    ->middleware(['guest', SetTenantContext::class])
    ->group(function () {
        // Register routes
        Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
        Route::post('register', [RegisteredUserController::class, 'store'])->name('tenant.register.store');

        Route::get('register/morador', [RegisterMoradorController::class, 'create'])->name('register.morador');
        Route::post('register/morador', [RegisterMoradorController::class, 'store'])->name('register.morador.store');
        Route::get('register/responsavel', [RegisterResponsavelController::class, 'create'])->name('register.responsavel');
        Route::post('register/responsavel', [RegisterResponsavelController::class, 'store'])->name('register.responsavel.store');

        // Login routes
        Route::get('login', [TenantAuthController::class, 'showLoginForm'])->name('tenant.login');
        Route::post('login', [TenantAuthController::class, 'login'])->name('tenant.login.store');

        // Password reset routes
        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.update');
    });

// =====================================================================
// TENANT AUTHENTICATED ROUTES — /tenant/* — Users logados mas não verificados
// =====================================================================
Route::prefix('tenant')
    ->middleware(['auth', SetTenantContext::class])
    ->group(function () {
        // Email verification routes
        Route::get('verify-email', [EmailVerificationPromptController::class, '__invoke'])->name('verification.notice');
        Route::get('verify-email/{id}/{hash}', [VerifyEmailController::class, '__invoke'])->middleware(['signed', 'throttle:6,1'])->name('verification.verify');
        Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])->middleware('throttle:6,1')->name('verification.send');
    });

// Tenant Logout
Route::post('logout', [TenantAuthController::class, 'logout'])
    ->middleware('auth')
    ->name('tenant.logout');

// Redirecionar raiz para tenant login
Route::redirect('/auth', '/tenant/login');
Route::redirect('/login', '/tenant/login');
