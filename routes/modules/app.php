<?php

use App\Http\Controllers\Aluno\AulaController;
use App\Http\Controllers\Aluno\CadastroController;
use App\Http\Controllers\Aluno\CertificadoController;
use App\Http\Controllers\Aluno\DashboardController;
use App\Http\Controllers\Aluno\EventoController;
use App\Http\Controllers\Aluno\PesquisaSatisfacaoController;
use App\Http\Controllers\Aluno\SenhaController;
use App\Http\Controllers\Aluno\TurmaController;
use App\Http\Controllers\App\ProfileController;
use App\Http\Controllers\App\QuizController;
use App\Livewire\App\PortalInscricaoAluno;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Aluno — Usuários comuns do tenant
| Middleware: auth + verified + tenant-access (auto-redireciona se super-admin)
| Permite: tenant_user
|--------------------------------------------------------------------------
*/

Route::prefix('aluno')
    ->name('app.')
    ->middleware(['auth', 'verified', 'accepted-privacy-term', 'tenant-access', 'tenant', 'has-tenant', 'user.type:tenant_user'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('dados', [CadastroController::class, 'edit'])->name('cadastro.edit');
        Route::put('dados', [CadastroController::class, 'update'])->name('cadastro.update');
        Route::get('senha', [SenhaController::class, 'edit'])->name('senha.edit');
        Route::put('senha', [SenhaController::class, 'update'])->name('senha.update');
        Route::get('turmas', [TurmaController::class, 'index'])->name('turmas.index');
        Route::post('turmas/{courseClass}/inscrever', [TurmaController::class, 'enroll'])->name('turmas.inscrever');
        Route::get('turmas/{courseClass}', [TurmaController::class, 'show'])->name('turmas.show');
        Route::get('aulas/{classLesson}', [AulaController::class, 'show'])->name('aulas.show')->whereNumber('classLesson');
        Route::get('aulas/{classLesson}/material', [AulaController::class, 'downloadMaterial'])->name('aulas.material')->whereNumber('classLesson');
        Route::post('aulas/{classLesson}/presenca', [AulaController::class, 'storePresence'])->name('aulas.presenca')->whereNumber('classLesson');
        Route::get('certificados', [CertificadoController::class, 'index'])->name('certificados.index');
        Route::get('certificados/{certificate}/baixar', [CertificadoController::class, 'download'])->name('certificados.baixar');
        Route::get('eventos', [EventoController::class, 'index'])->name('eventos.index');
        Route::get('eventos/{evento}', [EventoController::class, 'show'])->name('eventos.show');
        Route::post('eventos/{evento}/presenca', [EventoController::class, 'storePresence'])->name('eventos.presenca');
        Route::get('pesquisas-satisfacao', [PesquisaSatisfacaoController::class, 'index'])->name('pesquisas-satisfacao.index');
        Route::get('pesquisas-satisfacao/turma/{turma}', [PesquisaSatisfacaoController::class, 'show'])->name('pesquisas-satisfacao.show');
        Route::post('pesquisas-satisfacao/turma/{turma}', [PesquisaSatisfacaoController::class, 'store'])->name('pesquisas-satisfacao.store');

        Route::get('perfil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('perfil', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('perfil/senha', [ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::get('escola/dashboard', fn () => redirect()->route('app.dashboard'))->name('escola.dashboard');
        Route::get('quizzes', [QuizController::class, 'index'])->name('quizzes.index');
        Route::get('quizzes/{quiz}', [QuizController::class, 'show'])->name('quizzes.show');
        Route::post('quizzes/{quiz}/enviar', [QuizController::class, 'submit'])->name('quizzes.submit');
        Route::get('inscricoes', PortalInscricaoAluno::class)->name('inscricoes.index');
    });
