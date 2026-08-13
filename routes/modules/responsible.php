<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Redireciona /professor (gestor legado) para o painel único /docente.
| Mantém nomes responsible.* para bookmarks e e-mails antigos.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'accepted-privacy-term', 'tenant-access', 'tenant', 'has-tenant'])->group(function (): void {

    Route::get('professor', fn () => redirect()->route('professor.dashboard', [], 301))->name('responsible.dashboard');

    Route::redirect('professor/perfil', '/docente/perfil', 301)->name('responsible.perfil.edit');
    Route::redirect('professor/quizzes', '/docente/quizzes', 301)->name('responsible.quizzes.index');
    Route::redirect('professor/chamada', '/docente/turmas', 301)->name('responsible.chamada.index');
    Route::redirect('professor/escola/quizzes/gestao', '/docente/quizzes', 301);

    Route::get('professor/quizzes/{quiz}', function (\App\Models\Quiz $quiz) {
        return redirect()->route('professor.quizzes.show', $quiz, 301);
    })
        ->whereNumber('quiz')
        ->name('responsible.quizzes.show');

    Route::get('professor/quizzes/{quiz}/imprimir', function (\App\Models\Quiz $quiz) {
        return redirect()->route('professor.quizzes.print', $quiz, 301);
    })
        ->whereNumber('quiz')
        ->name('responsible.quizzes.print');
});
