<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\ClassLesson;
use App\Models\CourseClassAnnouncement;
use App\Models\Enrollment;
use App\Support\AlunoProgress;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $student = $this->studentService->findByUserId((int) $user->id);

        if (! $student) {
            return view('aluno.dashboard', [
                'user' => $user,
                'student' => null,
                'announcements' => collect(),
                'upcomingLessons' => collect(),
                'enrollmentSnapshots' => collect(),
            ]);
        }

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['cursando', 'concluido'])
            ->with([
                'courseClass.course',
                'courseClass.lessons' => fn ($q) => $q->orderBy('date')->orderBy('start_time'),
                'courseClass.schedules',
            ])
            ->get();

        $classIds = $enrollments->pluck('course_class_id')->filter()->unique()->values();

        $start = now()->startOfDay();
        $end = now()->addDays(14)->endOfDay();

        $announcements = collect();
        $upcomingLessons = collect();

        if ($classIds->isNotEmpty()) {
            $announcements = CourseClassAnnouncement::query()
                ->whereIn('course_class_id', $classIds)
                ->with('courseClass:id,name,course_id')
                ->whereNotNull('reference_date')
                ->whereBetween('reference_date', [$start->toDateString(), $end->copy()->toDateString()])
                ->orderBy('reference_date')
                ->orderByDesc('id')
                ->limit(30)
                ->get();

            $upcomingLessons = ClassLesson::query()
                ->whereIn('course_class_id', $classIds)
                ->with('courseClass:id,name')
                ->whereDate('date', '>=', $start->toDateString())
                ->orderBy('date')
                ->orderBy('start_time')
                ->limit(12)
                ->get();
        }

        $enrollmentSnapshots = $enrollments->map(function (Enrollment $e) use ($student) {
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

        return view('aluno.dashboard', compact(
            'user',
            'student',
            'announcements',
            'upcomingLessons',
            'enrollmentSnapshots'
        ));
    }
}
