<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class CertificadoController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function index(): View
    {
        $student = $this->requireStudent();

        $completedCourseIds = Enrollment::query()
            ->where('enrollments.student_id', $student->id)
            ->where('enrollments.status', 'concluido')
            ->join('course_classes', 'course_classes.id', '=', 'enrollments.course_class_id')
            ->pluck('course_classes.course_id')
            ->unique()
            ->filter();

        $certificatesQuery = Certificate::query()
            ->where('student_id', $student->id)
            ->where('status', 'issued')
            ->with(['course', 'event'])
            ->orderByDesc('issued_at');

        $certificatesQuery->where(function ($q) use ($completedCourseIds): void {
            $q->whereNotNull('event_id');
            if ($completedCourseIds->isNotEmpty()) {
                $q->orWhereIn('course_id', $completedCourseIds);
            }
        });

        $certificates = $certificatesQuery->get()->map(function (Certificate $certificate) use ($student) {
            $deadline = $this->accessDeadlineFor($certificate, $student);
            $certificate->setAttribute('access_deadline', $deadline);
            $certificate->setAttribute(
                'access_open',
                $deadline === null || now()->lte($deadline)
            );

            return $certificate;
        });

        return view('aluno.certificados.index', compact('student', 'certificates'));
    }

    public function download(Certificate $certificate)
    {
        $student = $this->requireStudent();

        abort_unless(
            (int) $certificate->student_id === (int) $student->id && $certificate->status === 'issued',
            403
        );

        if ($certificate->course_id) {
            $completed = Enrollment::query()
                ->where('enrollments.student_id', $student->id)
                ->where('enrollments.status', 'concluido')
                ->whereHas('courseClass', fn ($q) => $q->where('course_id', $certificate->course_id))
                ->exists();

            abort_unless($completed, 403);
        }

        $deadline = $this->accessDeadlineFor($certificate, $student);
        abort_if($deadline !== null && now()->gt($deadline), 403, 'O prazo para acessar este certificado encerrou.');

        return app(AdminCertificateController::class)->downloadByHash($certificate->validation_hash);
    }

    private function accessDeadlineFor(Certificate $certificate, Student $student): ?Carbon
    {
        if ($certificate->event_id) {
            $certificate->loadMissing('event');

            return $certificate->event?->certificado_disponivel_ate;
        }

        if (! $certificate->course_id) {
            return null;
        }

        /** @var CourseClass|null $courseClass */
        $courseClass = CourseClass::query()
            ->where('course_id', $certificate->course_id)
            ->whereHas('enrollments', function ($q) use ($student): void {
                $q->where('student_id', $student->id)
                    ->where('status', 'concluido');
            })
            ->orderByDesc('certificado_disponivel_ate')
            ->first();

        return $courseClass?->certificado_disponivel_ate;
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
