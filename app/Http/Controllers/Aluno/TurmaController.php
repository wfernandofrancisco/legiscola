<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use App\Support\AlunoProgress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TurmaController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function index(): View
    {
        $student = $this->requireStudent();

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['inscrito', 'cursando', 'concluido'])
            ->with(['courseClass.course', 'courseClass.schedules', 'courseClass.lessons', 'courseClass.teachers'])
            ->orderByDesc('id')
            ->get();

        $rows = $enrollments->map(function (Enrollment $e) use ($student) {
            $cc = $e->courseClass;
            if (! $cc) {
                return null;
            }

            return [
                'enrollment' => $e,
                'courseClass' => $cc,
                'quizPct' => AlunoProgress::quizCompletionPercent($student, $cc),
                'presencePct' => AlunoProgress::attendanceSheetPercent($student, $cc),
            ];
        })->filter()->values();

        $studentEnrollmentStatuses = Enrollment::query()
            ->where('student_id', $student->id)
            ->pluck('status', 'course_class_id')
            ->all();

        $availableCourseClasses = CourseClass::query()
            ->where('status', 'inscricao')
            ->where('enrollment_start', '<=', now())
            ->where('enrollment_end', '>=', now())
            ->with(['course', 'schedules', 'teachers'])
            ->withCount([
                'enrollments as matriculas_count' => fn ($query) => $query->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->latest('enrollment_end')
            ->get();

        return view('aluno.turmas.index', compact('student', 'rows', 'availableCourseClasses', 'studentEnrollmentStatuses'));
    }

    public function enroll(CourseClass $courseClass, EnrollmentServiceInterface $enrollmentService): RedirectResponse
    {
        $student = $this->requireStudent();

        try {
            $enrollmentService->matricularEmTurma((int) $student->id, (int) $courseClass->id);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Inscrição realizada com sucesso.');
    }

    public function show(CourseClass $courseClass): View
    {
        $student = $this->requireStudent();

        $enrollment = Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_class_id', $courseClass->id)
            ->whereIn('status', ['inscrito', 'cursando', 'concluido'])
            ->firstOrFail();

        $courseClass->load([
            'course',
            'teachers',
            'schedules',
            'lessons' => fn ($q) => $q->orderBy('date')->orderBy('start_time'),
            'announcements' => fn ($q) => $q
                ->whereNotNull('reference_date')
                ->whereDate('reference_date', '>=', now()->toDateString())
                ->orderBy('reference_date')
                ->orderByDesc('id')
                ->limit(40),
            'linkedQuizzes' => fn ($q) => $q
                ->where('quizzes.is_active', true)
                ->wherePivot('is_active', true)
                ->orderBy('title'),
        ]);

        $quizPct = AlunoProgress::quizCompletionPercent($student, $courseClass);
        $presencePct = AlunoProgress::attendanceSheetPercent($student, $courseClass);

        return view('aluno.turmas.show', compact('student', 'enrollment', 'courseClass', 'quizPct', 'presencePct'));
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
