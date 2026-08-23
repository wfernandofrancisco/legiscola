<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreStudentRequest;
use App\Http\Requests\Escola\UpdateStudentRequest;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\EventEnrollment;
use App\Models\Student;
use App\Support\CourseClassAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(private StudentServiceInterface $service) {}

    public function index(Request $request): View
    {
        $students = Student::query()
            ->with('user')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where('enrollment_number', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($q) use ($search): void {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Alunos'],
        ];

        return view('admin.students.index', compact('students', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Alunos', 'href' => route('admin.alunos.index')],
            ['label' => 'Novo aluno'],
        ];
        return view('admin.students.create', compact('breadcrumbs'));
    }

    public function edit(Student $student): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Alunos', 'href' => route('admin.alunos.index')],
            ['label' => 'Editar aluno'],
        ];
        return view('admin.students.edit', compact('student', 'breadcrumbs'));
    }

    public function historico(Student $student): View
    {
        $student->load('user');

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->whereHas('courseClass')
            ->with([
                'courseClass.course',
                'courseClass.lessons' => fn ($q) => $q->orderBy('date')->orderBy('start_time'),
            ])
            ->latest('id')
            ->paginate(8, ['*'], 'cursos')
            ->withQueryString();

        $lessonIds = collect($enrollments->items())
            ->pluck('courseClass.lessons')
            ->flatten()
            ->pluck('id')
            ->filter()
            ->unique()
            ->values();

        $attendanceByLesson = $lessonIds->isEmpty()
            ? collect()
            : Attendance::query()
                ->where('student_id', $student->id)
                ->whereIn('class_lesson_id', $lessonIds)
                ->get()
                ->keyBy('class_lesson_id');

        $turmaRows = collect($enrollments->items())->map(function (Enrollment $enrollment) use ($student, $attendanceByLesson) {
            $turma = $enrollment->courseClass;
            $lessons = $turma->lessons->map(function ($lesson) use ($attendanceByLesson) {
                $att = $attendanceByLesson->get($lesson->id);

                return [
                    'id' => $lesson->id,
                    'title' => $lesson->title,
                    'date' => $lesson->date?->format('d/m/Y'),
                    'start' => $lesson->start_time ? substr((string) $lesson->start_time, 0, 5) : null,
                    'end' => $lesson->end_time ? substr((string) $lesson->end_time, 0, 5) : null,
                    'present' => $att?->is_present,
                    'hasRecord' => $att !== null,
                ];
            });

            return [
                'id' => $enrollment->id,
                'status' => $enrollment->status,
                'course' => $turma->course?->name ?? 'Curso',
                'turma' => $turma->name,
                'turmaId' => $turma->id,
                'presencePct' => CourseClassAttendance::studentPercent($student, $turma),
                'lessonCount' => $lessons->count(),
                'lessons' => $lessons,
            ];
        });

        $eventEnrollments = EventEnrollment::query()
            ->where('student_id', $student->id)
            ->whereHas('event')
            ->with('event')
            ->latest('id')
            ->paginate(8, ['*'], 'eventos')
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Alunos', 'href' => route('admin.alunos.index')],
            ['label' => 'Histórico'],
        ];

        return view('admin.students.historico', compact(
            'student',
            'enrollments',
            'turmaRows',
            'eventEnrollments',
            'breadcrumbs',
        ));
    }

    public function store(StoreStudentRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return back()->with('success', 'Aluno cadastrado com sucesso.');
    }

    public function update(UpdateStudentRequest $request, Student $student): RedirectResponse
    {
        $this->service->update($student, $request->validated());

        return back()->with('success', 'Aluno atualizado com sucesso.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $this->service->delete($student);
        return back()->with('success', 'Aluno removido com sucesso.');
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        $results = Student::query()
            ->with('user:id,name,email')
            ->where('status', 'ativo')
            ->whereHas('user', fn ($q) => $q->where('status', 'ativo'))
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
}
