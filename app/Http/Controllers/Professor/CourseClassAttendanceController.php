<?php

namespace App\Http\Controllers\Professor;

use App\Contracts\Services\ClassLessonServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Professor\QuickClassLessonFromSheetRequest;
use App\Models\Attendance;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\TenantAdminSetting;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseClassAttendanceController extends Controller
{
    /** @var list<string> */
    private const SHEET_ENROLLMENT_STATUSES = ['inscrito', 'cursando', 'concluido', 'baixa_presenca'];

    public function __construct(private ClassLessonServiceInterface $classLessonService) {}

    /** @return list<string> */
    private function enrollmentStatusesForSheet(): array
    {
        return self::SHEET_ENROLLMENT_STATUSES;
    }

    public function show(Request $request, CourseClass $turma): View
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $dateInput = $this->parseSheetDateParameter($request);
        $user = auth()->user();
        $authStaffCanOverrideAttendance = $user
            && ($user->isTenantManager() || $user->hasTenantRole(User::TYPE_TENANT_MANAGER));

        $turma->loadMissing([
            'schedules' => fn ($q) => $q->orderBy('weekday')->orderBy('start_time'),
        ]);
        $weeklyScheduleSlots = $turma->schedules;

        $enrollments = Enrollment::query()
            ->withStudentForAttendanceSheet((int) $turma->tenant_id)
            ->where('course_class_id', $turma->id)
            ->whereIn('status', $this->enrollmentStatusesForSheet())
            ->orderBy('status')
            ->get()
            ->sortBy(fn (Enrollment $enrollment) => mb_strtolower((string) ($enrollment->student?->user?->name
                ?? $enrollment->student?->email
                ?? '')))
            ->values();

        $studentIds = $enrollments->pluck('student_id');

        $turma->loadMissing(['course:id,name']);
        $lessons = ClassLesson::orderedForCourseClass((int) $turma->id);

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
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Minhas turmas', 'href' => route('professor.turmas.index')],
            ['label' => $turma->name, 'href' => route('professor.turmas.show', $turma)],
            ['label' => 'Chamadas'],
        ];

        $recentAnnouncements = \App\Models\CourseClassAnnouncement::query()
            ->where('course_class_id', $turma->id)
            ->with('createdBy:id,name')
            ->withCount('deliveries')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $defaultScheduleStart = optional($weeklyScheduleSlots->first())->start_time ?? '19:00';
        $defaultScheduleEnd = optional($weeklyScheduleSlots->first())->end_time ?? '22:00';

        return view('professor.course-classes.attendance-sheet', compact(
            'turma',
            'date',
            'enrollments',
            'breadcrumbs',
            'recentAnnouncements',
            'weeklyScheduleSlots',
            'lessons',
            'authStaffCanOverrideAttendance',
            'lessonSheetLessons',
            'lessonActiveLesson',
            'lessonSheetMeta',
            'lessonHasAttendance',
            'lessonCanManage',
            'lessonAttendanceByStudent',
            'lessonActiveMeta',
            'defaultScheduleStart',
            'defaultScheduleEnd',
        ));
    }

    /**
     * Sanitiza `date=` na query (links com "&amp;" colados geram valores inválidos para Carbon).
     */
    private function parseSheetDateParameter(Request $request): string
    {
        $raw = (string) $request->query('date', '');
        $raw = str_replace('&amp;', '&', $raw);
        if (($pos = strpos($raw, '&')) !== false) {
            $raw = substr($raw, 0, $pos);
        }
        $raw = trim($raw);
        if ($raw === '') {
            return Carbon::now()->toDateString();
        }
        try {
            return Carbon::parse($raw)->toDateString();
        } catch (\Throwable) {
            return Carbon::now()->toDateString();
        }
    }

    public function quickStoreLesson(QuickClassLessonFromSheetRequest $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $data = [...$request->validated(), 'course_class_id' => $turma->id];

        $lesson = $this->classLessonService->create($data);

        return redirect()
            ->route('professor.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $lesson->date?->toDateString() ?? $request->validated()['date'],
                'tab' => 'chamadas',
                'lesson' => $lesson->id,
            ])
            ->with('success', 'Aula criada — você já pode lançar a chamada.');
    }

    public function store(Request $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $validated = $request->validate([
            'lesson_id' => ['required', 'integer', Rule::exists('class_lessons', 'id')->where('course_class_id', $turma->id)],
            'present_students' => ['nullable', 'array'],
            'present_students.*' => ['integer'],
        ]);

        $lesson = ClassLesson::findForTurmaOrFail((int) $turma->id, (int) $validated['lesson_id']);

        $presentStudents = collect($validated['present_students'] ?? [])->map(fn ($id) => (int) $id)->values();

        $enrollments = Enrollment::query()
            ->where('course_class_id', $turma->id)
            ->whereIn('status', $this->enrollmentStatusesForSheet())
            ->get(['student_id']);

        $existingRecord = Attendance::query()
            ->where('course_id', $turma->course_id)
            ->where('class_lesson_id', $lesson->id)
            ->whereIn('student_id', $enrollments->pluck('student_id'))
            ->whereNotNull('recorded_by_user_id')
            ->orderBy('id')
            ->first(['recorded_by_user_id']);

        $isStaff = false;
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
            ->route('professor.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $classDate,
                'tab' => 'chamadas',
                'lesson' => $lesson->id,
            ])
            ->with('success', 'Chamada da aula salva com sucesso.');
    }

    public function destroy(Request $request, CourseClass $turma): RedirectResponse
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

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

        if ($existingRecord && (int) $existingRecord->recorded_by_user_id !== (int) auth()->id()) {
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
            ->route('professor.turmas.ficha-presenca', [
                'turma' => $turma,
                'date' => $lesson->date?->toDateString() ?? now()->toDateString(),
                'tab' => 'chamadas',
                'lesson' => $lesson->id,
            ])
            ->with('success', $message);
    }

    public function print(Request $request, CourseClass $turma)
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $lessonId = (int) $request->query('lesson', 0);
        abort_unless($lessonId > 0, 404);

        $lesson = ClassLesson::findForTurmaOrFail((int) $turma->id, $lessonId);

        $date = $lesson->date?->toDateString() ?? now()->toDateString();
        $printMode = $request->string('mode')->toString() === 'blank' ? 'blank' : 'filled';

        $enrollments = Enrollment::query()
            ->withStudentForAttendanceSheet((int) $turma->tenant_id)
            ->where('course_class_id', $turma->id)
            ->whereIn('status', $this->enrollmentStatusesForSheet())
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
