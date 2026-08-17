<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CourseClassServiceInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Enums\CertificateTipoEmissao;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCourseClassAnnouncementRequest;
use App\Http\Requests\Escola\StoreCourseClassEnrollmentRequest;
use App\Http\Requests\Escola\StoreCourseClassRequest;
use App\Http\Requests\Escola\UpdateCourseClassQuizWindowsRequest;
use App\Http\Requests\Escola\UpdateCourseClassRequest;
use App\Http\Requests\Escola\UpdateEnrollmentStatusRequest;
use App\Jobs\ProcessCourseClassAnnouncementJob;
use App\Models\Attendance;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\CourseClass;
use App\Models\ClassLesson;
use App\Models\CourseClassAnnouncement;
use App\Models\Teacher;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\SatisfactionSurvey;
use App\Models\TenantAdminSetting;
use App\Support\CourseClassAttendance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseClassCrudController extends Controller
{
    private const ATTENDANCE_ENROLLMENT_STATUSES = ['inscrito', 'cursando', 'concluido', 'baixa_presenca'];

    public function __construct(
        private CourseClassServiceInterface $service,
        private EnrollmentServiceInterface $enrollmentService
    ) {}

    public function index(Request $request): View
    {
        $courseClasses = $this->service->paginateFiltered(
            15,
            $request->string('search')->toString(),
            $request->string('status')->toString() ?: null
        );
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Turmas'],
        ];

        return view('admin.course-classes.index', compact('courseClasses', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Turmas', 'href' => route('admin.turmas.index')],
            ['label' => 'Nova turma'],
        ];

        $teachers = Teacher::query()->where('status', 'ativo')->orderBy('full_name')->orderBy('email')->get();
        $satisfactionSurveys = SatisfactionSurvey::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        return view('admin.course-classes.create', compact('breadcrumbs', 'teachers', 'satisfactionSurveys'));
    }

    public function store(StoreCourseClassRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.turmas.index')->with('success', 'Turma criada com sucesso.');
    }

    public function edit(CourseClass $turma): View
    {
        $courseClass = $turma->load([
            'linkedQuizzes' => fn ($q) => $q->orderBy('title'),
            'teachers',
        ]);
        $teachers = Teacher::query()->where('status', 'ativo')->orderBy('full_name')->orderBy('email')->get();
        $satisfactionSurveys = SatisfactionSurvey::query()
            ->where(function ($q) use ($turma): void {
                $q->where('is_active', true);
                if ($turma->satisfaction_survey_id) {
                    $q->orWhere('id', $turma->satisfaction_survey_id);
                }
            })
            ->orderBy('title')
            ->get(['id', 'title']);
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Turmas', 'href' => route('admin.turmas.index')],
            ['label' => 'Editar: '.$turma->name],
        ];

        return view('admin.course-classes.edit', compact('courseClass', 'breadcrumbs', 'teachers', 'satisfactionSurveys'));
    }

    public function show(Request $request, CourseClass $turma): View
    {
        $turma->load([
            'linkedQuizzes' => fn ($q) => $q->orderBy('title'),
        ]);

        $summary = $this->enrollmentService->statusSummary($turma->id);
        $enrollments = $this->enrollmentService->paginateByCourseClass(
            $turma->id,
            15,
            $request->string('search')->toString() ?: null,
            $request->string('filter_status')->toString() ?: null
        );
        $studentIdsOnPage = collect($enrollments->items())->pluck('student_id')->filter()->values();
        $latestCertificateHashByStudent = [];
        $activeCertificateTemplate = CertificateTemplate::latestActiveForEmission(CertificateTipoEmissao::Curso);

        if ($studentIdsOnPage->isNotEmpty()) {
            $latestCertificateHashByStudent = Certificate::query()
                ->whereIn('student_id', $studentIdsOnPage)
                ->whereNotIn('status', ['revoked', 'revogado', 'inativo', 'cancelado'])
                ->whereNotNull('validation_hash')
                ->orderByDesc('issued_at')
                ->orderByDesc('id')
                ->get(['student_id', 'validation_hash'])
                ->unique('student_id')
                ->pluck('validation_hash', 'student_id')
                ->toArray();
        }

        $allStudentIdsInTurma = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->pluck('student_id');

        $totalAttendanceDates = 0;
        $attendancePercentageByStudent = [];

        if ($allStudentIdsInTurma->isNotEmpty()) {
            $attendanceStats = CourseClassAttendance::percentagesByStudentIds($turma, $allStudentIdsInTurma);
            $totalAttendanceDates = $attendanceStats['denominator'];
            $attendancePercentageByStudent = $attendanceStats['by_student_id'];
        }

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Turmas', 'href' => route('admin.turmas.index')],
            ['label' => 'Triagem de alunos'],
        ];

        $recentAnnouncements = CourseClassAnnouncement::query()
            ->where('course_class_id', $turma->id)
            ->with('createdBy:id,name')
            ->withCount('deliveries')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $surveyCompletedByStudent = [];
        if ($turma->requiresSatisfactionSurvey() && $studentIdsOnPage->isNotEmpty()) {
            $surveyCompletedByStudent = \App\Models\SatisfactionSurveyResponse::query()
                ->where('satisfaction_survey_id', $turma->satisfaction_survey_id)
                ->where('course_class_id', $turma->id)
                ->whereIn('student_id', $studentIdsOnPage)
                ->pluck('student_id')
                ->mapWithKeys(fn ($id) => [(int) $id => true])
                ->all();
        }

        return view('admin.course-classes.show', compact(
            'turma',
            'summary',
            'enrollments',
            'breadcrumbs',
            'totalAttendanceDates',
            'attendancePercentageByStudent',
            'latestCertificateHashByStudent',
            'activeCertificateTemplate',
            'recentAnnouncements',
            'surveyCompletedByStudent'
        ));
    }

    public function storeAnnouncement(StoreCourseClassAnnouncementRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('update', $turma);

        $data = $request->validated();
        $channels = array_values(array_unique($data['channels']));

        $announcement = CourseClassAnnouncement::query()->create([
            'tenant_id' => $turma->tenant_id,
            'course_class_id' => $turma->id,
            'reference_date' => $data['reference_date'] ?? null,
            'subject' => isset($data['subject']) ? trim((string) $data['subject']) : null,
            'body' => trim((string) $data['body']),
            'channels' => $channels,
            'consent_acknowledged' => true,
            'created_by' => auth()->id(),
        ]);

        ProcessCourseClassAnnouncementJob::dispatch($announcement->id)->afterCommit();

        $refererPath = parse_url((string) $request->headers->get('Referer'), PHP_URL_PATH) ?? '';
        $redirectRoute = str_contains($refererPath, '/ficha-presenca')
            ? route('admin.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $data['reference_date'] ?? now()->toDateString(),
                'tab' => 'avisos',
            ])
            : route('admin.turmas.show', ['turma' => $turma, 'tab' => 'avisos']);

        return redirect()
            ->to($redirectRoute)
            ->with(
                'success',
                'Aviso registrado. E-mails foram enfileirados (fila + MAIL_* do .env). SMS simulado: veja storage/logs/laravel.log.'
            );
    }

    public function update(UpdateCourseClassRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->service->update($turma, $request->validated());

        return redirect()->route('admin.turmas.index')->with('success', 'Turma atualizada com sucesso.');
    }

    public function updateQuizWindows(UpdateCourseClassQuizWindowsRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('update', $turma);

        foreach ($request->validated()['windows'] ?? [] as $row) {
            $quizId = (int) $row['quiz_id'];
            if (! $turma->linkedQuizzes()->where('quizzes.id', $quizId)->exists()) {
                continue;
            }

            $opens = $row['opens_at'] ?? null;
            $closes = $row['closes_at'] ?? null;
            if ($opens && $closes && Carbon::parse($closes)->lt(Carbon::parse($opens))) {
                return back()->withErrors([
                    'windows' => 'Em cada linha, o encerramento deve ser na mesma data/hora ou depois da abertura.',
                ])->withInput();
            }

            $turma->linkedQuizzes()->updateExistingPivot($quizId, [
                'opens_at' => $opens,
                'closes_at' => $closes,
            ]);
        }

        $refererPath = parse_url((string) $request->headers->get('Referer'), PHP_URL_PATH) ?? '';
        if (str_ends_with(rtrim($refererPath, '/'), '/edit')) {
            return redirect()->route('admin.turmas.edit', $turma)
                ->with('success', 'Janelas de disponibilidade dos quizzes nesta turma foram salvas.');
        }

        return redirect()
            ->to(route('admin.turmas.show', $turma).'?tab=quizzes')
            ->with('success', 'Janelas de disponibilidade dos quizzes nesta turma foram salvas.');
    }

    public function destroy(CourseClass $turma): RedirectResponse
    {
        $this->service->delete($turma);

        return redirect()->route('admin.turmas.index')->with('success', 'Turma removida com sucesso.');
    }

    public function updateEnrollmentStatus(UpdateEnrollmentStatusRequest $request, CourseClass $turma, Enrollment $enrollment): RedirectResponse
    {
        abort_if((int) $enrollment->course_class_id !== (int) $turma->id, 404);

        $this->enrollmentService->updateStatus(
            $enrollment,
            $request->validated()['enrollment_status'],
            $request->validated()['observations'] ?? null
        );

        return back()->with('success', 'Status da matrícula atualizado com sucesso.');
    }

    public function markInscritosAsConcluido(CourseClass $turma): RedirectResponse
    {
        if ($turma->status !== 'concluido') {
            return back()->withErrors([
                'status' => 'Só é permitido concluir alunos quando a turma estiver com status concluído.',
            ]);
        }

        $minimumAttendance = 75;
        $inscritos = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->where('status', 'inscrito')
            ->get(['id', 'student_id']);

        if ($inscritos->isEmpty()) {
            return back()->with('success', 'Nenhum aluno com status inscrito para atualizar.');
        }

        $studentIds = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->pluck('student_id');

        $attendanceStats = CourseClassAttendance::percentagesByStudentIds($turma, $studentIds);
        $totalAttendanceDates = $attendanceStats['denominator'];

        $eligibleEnrollmentIds = [];
        foreach ($inscritos as $enrollment) {
            $percent = (int) ($attendanceStats['by_student_id'][$enrollment->student_id] ?? 0);

            if ($totalAttendanceDates > 0 && $percent >= $minimumAttendance) {
                $eligibleEnrollmentIds[] = (int) $enrollment->id;
            }
        }

        $updated = 0;
        if ($eligibleEnrollmentIds !== []) {
            $updated = Enrollment::query()
                ->whereIn('id', $eligibleEnrollmentIds)
                ->update([
                    'status' => 'concluido',
                    'updated_at' => now(),
                ]);
        }

        $notEligible = $inscritos->count() - $updated;

        if ($updated > 0 && $notEligible > 0) {
            $message = "{$updated} aluno(s) inscrito(s) marcado(s) como concluído(s). {$notEligible} não atingiram {$minimumAttendance}% de presença nas aulas da turma.";
        } elseif ($updated > 0) {
            $message = "{$updated} aluno(s) inscrito(s) marcado(s) como concluído(s).";
        } else {
            $message = "Nenhum inscrito atingiu {$minimumAttendance}% de presença nas aulas cadastradas para conclusão.";
        }

        return back()->with('success', $message);
    }

    public function storeEnrollment(StoreCourseClassEnrollmentRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->enrollmentService->matricularEmTurmaAdmin(
            (int) $request->validated()['student_id'],
            $turma->id,
            (string) ($request->validated()['status'] ?? 'inscrito'),
            $request->validated()['observations'] ?? null
        );

        return back()->with('success', 'Aluno matriculado na turma com sucesso.');
    }

    public function searchStudents(Request $request, CourseClass $turma): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        $results = Student::query()
            ->with('user:id,name,email,status')
            ->where('status', 'ativo')
            ->whereHas('user', fn ($q) => $q->where('status', 'ativo'))
            ->whereDoesntHave('enrollments', fn ($q) => $q->where('course_class_id', $turma->id))
            ->when($term !== '', function ($query) use ($term): void {
                $query->where(function ($q) use ($term): void {
                    $q->where('email', 'like', "%{$term}%")
                        ->orWhereHas('user', function ($uq) use ($term): void {
                            $uq->where('name', 'like', "%{$term}%")
                                ->orWhere('email', 'like', "%{$term}%");
                        });
                });
            })
            ->limit(20)
            ->get()
            ->map(fn (Student $student) => [
                'id' => $student->id,
                'name' => $student->user?->name ?? 'Sem nome',
                'email' => $student->user?->email ?? '',
            ]);

        return response()->json($results);
    }

    public function attendanceSheet(Request $request, CourseClass $turma): View
    {
        $dateInput = Carbon::parse($request->string('date')->toString() ?: now()->toDateString())->toDateString();

        $enrollments = Enrollment::query()
            ->withStudentForAttendanceSheet((int) $turma->tenant_id)
            ->where('course_class_id', $turma->id)
            ->whereIn('status', self::ATTENDANCE_ENROLLMENT_STATUSES)
            ->orderBy('status')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => mb_strtolower((string) ($enrollment->student?->user?->name
                ?? $enrollment->student?->email
                ?? '')))
            ->values();

        $studentIds = $enrollments->pluck('student_id');

        $turma->loadMissing(['course:id,name']);

        $lessons = ClassLesson::orderedForCourseClass((int) $turma->id);

        $authStaffCanOverrideAttendance = auth()->user()->isTenantAdmin() || auth()->user()->isTenantManager();

        $lessonSheetLessons = $lessons->values();

        $lessonActiveLesson = null;
        $lessonSheetMeta = [];
        $lessonHasAttendance = false;
        $lessonCanManage = false;
        $lessonAttendanceByStudent = collect();
        $lessonActiveMeta = null;

        if ($lessonSheetLessons->isNotEmpty()) {
            $lessonQueryId = (int) $request->query('lesson', 0);
            $lessonActiveLesson = $lessonQueryId > 0
                ? $lessonSheetLessons->first(fn (ClassLesson $l) => (int) $l->id === $lessonQueryId)
                : null;
            if (! $lessonActiveLesson) {
                $lessonActiveLesson = $lessonSheetLessons->first(fn (ClassLesson $l) => $l->date?->toDateString() === $dateInput)
                    ?? $lessonSheetLessons->first();
            }

            $lessonSheetMeta = $this->sheetMetaByLesson($turma, $studentIds, $lessonSheetLessons->pluck('id'));

            $lessonHasAttendance = Attendance::query()
                ->where('course_id', $turma->course_id)
                ->where('class_lesson_id', $lessonActiveLesson->id)
                ->whereIn('student_id', $studentIds)
                ->exists();

            $lessonActiveMeta = $lessonSheetMeta[(int) $lessonActiveLesson->id] ?? null;

            $lessonCanManage = $authStaffCanOverrideAttendance
                || ! $lessonHasAttendance
                || ((int) ($lessonActiveMeta['recorded_by_user_id'] ?? 0) === (int) auth()->id());

            $lessonAttendanceByStudent = Attendance::query()
                ->where('course_id', $turma->course_id)
                ->where('class_lesson_id', $lessonActiveLesson->id)
                ->whereIn('student_id', $studentIds)
                ->pluck('is_present', 'student_id');
        }

        $date = $lessonActiveLesson
            ? ($lessonActiveLesson->date?->toDateString() ?? $dateInput)
            : $dateInput;

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Turmas', 'href' => route('admin.turmas.index')],
            ['label' => 'Triagem', 'href' => route('admin.turmas.show', $turma)],
            ['label' => 'Chamadas por aula'],
        ];

        $recentAnnouncements = CourseClassAnnouncement::query()
            ->where('course_class_id', $turma->id)
            ->with('createdBy:id,name')
            ->withCount('deliveries')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.course-classes.attendance-sheet', compact(
            'turma',
            'date',
            'enrollments',
            'breadcrumbs',
            'recentAnnouncements',
            'lessons',
            'authStaffCanOverrideAttendance',
            'lessonSheetLessons',
            'lessonActiveLesson',
            'lessonSheetMeta',
            'lessonHasAttendance',
            'lessonCanManage',
            'lessonAttendanceByStudent',
            'lessonActiveMeta'
        ));
    }

    public function storeAttendanceSheet(Request $request, CourseClass $turma): RedirectResponse
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', Rule::exists('class_lessons', 'id')->where('course_class_id', $turma->id)],
            'present_students' => ['nullable', 'array'],
            'present_students.*' => ['integer'],
        ]);

        $lesson = ClassLesson::findForTurmaOrFail((int) $turma->id, (int) $validated['lesson_id']);

        $presentStudents = collect($validated['present_students'] ?? [])->map(fn ($id) => (int) $id)->values();

        $enrollments = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->whereIn('status', self::ATTENDANCE_ENROLLMENT_STATUSES)
            ->get(['student_id']);

        $existingRecord = Attendance::query()
            ->where('course_id', $turma->course_id)
            ->where('class_lesson_id', $lesson->id)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->whereNotNull('recorded_by_user_id')
            ->orderBy('id')
            ->first(['recorded_by_user_id']);

        $isStaff = auth()->user()->isTenantAdmin() || auth()->user()->isTenantManager();
        if ($existingRecord && (int) $existingRecord->recorded_by_user_id !== (int) auth()->id() && ! $isStaff) {
            $this->authorize('manageSheet', [Attendance::class, (int) $existingRecord->recorded_by_user_id]);
        }

        $classDate = $lesson->date?->toDateString() ?? now()->toDateString();

        foreach ($enrollments as $enrollment) {
            $isPresent = $presentStudents->contains((int) $enrollment->student_id);

            Attendance::query()->updateOrCreate(
                [
                    'tenant_id' => (int) auth()->user()->tenant_id,
                    'student_id' => (int) $enrollment->student_id,
                    'class_lesson_id' => $lesson->id,
                ],
                [
                    'course_id' => $turma->course_id,
                    'class_date' => $classDate,
                    'status' => $isPresent ? 'presente' : 'falta',
                    'is_present' => $isPresent,
                    'recorded_by_user_id' => auth()->id(),
                ]
            );
        }

        return redirect()
            ->route('admin.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $classDate,
                'tab' => 'chamadas',
                'lesson' => $lesson->id,
            ])
            ->with('success', 'Chamada da aula salva com sucesso.');
    }

    public function destroyAttendanceSheet(Request $request, CourseClass $turma): RedirectResponse
    {
        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', Rule::exists('class_lessons', 'id')->where('course_class_id', $turma->id)],
        ]);

        $lesson = ClassLesson::findForTurmaOrFail((int) $turma->id, (int) $validated['lesson_id']);

        $studentIds = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->pluck('student_id');

        $existingRecord = Attendance::query()
            ->where('course_id', $turma->course_id)
            ->where('class_lesson_id', $lesson->id)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('recorded_by_user_id')
            ->orderBy('id')
            ->first(['recorded_by_user_id']);

        $isStaff = auth()->user()->isTenantAdmin() || auth()->user()->isTenantManager();
        if ($existingRecord && (int) $existingRecord->recorded_by_user_id !== (int) auth()->id() && ! $isStaff) {
            $this->authorize('manageSheet', [Attendance::class, (int) $existingRecord->recorded_by_user_id]);
        }

        $deleted = Attendance::query()
            ->where('course_id', $turma->course_id)
            ->where('class_lesson_id', $lesson->id)
            ->whereIn('student_id', $studentIds)
            ->delete();

        $message = $deleted > 0
            ? 'Chamada desta aula foi excluída.'
            : 'Não havia presença registrada para esta aula.';

        return redirect()
            ->route('admin.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $lesson->date?->toDateString() ?? now()->toDateString(),
                'tab' => 'chamadas',
                'lesson' => $lesson->id,
            ])
            ->with('success', $message);
    }

    public function printAttendanceSheet(Request $request, CourseClass $turma)
    {
        $lessonId = (int) $request->query('lesson', 0);
        abort_unless($lessonId > 0, 404);

        $lesson = ClassLesson::findForTurmaOrFail((int) $turma->id, $lessonId);

        $date = $lesson->date?->toDateString() ?? now()->toDateString();
        $printMode = $request->string('mode')->toString() === 'blank' ? 'blank' : 'filled';

        $enrollments = Enrollment::query()
            ->withStudentForAttendanceSheet((int) $turma->tenant_id)
            ->where('course_class_id', $turma->id)
            ->whereIn('status', self::ATTENDANCE_ENROLLMENT_STATUSES)
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => mb_strtolower((string) ($enrollment->student?->user?->name
                ?? $enrollment->student?->email
                ?? '')))
            ->values();

        $attendanceByStudent = Attendance::query()
            ->where('class_lesson_id', $lesson->id)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->pluck('is_present', 'student_id');

        $tenant = auth()->user()->tenant()->first();
        $settings = TenantAdminSetting::query()->where('tenant_id', auth()->user()->tenant_id)->first();

        $logoPath = null;
        if (! empty($settings?->logo_prefeitura_path)) {
            $candidate = storage_path('app/public/'.$settings->logo_prefeitura_path);
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $pdf = Pdf::loadView('admin.course-classes.attendance-sheet-pdf', [
            'turma' => $turma,
            'classLesson' => $lesson,
            'date' => $date,
            'tenant' => $tenant,
            'logoPath' => $logoPath,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
            'printMode' => $printMode,
            'enrollments' => $enrollments,
            'attendanceByStudent' => $attendanceByStudent,
        ])->setPaper('a4', 'portrait');

        $filename = 'chamada-'.str($turma->name)->slug().'-'.str($lesson->title)->slug().'-'.$date.'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Metadados de quem lançou presença, por aula.
     *
     * @return array<int, array{recorded_by_user_id: int, recorded_by_name: string}>
     */
    private function sheetMetaByLesson(CourseClass $turma, Collection $studentIds, Collection $lessonIds): array
    {
        if ($lessonIds->isEmpty()) {
            return [];
        }

        $records = Attendance::query()
            ->with('recorder:id,name')
            ->where('course_id', $turma->course_id)
            ->whereIn('class_lesson_id', $lessonIds)
            ->whereIn('student_id', $studentIds)
            ->whereNotNull('recorded_by_user_id')
            ->orderByDesc('id')
            ->get(['class_lesson_id', 'recorded_by_user_id']);

        return $records
            ->groupBy(fn (Attendance $attendance) => (int) $attendance->class_lesson_id)
            ->map(function (Collection $items): array {
                /** @var Attendance $latest */
                $latest = $items->first();
                $fullName = (string) ($latest->recorder?->name ?? 'Usuário não identificado');

                return [
                    'recorded_by_user_id' => (int) $latest->recorded_by_user_id,
                    'recorded_by_name' => $this->firstAndLastName($fullName),
                ];
            })
            ->all();
    }

    private function firstAndLastName(string $fullName): string
    {
        $parts = preg_split('/\s+/', trim($fullName)) ?: [];
        $parts = array_values(array_filter($parts, fn (string $part): bool => $part !== ''));
        if (count($parts) <= 1) {
            return $fullName;
        }

        return $parts[0].' '.$parts[count($parts) - 1];
    }
}
