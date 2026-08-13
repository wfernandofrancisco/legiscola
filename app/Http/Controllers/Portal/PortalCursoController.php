<?php

namespace App\Http\Controllers\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Support\TenantContext;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PortalCursoController extends Controller
{
    /** @var array<string, string> */
    private const TURMA_TABS = [
        'cadastrado' => 'Em cadastro',
        'inscricao' => 'Inscrições abertas',
        'em_andamento' => 'Em andamento',
        'concluido' => 'Encerradas',
    ];

    public function __construct(
        private PortalCatalogRepositoryInterface $catalog,
    ) {}

    public function index(Request $request): View
    {
        $cursos = $this->catalog->coursesWithOfferingsPaginated((int) $request->integer('per_page') ?: 12);

        $requested = $request->query('situacao');
        $situacao = is_string($requested) && array_key_exists($requested, self::TURMA_TABS)
            ? $requested
            : null;

        if ($situacao === null) {
            foreach (array_keys(self::TURMA_TABS) as $st) {
                foreach ($cursos as $curso) {
                    if ($curso->courseClasses->contains(fn ($t) => ($t->status ?? '') === $st)) {
                        $situacao = $st;
                        break 2;
                    }
                }
            }
            $situacao ??= array_key_first(self::TURMA_TABS);
        }

        $cursosSemTurmaPortal = collect($cursos->items())->filter(
            fn ($c) => $c->relationLoaded('courseClasses') && $c->courseClasses->where('status', '!=', 'cancelado')->isEmpty()
        );

        return view('portal.cursos.index', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'cursos' => $cursos,
            'turmaTabs' => self::TURMA_TABS,
            'situacao' => $situacao,
            'cursosSemTurmaPortal' => $cursosSemTurmaPortal,
        ]);
    }

    public function show(int $curso): View
    {
        $cursoModel = $this->catalog->findCourseForPortal($curso);
        abort_if($cursoModel === null, 404);
        abort_unless((int) $cursoModel->tenant_id === TenantContext::getTenantId(), 404);

        $studentEnrollmentStatuses = [];
        if (auth()->check()) {
            $studentId = Student::query()
                ->where('user_id', auth()->id())
                ->value('id');

            if ($studentId) {
                $studentEnrollmentStatuses = Enrollment::query()
                    ->where('student_id', $studentId)
                    ->whereIn('course_class_id', $cursoModel->courseClasses->pluck('id'))
                    ->pluck('status', 'course_class_id')
                    ->all();
            }
        }

        return view('portal.cursos.show', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'curso' => $cursoModel,
            'cursosRelacionados' => $this->catalog->relatedActiveCourses($cursoModel->id, 3),
            'studentEnrollmentStatuses' => $studentEnrollmentStatuses,
        ]);
    }

    public function enroll(int $curso, CourseClass $turma, EnrollmentServiceInterface $enrollmentService): RedirectResponse
    {
        abort_unless((int) $turma->tenant_id === TenantContext::getTenantId(), 404);
        abort_unless((int) $turma->course_id === $curso, 404);

        $studentId = Student::query()
            ->where('user_id', auth()->id())
            ->value('id');

        if (! $studentId) {
            return back()->with('error', 'Perfil de aluno não encontrado para este usuário.');
        }

        try {
            $enrollmentService->matricularEmTurma((int) $studentId, (int) $turma->id);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Inscrição realizada com sucesso.');
    }

    public function historico(Request $request): View
    {
        return view('portal.cursos.historico', [
            'tenant' => Tenant::query()->findOrFail((int) TenantContext::getTenantId()),
            'turmas' => $this->catalog->concludedCourseClassesPaginated((int) $request->integer('per_page') ?: 24),
        ]);
    }
}
