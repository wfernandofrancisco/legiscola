<?php

namespace App\Providers;

use App\Contracts\Repositories\BudgetRepositoryInterface;
use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Repositories\CertificateTemplateRepositoryInterface;
use App\Contracts\Repositories\CentralProcessRunRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\CourseClassRepositoryInterface;
use App\Contracts\Repositories\CurriculumRepositoryInterface;
use App\Contracts\Repositories\CnaeRepositoryInterface;
use App\Contracts\Repositories\CnaeSinonimoRepositoryInterface;
use App\Contracts\Repositories\EmpresaOverrideRepositoryInterface;
use App\Contracts\Repositories\EmpresaRelacaoArquivoRepositoryInterface;
use App\Contracts\Repositories\EmpresaRelacaoComentarioRepositoryInterface;
use App\Contracts\Repositories\EmpresaRelacaoRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\ExamTemplateRepositoryInterface;
use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Repositories\NaturezaJuridicaRepositoryInterface;
use App\Contracts\Repositories\NoticiaRepositoryInterface;
use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\TenantLookupRepositoryInterface;
use App\Contracts\Repositories\TenantRepositoryInterface;
use App\Contracts\Repositories\QuizRepositoryInterface;
use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Contracts\Repositories\ProfessorCredenciamentoRepositoryInterface;
use App\Contracts\Repositories\SobreEscolaRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\ClassLessonRepositoryInterface;
use App\Contracts\Services\BudgetServiceInterface;
use App\Contracts\Services\CertificateServiceInterface;
use App\Contracts\Services\CertificateTemplateServiceInterface;
use App\Contracts\Services\CagedProcessServiceInterface;
use App\Contracts\Services\CourseServiceInterface;
use App\Contracts\Services\CourseClassServiceInterface;
use App\Contracts\Services\CurriculumServiceInterface;
use App\Contracts\Services\CnaeServiceInterface;
use App\Contracts\Services\CnaeSinonimoServiceInterface;
use App\Contracts\Services\CnpjProcessServiceInterface;
use App\Contracts\Services\ComexProcessServiceInterface;
use App\Contracts\Services\EmpresaOverrideServiceInterface;
use App\Contracts\Services\EmpresaRelacaoArquivoServiceInterface;
use App\Contracts\Services\EmpresaRelacaoComentarioServiceInterface;
use App\Contracts\Services\EmpresaRelacaoServiceInterface;
use App\Contracts\Services\EstbanProcessServiceInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Services\NaturezaJuridicaServiceInterface;
use App\Contracts\Services\NoticiaServiceInterface;
use App\Contracts\Services\ExamBuilderServiceInterface;
use App\Contracts\Services\QuizServiceInterface;
use App\Contracts\Services\PermissionServiceInterface;
use App\Contracts\Services\RoleServiceInterface;
use App\Contracts\Services\ProfessorCredenciamentoServiceInterface;
use App\Contracts\Services\SobreEscolaServiceInterface;
use App\Contracts\Services\TenantServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Contracts\Services\AttendanceServiceInterface;
use App\Contracts\Services\GradeServiceInterface;
use App\Contracts\Services\TeacherServiceInterface;
use App\Contracts\TransactionalSmsSenderInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Contracts\Services\ClassLessonServiceInterface;
use App\Contracts\Services\EventCrudServiceInterface;
use App\Contracts\Services\Portal\PortalHomeServiceInterface;
use App\Contracts\Services\Portal\PortalThemeServiceInterface;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Repositories\BudgetRepository;
use App\Repositories\CertificateRepository;
use App\Repositories\CertificateTemplateRepository;
use App\Repositories\CentralProcessRunRepository;
use App\Repositories\CourseRepository;
use App\Repositories\CourseClassRepository;
use App\Repositories\CurriculumRepository;
use App\Repositories\CnaeRepository;
use App\Repositories\CnaeSinonimoRepository;
use App\Repositories\EmpresaOverrideRepository;
use App\Repositories\EmpresaRelacaoArquivoRepository;
use App\Repositories\EmpresaRelacaoComentarioRepository;
use App\Repositories\EmpresaRelacaoRepository;
use App\Repositories\EnrollmentRepository;
use App\Repositories\ExamTemplateRepository;
use App\Repositories\EventRepository;
use App\Repositories\NaturezaJuridicaRepository;
use App\Repositories\NoticiaRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\StudentRepository;
use App\Repositories\TeacherRepository;
use App\Repositories\AttendanceRepository;
use App\Repositories\GradeRepository;
use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Repositories\RoleRepository;
use App\Repositories\TenantLookupRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use App\Repositories\QuizRepository;
use App\Repositories\Portal\PortalCatalogRepository;
use App\Repositories\ProfessorCredenciamentoRepository;
use App\Repositories\SobreEscolaRepository;
use App\Repositories\ClassLessonRepository;
use App\Services\BudgetService;
use App\Services\CertificateService;
use App\Services\CertificateTemplateService;
use App\Services\CagedProcessService;
use App\Services\CourseService;
use App\Services\CourseClassService;
use App\Services\CurriculumService;
use App\Services\CnaeService;
use App\Services\CnaeSinonimoService;
use App\Services\CnpjProcessService;
use App\Services\ComexProcessService;
use App\Services\EmpresaOverrideService;
use App\Services\EmpresaRelacaoArquivoService;
use App\Services\EmpresaRelacaoComentarioService;
use App\Services\EmpresaRelacaoService;
use App\Services\EstbanProcessService;
use App\Services\EnrollmentService;
use App\Services\ExamBuilderService;
use App\Services\QuizService;
use App\Services\NaturezaJuridicaService;
use App\Services\NoticiaService;
use App\Services\PermissionService;
use App\Services\RoleService;
use App\Services\ProfessorCredenciamentoService;
use App\Services\SobreEscolaService;
use App\Services\StudentService;
use App\Services\AttendanceService;
use App\Services\GradeService;
use App\Services\TeacherService;
use App\Services\TenantService;
use App\Services\LogTransactionalSmsSender;
use App\Services\UserService;
use App\Services\ClassLessonService;
use App\Services\EventCrudService;
use App\Services\Portal\PortalHomeService;
use App\Services\Portal\PortalThemeService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use App\View\Composers\PortalComposer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Repositories
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(BudgetRepositoryInterface::class, BudgetRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(TenantRepositoryInterface::class, TenantRepository::class);
        $this->app->bind(CnaeRepositoryInterface::class, CnaeRepository::class);
        $this->app->bind(NaturezaJuridicaRepositoryInterface::class, NaturezaJuridicaRepository::class);
        $this->app->bind(CnaeSinonimoRepositoryInterface::class, CnaeSinonimoRepository::class);
        $this->app->bind(NoticiaRepositoryInterface::class, NoticiaRepository::class);
        $this->app->bind(CentralProcessRunRepositoryInterface::class, CentralProcessRunRepository::class);
        $this->app->bind(TenantLookupRepositoryInterface::class, TenantLookupRepository::class);
        $this->app->bind(EmpresaRelacaoRepositoryInterface::class, EmpresaRelacaoRepository::class);
        $this->app->bind(EmpresaRelacaoArquivoRepositoryInterface::class, EmpresaRelacaoArquivoRepository::class);
        $this->app->bind(EmpresaRelacaoComentarioRepositoryInterface::class, EmpresaRelacaoComentarioRepository::class);
        $this->app->bind(EmpresaOverrideRepositoryInterface::class, EmpresaOverrideRepository::class);
        $this->app->bind(EnrollmentRepositoryInterface::class, EnrollmentRepository::class);
        $this->app->bind(ExamTemplateRepositoryInterface::class, ExamTemplateRepository::class);
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
        $this->app->bind(CourseClassRepositoryInterface::class, CourseClassRepository::class);
        $this->app->bind(CurriculumRepositoryInterface::class, CurriculumRepository::class);
        $this->app->bind(EventRepositoryInterface::class, EventRepository::class);
        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
        $this->app->bind(ClassLessonRepositoryInterface::class, ClassLessonRepository::class);
        $this->app->bind(AttendanceRepositoryInterface::class, AttendanceRepository::class);
        $this->app->bind(GradeRepositoryInterface::class, GradeRepository::class);
        $this->app->bind(CertificateRepositoryInterface::class, CertificateRepository::class);
        $this->app->bind(CertificateTemplateRepositoryInterface::class, CertificateTemplateRepository::class);
        $this->app->bind(QuizRepositoryInterface::class, QuizRepository::class);
        $this->app->bind(ProfessorCredenciamentoRepositoryInterface::class, ProfessorCredenciamentoRepository::class);
        $this->app->bind(SobreEscolaRepositoryInterface::class, SobreEscolaRepository::class);
        $this->app->bind(PortalCatalogRepositoryInterface::class, PortalCatalogRepository::class);

        // Services
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(BudgetServiceInterface::class, BudgetService::class);
        $this->app->bind(RoleServiceInterface::class, RoleService::class);
        $this->app->bind(PermissionServiceInterface::class, PermissionService::class);
        $this->app->bind(TenantServiceInterface::class, TenantService::class);
        $this->app->bind(CnaeServiceInterface::class, CnaeService::class);
        $this->app->bind(NaturezaJuridicaServiceInterface::class, NaturezaJuridicaService::class);
        $this->app->bind(CnaeSinonimoServiceInterface::class, CnaeSinonimoService::class);
        $this->app->bind(NoticiaServiceInterface::class, NoticiaService::class);
        $this->app->bind(CnpjProcessServiceInterface::class, CnpjProcessService::class);
        $this->app->bind(CagedProcessServiceInterface::class, CagedProcessService::class);
        $this->app->bind(ComexProcessServiceInterface::class, ComexProcessService::class);
        $this->app->bind(EstbanProcessServiceInterface::class, EstbanProcessService::class);
        $this->app->bind(EmpresaRelacaoArquivoServiceInterface::class, EmpresaRelacaoArquivoService::class);
        $this->app->bind(EmpresaRelacaoComentarioServiceInterface::class, EmpresaRelacaoComentarioService::class);
        $this->app->bind(EmpresaRelacaoServiceInterface::class, EmpresaRelacaoService::class);
        $this->app->bind(EmpresaOverrideServiceInterface::class, EmpresaOverrideService::class);
        $this->app->bind(EnrollmentServiceInterface::class, EnrollmentService::class);
        $this->app->bind(ExamBuilderServiceInterface::class, ExamBuilderService::class);
        $this->app->bind(CourseServiceInterface::class, CourseService::class);
        $this->app->bind(CourseClassServiceInterface::class, CourseClassService::class);
        $this->app->bind(CurriculumServiceInterface::class, CurriculumService::class);
        $this->app->bind(StudentServiceInterface::class, StudentService::class);
        $this->app->bind(TeacherServiceInterface::class, TeacherService::class);
        $this->app->bind(ClassLessonServiceInterface::class, ClassLessonService::class);
        $this->app->bind(EventCrudServiceInterface::class, EventCrudService::class);
        $this->app->bind(AttendanceServiceInterface::class, AttendanceService::class);
        $this->app->bind(GradeServiceInterface::class, GradeService::class);
        $this->app->bind(CertificateServiceInterface::class, CertificateService::class);
        $this->app->bind(CertificateTemplateServiceInterface::class, CertificateTemplateService::class);
        $this->app->bind(QuizServiceInterface::class, QuizService::class);
        $this->app->bind(ProfessorCredenciamentoServiceInterface::class, ProfessorCredenciamentoService::class);
        $this->app->bind(SobreEscolaServiceInterface::class, SobreEscolaService::class);
        $this->app->bind(PortalThemeServiceInterface::class, PortalThemeService::class);
        $this->app->bind(PortalHomeServiceInterface::class, PortalHomeService::class);

        $this->app->bind(TransactionalSmsSenderInterface::class, LogTransactionalSmsSender::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Activity log listeners
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        View::composer(['layouts.portal', 'portal.*', 'auth.tenant-verify-email'], PortalComposer::class);
    }
}
