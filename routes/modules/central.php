<?php

use App\Http\Controllers\Central\CagedImportController;
use App\Http\Controllers\Central\CnaeController;
use App\Http\Controllers\Central\CnaeSinonimoController;
use App\Http\Controllers\Central\CnpjProcessController;
use App\Http\Controllers\Central\ComexImportController;
use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\EstbanImportController;
use App\Http\Controllers\Central\GlobalPrivacyTermController;
use App\Http\Controllers\Central\NaturezaJuridicaController;
use App\Http\Controllers\Central\PermissionController;
use App\Http\Controllers\Central\RoleController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\TenantUserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Central — Super Admin
|--------------------------------------------------------------------------
*/

Route::prefix('central')
    ->name('central.')
    ->middleware(['auth', 'verified', 'accepted-privacy-term', 'central-access', 'user.type:super_admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('termo-lgpd', [GlobalPrivacyTermController::class, 'edit'])->name('global-privacy-term.edit');
        Route::put('termo-lgpd', [GlobalPrivacyTermController::class, 'update'])->name('global-privacy-term.update');

        Route::resource('roles', RoleController::class);
        Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');

        Route::resource('permissions', PermissionController::class);

        Route::resource('tenants', TenantController::class);

        Route::get('tenants/{tenant}/users', [TenantUserController::class, 'index'])->name('tenants.users.index');
        Route::put('tenants/{tenant}/users/{user}', [TenantUserController::class, 'update'])->name('tenants.users.update');

        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/deactivate', [TenantController::class, 'deactivate'])->name('tenants.deactivate');
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');

        Route::post('tenants/{tenant}/activate-cadastro', [TenantController::class, 'activateCadastro'])->name('tenants.activate-cadastro');
        Route::post('tenants/{tenant}/deactivate-cadastro', [TenantController::class, 'deactivateCadastro'])->name('tenants.deactivate-cadastro');

        Route::post('tenants/{tenant}/link-user', [TenantController::class, 'linkUser'])->name('tenants.link-user');
        Route::post('tenants/{tenant}/invite-user', [TenantController::class, 'inviteUser'])->name('tenants.invite-user');
    });
