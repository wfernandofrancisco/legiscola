<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Admin\CertificateController as AdminCertificateController;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Student;
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

        $certificates = $certificatesQuery->get();

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

        return app(AdminCertificateController::class)->downloadByHash($certificate->validation_hash);
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
