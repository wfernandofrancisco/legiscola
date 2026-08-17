<?php

use App\Http\Controllers\Portal\PortalAcessoController;
use App\Http\Controllers\Portal\PortalCertificadoValidacaoController;
use App\Http\Controllers\Portal\PortalPrivacidadeController;
use App\Http\Controllers\Portal\PortalAgendaController;
use App\Http\Controllers\Portal\PortalContatoController;
use App\Http\Controllers\Portal\PortalCursoController;
use App\Http\Controllers\Portal\PortalEventoController;
use App\Http\Controllers\Portal\PortalEventoPalestranteCertificadoController;
use App\Http\Controllers\Portal\PortalNoticiaController;
use App\Http\Controllers\Portal\PortalProfessorController;
use App\Http\Controllers\Portal\PortalSobreController;
use App\Http\Controllers\Portal\PortalTutorialController;
use App\Http\Middleware\SetTenantContext;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal público do tenant (requer TenantContext pelo subdomínio)
|--------------------------------------------------------------------------
*/

Route::middleware([SetTenantContext::class, 'tenant.portal'])
    ->name('portal.')
    ->group(function (): void {

        Route::get('/certificados/validar', [PortalCertificadoValidacaoController::class, 'create'])
            ->name('certificados.validar');
        Route::post('/certificados/validar', [PortalCertificadoValidacaoController::class, 'store'])
            ->name('certificados.validar.consultar');

        Route::get('/noticias', [PortalNoticiaController::class, 'index'])->name('noticias.index');
        Route::get('/noticias/{slug}', [PortalNoticiaController::class, 'show'])->name('noticias.show');

        Route::get('/eventos', [PortalEventoController::class, 'index'])->name('eventos.index');
        Route::get('/eventos/{evento}', [PortalEventoController::class, 'show'])->name('eventos.show')->whereNumber('evento');
        Route::get('/eventos/{evento}/certificado-palestrante', [PortalEventoPalestranteCertificadoController::class, 'create'])
            ->name('eventos.certificado-palestrante')
            ->whereNumber('evento');
        Route::post('/eventos/{evento}/certificado-palestrante', [PortalEventoPalestranteCertificadoController::class, 'store'])
            ->name('eventos.certificado-palestrante.store')
            ->whereNumber('evento');
        Route::post('/eventos/{evento}/inscrever', [PortalEventoController::class, 'enroll'])
            ->middleware(['auth', 'verified', 'accepted-privacy-term', 'role:tenant_user'])
            ->name('eventos.inscrever')
            ->whereNumber('evento');

        Route::get('/agenda', [PortalAgendaController::class, 'index'])->name('agenda.index');

        Route::get('/cursos', [PortalCursoController::class, 'index'])->name('cursos.index');
        Route::get('/cursos/historico', [PortalCursoController::class, 'historico'])->name('cursos.historico');
        Route::get('/cursos/{curso}', [PortalCursoController::class, 'show'])->name('cursos.show')->whereNumber('curso');
        Route::post('/cursos/{curso}/turmas/{turma}/inscrever', [PortalCursoController::class, 'enroll'])
            ->middleware(['auth', 'verified', 'accepted-privacy-term', 'role:tenant_user'])
            ->name('cursos.turmas.inscrever')
            ->whereNumber(['curso', 'turma']);

        Route::get('/sobre', [PortalSobreController::class, 'show'])->name('sobre');

        Route::get('/privacidade', [PortalPrivacidadeController::class, 'show'])->name('privacidade');

        Route::get('/professores', [PortalProfessorController::class, 'index'])->name('professores.index');

        Route::get('/tutorial', [PortalTutorialController::class, 'index'])->name('tutorial');

        Route::get('/contato', [PortalContatoController::class, 'index'])->name('contato');
        Route::post('/contato', [PortalContatoController::class, 'store'])->name('contato.store');

        Route::get('/acesso/entrar', [PortalAcessoController::class, 'login'])
            ->middleware('guest')->name('acesso.login');
        Route::get('/acesso/cadastro', [PortalAcessoController::class, 'register'])
            ->middleware('guest')->name('acesso.register');
        Route::get('/acesso/esqueci-senha', [PortalAcessoController::class, 'forgot'])
            ->middleware('guest')->name('acesso.forgot');

        Route::get('/acesso/docente/entrar', [PortalAcessoController::class, 'docenteLogin'])
            ->middleware('guest')->name('acesso.docente.login');
        Route::get('/acesso/docente/esqueci-senha', [PortalAcessoController::class, 'docenteForgot'])
            ->middleware('guest')->name('acesso.docente.forgot');
    });
