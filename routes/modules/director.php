<?php

use App\Http\Controllers\Director\AgendaController;
use App\Http\Controllers\Director\CatalogoAulaController;
use App\Http\Controllers\Director\CatalogoController;
use App\Http\Controllers\Director\ClienteController;
use App\Http\Controllers\Director\DashboardController;
use App\Http\Controllers\Director\FinanceiroController;
use App\Http\Controllers\Director\LicencaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Diretor Regional — /diretor
|--------------------------------------------------------------------------
|
| Roda no domínio raiz, igual à Central: sem o middleware `tenant`, porque a
| abrangência do diretor é multi-tenant (por UF) e não caberia no TenantContext.
| O isolamento vem de DirectorContext + DirectorInsightsService.
|
*/

Route::prefix('diretor')
    ->name('diretor.')
    ->middleware(['auth', 'verified', 'accepted-privacy-term', 'director-access', 'user.type:tenant_director'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('camaras', [ClienteController::class, 'index'])->name('clientes.index');
        Route::get('camaras/{cliente}', [ClienteController::class, 'show'])->name('clientes.show');

        Route::resource('catalogo', CatalogoController::class)
            ->parameters(['catalogo' => 'catalogo']);

        Route::post('catalogo/{catalogo}/aulas', [CatalogoAulaController::class, 'store'])->name('catalogo.aulas.store');
        Route::put('catalogo/{catalogo}/aulas/{aula}', [CatalogoAulaController::class, 'update'])->name('catalogo.aulas.update');
        Route::delete('catalogo/{catalogo}/aulas/{aula}', [CatalogoAulaController::class, 'destroy'])->name('catalogo.aulas.destroy');
        Route::post('catalogo/{catalogo}/aulas/ordenar', [CatalogoAulaController::class, 'reorder'])->name('catalogo.aulas.reorder');

        Route::resource('licencas', LicencaController::class)
            ->parameters(['licencas' => 'licenca'])
            ->except(['show']);

        Route::get('agenda', [AgendaController::class, 'index'])->name('agenda.index');
        Route::get('agenda/events', [AgendaController::class, 'events'])->name('agenda.events');
        Route::get('agenda/export.ics', [AgendaController::class, 'exportIcs'])->name('agenda.export');

        Route::get('financeiro', [FinanceiroController::class, 'index'])->name('financeiro.index');
    });
