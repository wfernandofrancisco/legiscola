<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\ClassLesson;
use App\Models\Enrollment;
use App\Models\Student;
use App\Support\AlunoProgress;
use App\Support\YoutubeId;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AulaController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function show(int $classLesson): View
    {
        $student = $this->requireStudent();
        $classLesson = $this->resolveEnrolledLesson($student, $classLesson);

        $classLesson->load('courseClass.course');

        $courseClass = $classLesson->courseClass;
        $youtubeId = null;
        if ($classLesson->video_url && YoutubeId::isYoutube($classLesson->video_url)) {
            $youtubeId = YoutubeId::fromUrl($classLesson->video_url);
        }

        $quizPct = $courseClass ? AlunoProgress::quizCompletionPercent($student, $courseClass) : null;
        $presencePct = $courseClass ? AlunoProgress::attendanceSheetPercent($student, $courseClass) : null;

        $canMarkOnlinePresence = $courseClass
            && ($courseClass->tipo_turma === 'online' || $classLesson->is_online);

        $onlinePresenceConfirmed = $canMarkOnlinePresence
            && Attendance::query()
                ->where('student_id', $student->id)
                ->where('class_lesson_id', $classLesson->id)
                ->where('is_present', true)
                ->exists();

        return view('aluno.aulas.show', compact(
            'student',
            'classLesson',
            'youtubeId',
            'quizPct',
            'presencePct',
            'canMarkOnlinePresence',
            'onlinePresenceConfirmed'
        ));
    }

    public function storePresence(int $classLesson): RedirectResponse
    {
        $student = $this->requireStudent();
        $classLesson = $this->resolveEnrolledLesson($student, $classLesson);

        $classLesson->load('courseClass');
        $courseClass = $classLesson->courseClass;
        abort_unless(
            $courseClass && ($courseClass->tipo_turma === 'online' || $classLesson->is_online),
            403,
            'Presença nesta tela só é permitida para turmas ou aulas online.'
        );

        $date = $classLesson->date?->toDateString() ?? now()->toDateString();

        Attendance::query()->updateOrCreate(
            [
                'tenant_id' => (int) $student->tenant_id,
                'student_id' => $student->id,
                'class_lesson_id' => $classLesson->id,
            ],
            [
                'course_id' => $courseClass->course_id,
                'class_date' => $date,
                'status' => 'presente',
                'is_present' => true,
                'recorded_by_user_id' => (int) auth()->id(),
            ]
        );

        return back()->with('success', 'Presença registrada nesta aula.');
    }

    public function downloadMaterial(int $classLesson): Response
    {
        $student = $this->requireStudent();
        $classLesson = $this->resolveEnrolledLesson($student, $classLesson);

        $path = $classLesson->material_file_path;
        abort_unless($path && Storage::disk('public')->exists($path), 404);

        $name = $classLesson->material_file_name ?: basename($path);

        return Storage::disk('public')->download($path, $name);
    }

    private function resolveEnrolledLesson(Student $student, int $lessonId): ClassLesson
    {
        $classLesson = ClassLesson::findByIdIgnoringTenantScope($lessonId);
        $this->assertEnrolledInLessonClass($student, $classLesson);

        return $classLesson;
    }

    private function assertEnrolledInLessonClass(Student $student, ClassLesson $classLesson): void
    {
        Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_class_id', $classLesson->course_class_id)
            ->whereIn('status', Enrollment::STATUSES_ALUNO_ACESSO)
            ->firstOrFail();
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
