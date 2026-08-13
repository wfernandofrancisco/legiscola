<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortalContactMessageController;
use App\Http\Controllers\Admin\NoticiaController;
use App\Http\Controllers\Admin\ProvaController;
use App\Http\Controllers\Admin\TenantAdminSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\CertificateTemplateController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CourseClassCrudController;
use App\Http\Controllers\Admin\ClassLessonController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\CourseClassController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ProfessorCredenciamentoController;
use App\Http\Controllers\Admin\SobreEscolaController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\StudentGeolocationController;
use App\Http\Controllers\Admin\SystemReportController;
use App\Http\Controllers\Admin\TeacherController;
use App\Livewire\Admin\ConstrutorProvas;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Módulo Admin — Cliente (Tenant Admin) gerencia sua empresa
| Middleware: auth + verified + tenant-access (auto-redireciona se super-admin)
| TenantScope filtra automaticamente por tenant_id do usuário
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified', 'accepted-privacy-term', 'tenant-access', 'tenant', 'has-tenant', 'user.type:tenant_admin'])
    ->group(function () {

        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('relatorios/sistema', [SystemReportController::class, 'index'])->name('relatorios.sistema');
        Route::get('relatorios/sistema/pdf', [SystemReportController::class, 'pdf'])->name('relatorios.sistema.pdf');

        // Profile do usuário
        Route::get('profile', [UserController::class, 'profileEdit'])->name('profile.edit');
        Route::put('profile', [UserController::class, 'profileUpdate'])->name('profile.update');
        Route::put('profile/change-password', [UserController::class, 'changePassword'])->name('profile.change-password');

        Route::get('settings', [TenantAdminSettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [TenantAdminSettingController::class, 'update'])->name('settings.update');

        // Exemplo de sub-grupos que você irá expandir:
        Route::resource('users', UserController::class);

        // Noticias do tenant
        Route::resource('noticias', NoticiaController::class);
        Route::delete('noticias/{noticia}/fotos/{foto}', [NoticiaController::class, 'destroyFoto'])
            ->name('noticias.fotos.destroy');

        Route::get('contatos-portal', [PortalContactMessageController::class, 'index'])->name('contatos-portal.index');
        Route::get('contatos-portal/{contato}', [PortalContactMessageController::class, 'show'])->name('contatos-portal.show');
        Route::post('contatos-portal/{contato}/responder', [PortalContactMessageController::class, 'reply'])->name('contatos-portal.reply');

        Route::resource('escola/cursos', CourseController::class)->parameters(['cursos' => 'course'])->except(['show']);
        Route::get('escola/cursos-busca', [CourseController::class, 'search'])->name('cursos.search');
        Route::resource('escola/professores', TeacherController::class)->parameters(['professores' => 'professore'])->except(['show']);
        Route::resource('escola/turmas', CourseClassCrudController::class)->parameters(['turmas' => 'turma']);
        Route::put('escola/turmas/{turma}/quizzes-janelas', [CourseClassCrudController::class, 'updateQuizWindows'])->name('turmas.quizzes-janelas.update');
        Route::patch('escola/turmas/{turma}/matriculas/{enrollment}/status', [CourseClassCrudController::class, 'updateEnrollmentStatus'])->name('turmas.matriculas.status');
        Route::patch('escola/turmas/{turma}/matriculas/concluir-inscritos', [CourseClassCrudController::class, 'markInscritosAsConcluido'])->name('turmas.matriculas.concluir-inscritos');
        Route::post('escola/turmas/{turma}/matriculas', [CourseClassCrudController::class, 'storeEnrollment'])->name('turmas.matriculas.store');
        Route::get('escola/turmas/{turma}/alunos-busca', [CourseClassCrudController::class, 'searchStudents'])->name('turmas.alunos.search');
        Route::get('escola/turmas/{turma}/ficha-presenca', [CourseClassCrudController::class, 'attendanceSheet'])->name('turmas.ficha-presenca');
        Route::post('escola/turmas/{turma}/ficha-presenca', [CourseClassCrudController::class, 'storeAttendanceSheet'])->name('turmas.ficha-presenca.store');
        Route::delete('escola/turmas/{turma}/ficha-presenca', [CourseClassCrudController::class, 'destroyAttendanceSheet'])->name('turmas.ficha-presenca.destroy');
        Route::get('escola/turmas/{turma}/ficha-presenca/imprimir', [CourseClassCrudController::class, 'printAttendanceSheet'])->name('turmas.ficha-presenca.print');
        Route::post('escola/turmas/{turma}/avisos', [CourseClassCrudController::class, 'storeAnnouncement'])->name('turmas.avisos.store');
        Route::resource('escola/aulas', ClassLessonController::class)->parameters(['aulas' => 'aula'])->except(['show']);
        Route::get('escola/aulas/turmas-busca', [ClassLessonController::class, 'searchCourseClasses'])->name('aulas.turmas.search');
        Route::get('escola/eventos/{evento}/inscritos-pdf', [EventController::class, 'printEnrollmentsPdf'])->name('eventos.inscritos-pdf');
        Route::get('escola/eventos/{evento}/triagem-pdf', [EventController::class, 'printEventTriagemPdf'])->name('eventos.triagem-pdf');
        Route::patch('escola/eventos/{evento}/inscricoes/{event_enrollment}', [EventController::class, 'updateEnrollmentPresente'])->name('eventos.inscricao.update');
        Route::post('escola/eventos/{evento}/inscricoes/todos-presentes', [EventController::class, 'markAllEnrollmentsPresente'])->name('eventos.inscricao.todos-presentes');
        Route::resource('escola/eventos', EventController::class)->parameters(['eventos' => 'evento'])->except(['show']);
        Route::get('escola/alunos/mapa', [StudentGeolocationController::class, 'index'])->name('alunos.mapa');
        Route::get('escola/alunos/mapa/marcadores', [StudentGeolocationController::class, 'markers'])->name('alunos.mapa.marcadores');
        Route::resource('escola/alunos', StudentController::class)->parameters(['alunos' => 'student'])->except(['show']);
        Route::get('escola/alunos-busca', [StudentController::class, 'search'])->name('alunos.search');
        Route::resource('escola/professores-credenciamentos', ProfessorCredenciamentoController::class)
            ->parameters(['professores-credenciamentos' => 'professoresCredenciamento'])
            ->except(['show']);
        Route::delete(
            'escola/professores-credenciamentos/{professoresCredenciamento}/anexos/{anexo}',
            [ProfessorCredenciamentoController::class, 'destroyAnexo']
        )->name('professores-credenciamentos.anexos.destroy');
        Route::resource('escola/sobre-escola', SobreEscolaController::class)
            ->parameters(['sobre-escola' => 'sobreEscola'])
            ->except(['show']);
        Route::match(['get', 'post', 'put', 'patch'], 'escola/templates-certificado-preview', [CertificateTemplateController::class, 'preview'])->name('templates-certificado.preview');
        Route::match(['get', 'post', 'put', 'patch'], 'escola/templates-certificado/preview', [CertificateTemplateController::class, 'preview'])->name('templates-certificado.preview.legacy');
        Route::resource('escola/templates-certificado', CertificateTemplateController::class)->parameters(['templates-certificado' => 'certificateTemplate'])->except(['show']);
        Route::post('escola/certificados/emitir', [CertificateController::class, 'issue'])->name('escola.certificados.issue');
        Route::post('escola/certificados/{certificate}/revogar', [CertificateController::class, 'revoke'])->name('escola.certificados.revoke');
        Route::post('escola/turmas/{courseClass}/complete', [CourseClassController::class, 'complete'])->name('escola.turmas.complete');
        Route::resource('quizzes', QuizController::class);
        Route::patch('quizzes/{quiz}/turmas/{courseClass}/status', [QuizController::class, 'toggleClassStatus'])->name('quizzes.turmas.status');
        Route::get('quizzes/{quiz}/imprimir', [QuizController::class, 'print'])->name('quizzes.print');
        Route::redirect('escola/quizzes/gestao', 'quizzes', 301);
        Route::get('provas/construtor', ConstrutorProvas::class)->name('provas.construtor');
        Route::get('provas/{prova}/imprimir', [ProvaController::class, 'imprimir'])->name('provas.imprimir');
    });
