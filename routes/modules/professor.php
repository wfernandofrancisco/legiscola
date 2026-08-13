<?php

use App\Http\Controllers\Professor\ClassLessonController;
use App\Http\Controllers\Professor\CourseClassAttendanceController;
use App\Http\Controllers\Professor\CourseClassAnnouncementController;
use App\Http\Controllers\Professor\DashboardController;
use App\Http\Controllers\Professor\PasswordController;
use App\Http\Controllers\Professor\ProfileController;
use App\Http\Controllers\Professor\QuizController;
use App\Http\Controllers\Professor\TurmaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Painel pedagógico do tenant — prefix /docente, nomes de rota professor.*.
| Acesso: docente (tenant_professor / cadastro Teacher) OU gestor (tenant_manager).
| URLs antigas /professor são redirecionadas para cá via routes/modules/responsible.php.
|--------------------------------------------------------------------------
*/

Route::prefix('docente')
    ->name('professor.')
    ->middleware(['auth', 'verified', 'accepted-privacy-term', 'tenant-access', 'tenant', 'has-tenant', 'docente-portal', 'user.type:tenant_responsible|tenant_manager'])
    ->group(function (): void {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('perfil', [ProfileController::class, 'edit'])->name('perfil.edit');
        Route::put('perfil', [ProfileController::class, 'update'])->name('perfil.update');
        Route::get('senha', [PasswordController::class, 'edit'])->name('senha.edit');
        Route::put('senha', [PasswordController::class, 'update'])->name('senha.update');

        Route::get('turmas', [TurmaController::class, 'index'])->name('turmas.index');
        Route::get('turmas/{turma}', [TurmaController::class, 'show'])->name('turmas.show');

        Route::get('turmas/{turma}/ficha-presenca', [CourseClassAttendanceController::class, 'show'])->name('turmas.ficha-presenca');
        Route::post('turmas/{turma}/ficha-presenca', [CourseClassAttendanceController::class, 'store'])->name('turmas.ficha-presenca.store');
        Route::post('turmas/{turma}/ficha-presenca/aula-rapida', [CourseClassAttendanceController::class, 'quickStoreLesson'])
            ->name('turmas.ficha-presenca.aula-rapida.store');
        Route::delete('turmas/{turma}/ficha-presenca', [CourseClassAttendanceController::class, 'destroy'])->name('turmas.ficha-presenca.destroy');
        Route::get('turmas/{turma}/ficha-presenca/imprimir', [CourseClassAttendanceController::class, 'print'])->name('turmas.ficha-presenca.print');
        Route::post('turmas/{turma}/avisos', [CourseClassAnnouncementController::class, 'store'])->name('turmas.avisos.store');

        Route::resource('aulas', ClassLessonController::class)->parameters(['aulas' => 'aula'])->except(['show']);
        Route::get('aulas/turmas-busca', [ClassLessonController::class, 'searchCourseClasses'])->name('aulas.turmas.search');

        Route::patch('quizzes/{quiz}/turmas/{courseClass}/status', [QuizController::class, 'toggleClassStatus'])->name('quizzes.turmas.status');
        Route::get('quizzes/{quiz}/imprimir', [QuizController::class, 'print'])->name('quizzes.print');
        Route::resource('quizzes', QuizController::class)->except(['destroy']);
    });
