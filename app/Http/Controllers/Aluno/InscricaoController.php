<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\EventEnrollment;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InscricaoController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService,
        private EnrollmentServiceInterface $enrollmentService,
    ) {}

    public function index(): View
    {
        $student = $this->requireStudent();
        $now = now();

        $courseClasses = CourseClass::query()
            ->visibleOnPortal()
            ->with(['course'])
            ->withCount([
                'enrollments as matriculas_count' => fn ($query) => $query->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->where('status', 'inscricao')
            ->whereNotNull('enrollment_start')
            ->whereNotNull('enrollment_end')
            ->where('enrollment_start', '<=', $now)
            ->where('enrollment_end', '>=', $now)
            ->orderBy('enrollment_end')
            ->get();

        $events = Event::query()
            ->visibleOnPortal()
            ->where('allow_online_registration', true)
            ->where('date_time', '>=', $now->copy()->startOfDay())
            ->where(function ($query) use ($now): void {
                $query->where(function ($inner) use ($now): void {
                    $inner->where('registration_starts_at', '<=', $now)
                        ->where('registration_ends_at', '>=', $now);
                })->orWhere(function ($inner): void {
                    $inner->whereNull('registration_starts_at')
                        ->whereNull('registration_ends_at');
                });
            })
            ->orderBy('date_time')
            ->get();

        $classEnrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->pluck('status', 'course_class_id')
            ->all();

        $eventEnrollments = EventEnrollment::query()
            ->where('student_id', $student->id)
            ->pluck('event_id')
            ->all();

        return view('aluno.inscricoes.index', compact(
            'student',
            'courseClasses',
            'events',
            'classEnrollments',
            'eventEnrollments',
        ));
    }

    public function storeTurma(CourseClass $courseClass): RedirectResponse
    {
        $student = $this->requireStudent();

        try {
            $this->enrollmentService->matricularEmTurma((int) $student->id, (int) $courseClass->id);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Inscrição na turma registrada com sucesso.');
    }

    public function storeEvento(Event $evento): RedirectResponse
    {
        $student = $this->requireStudent();

        try {
            $this->enrollmentService->inscreverEmEvento((int) $student->id, (int) $evento->id);
        } catch (ValidationException $exception) {
            return back()->with('error', collect($exception->errors())->flatten()->first());
        }

        return back()->with('success', 'Inscrição no evento registrada com sucesso.');
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
