<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes - Preparadas para consumo futuro via mobile (Ionic/Capacitor)
|--------------------------------------------------------------------------
|
| Todas as rotas da API retornam JSON e são protegidas via Laravel Sanctum.
| Para autenticar: POST /api/auth/login → recebe token Bearer.
| Inclua o header: Authorization: Bearer {token}
|
*/

// Rotas públicas (sem autenticação)
Route::prefix('v1')->group(function () {

    // Autenticação
    Route::prefix('auth')->group(function () {
        Route::post('login', [\App\Http\Controllers\Api\V1\AuthController::class, 'login']);
        Route::post('register', [\App\Http\Controllers\Api\V1\AuthController::class, 'register']);
        Route::post('forgot-password', [\App\Http\Controllers\Api\V1\AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [\App\Http\Controllers\Api\V1\AuthController::class, 'resetPassword']);
    });

    // Rotas protegidas por Sanctum (+ contexto de tenant para escopo global)
    Route::middleware(['auth:sanctum', 'tenant.api-context'])->group(function () {

        // Usuário autenticado
        Route::get('me', function (Request $request) {
            return response()->json($request->user()->load('roles'));
        });
        Route::post('auth/logout', [\App\Http\Controllers\Api\V1\AuthController::class, 'logout']);

        // Usuários (somente admin/funcionario)
        Route::middleware('role:super_admin|funcionario')->group(function () {
            Route::apiResource('users', \App\Http\Controllers\Api\V1\UserController::class);
            Route::post('users/{user}/activate',     [\App\Http\Controllers\Api\V1\UserController::class, 'activate']);
            Route::post('users/{user}/deactivate',   [\App\Http\Controllers\Api\V1\UserController::class, 'deactivate']);
            Route::patch('users/{user}/type',        [\App\Http\Controllers\Api\V1\UserController::class, 'changeType']);
        });

        // Empresas
        Route::apiResource('tenants', \App\Http\Controllers\Api\V1\TenantController::class);
        Route::post('tenants/{tenant}/users', [\App\Http\Controllers\Api\V1\TenantController::class, 'linkUser']);
        Route::delete('tenants/{tenant}/users/{user}', [\App\Http\Controllers\Api\V1\TenantController::class, 'unlinkUser']);

        // Orçamentos
        Route::apiResource('budgets', \App\Http\Controllers\Api\V1\BudgetController::class);
        Route::post('budgets/{budget}/approve', [\App\Http\Controllers\Api\V1\BudgetController::class, 'approve']);
        Route::post('budgets/{budget}/reject',  [\App\Http\Controllers\Api\V1\BudgetController::class, 'reject']);
    });
});
