<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Cliente — usuários das empresas clientes do SaaS
|
| NOTA: Este arquivo contém rotas comentadas para controllers ainda não criados.
| Descomente quando os controllers forem implementados.
|
| Acesso via subdomínio: cliente1.sistema.com.br
| O middleware 'tenant' detecta o subdomínio e seta o TenantContext
| automaticamente, isolando os dados de cada empresa.
|
| Hierarquia de roles dentro de um tenant:
|   tenant_admin   → dono/coordenador da empresa cliente (vê tudo, pode excluir)
|   tenant_manager → gerente: gerencia cadastros mas NÃO pode excluir
|   tenant_user    → acesso limitado (ex: só registros de uso)
|--------------------------------------------------------------------------
*/

/*
// -----------------------------------------------------------------------
// Submódulo Admin — gestão completa da empresa
// Acesso: cliente_admin | coordenador
// URL: cliente1.sistema.com.br/admin
// -----------------------------------------------------------------------
Route::prefix('admin')
    ->name('cliente.admin.')
    ->middleware(['auth', 'verified', 'tenant', 'has-tenant', 'role:tenant_admin|tenant_manager'])
    ->group(function () {

        Route::get('/', [\App\Http\Controllers\Cliente\Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Somente cliente_admin pode excluir (aplique gate 'delete' nos controllers)
        Route::resource('users', \App\Http\Controllers\Cliente\Admin\UserController::class);
        Route::resource('budgets', \App\Http\Controllers\Cliente\Admin\BudgetController::class);
    });

// -----------------------------------------------------------------------
// Submódulo Responsável — painel do dono/responsável da empresa
// Acesso: cliente_admin apenas
// URL: cliente1.sistema.com.br/responsavel
// -----------------------------------------------------------------------
Route::prefix('responsavel')
    ->name('cliente.responsavel.')
    ->middleware(['auth', 'verified', 'tenant', 'has-tenant', 'role:tenant_admin'])
    ->group(function () {

        Route::get('/', [\App\Http\Controllers\Cliente\Responsavel\DashboardController::class, 'index'])
            ->name('dashboard');
    });
*/

// -----------------------------------------------------------------------
// Submódulo Usuários (atendentes/operadores)
// Acesso: cliente_admin | coordenador | atendente
// URL: cliente1.sistema.com.br/app
// -----------------------------------------------------------------------
/*
Route::prefix('app')
    ->name('cliente.app.')
    ->middleware(['auth', 'verified', 'tenant', 'role:tenant_admin|tenant_manager|tenant_user'])
    ->group(function () {

        Route::get('/', [\App\Http\Controllers\Cliente\App\DashboardController::class, 'index'])
            ->name('dashboard');
    });
*/
