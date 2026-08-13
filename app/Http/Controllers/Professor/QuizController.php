<?php

namespace App\Http\Controllers\Professor;

use App\Contracts\Services\QuizServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\CourseClass;
use App\Models\Quiz;
use App\Models\TenantAdminSetting;
use App\Support\ProfessorContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function __construct(private QuizServiceInterface $quizService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quiz::class);

        $ids = ProfessorContext::assignedCourseClassIds();
        $search = $request->string('search')->toString() ?: null;
        $status = $request->input('status');

        $quizzes = Quiz::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount('questions')
            ->with(['courseClasses:id,name'])
            ->where(function ($q) use ($ids): void {
                if ($ids === []) {
                    $q->whereRaw('1 = 0');

                    return;
                }
                $q->whereIn('course_class_id', $ids)
                    ->orWhereHas('courseClasses', fn ($cq) => $cq->whereIn('course_classes.id', $ids));
            })
            ->when($search, fn ($q) => $q->where('title', 'like', '%'.$search.'%'))
            ->when($status !== null && $status !== '', fn ($q) => $q->where('is_active', (bool) $status))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Quizzes'],
        ];

        return view('professor.quizzes.index', compact('quizzes', 'breadcrumbs'));
    }

    public function create(): View
    {
        $this->authorize('create', Quiz::class);

        $ids = ProfessorContext::assignedCourseClassIds();
        $classes = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'tipo_turma']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Quizzes', 'href' => route('professor.quizzes.index')],
            ['label' => 'Novo quiz'],
        ];

        return view('professor.quizzes.create', compact('classes', 'breadcrumbs'));
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $this->authorize('create', Quiz::class);
        $this->assertQuizClassesAllowed($request->input('course_class_ids', []));

        $this->quizService->createQuiz($request->validated());

        return redirect()->route('professor.quizzes.index')->with('success', 'Quiz criado com sucesso.');
    }

    public function show(Quiz $quiz): View
    {
        $this->authorize('view', $quiz);
        $quiz->load(['questions.answers', 'courseClasses:id,name,tipo_turma']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Quizzes', 'href' => route('professor.quizzes.index')],
            ['label' => 'Visualizar'],
        ];

        return view('professor.quizzes.show', compact('quiz', 'breadcrumbs'));
    }

    public function edit(Quiz $quiz): View
    {
        $this->authorize('update', $quiz);
        $quiz->load(['questions.answers', 'courseClasses:id,name,tipo_turma']);

        $ids = ProfessorContext::assignedCourseClassIds();
        $classes = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'tipo_turma']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Quizzes', 'href' => route('professor.quizzes.index')],
            ['label' => 'Editar quiz'],
        ];

        return view('professor.quizzes.edit', compact('quiz', 'classes', 'breadcrumbs'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);
        $this->assertQuizClassesAllowed($request->input('course_class_ids', []));

        $this->quizService->updateQuiz($quiz, $request->validated());

        return redirect()->route('professor.quizzes.index')->with('success', 'Quiz atualizado com sucesso.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        abort(403);
    }

    public function toggleClassStatus(Request $request, Quiz $quiz, CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('update', $quiz);
        $this->authorize('interactAsAssignedProfessor', $courseClass);

        $validated = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        $this->quizService->toggleClassStatus($quiz, $courseClass->id, (bool) $validated['is_active']);

        return back()->with('success', 'Status da turma no quiz atualizado.');
    }

    public function print(Quiz $quiz)
    {
        $this->authorize('view', $quiz);
        $quiz->load(['questions.answers', 'courseClasses:id,name']);
        $tenant = auth()->user()->tenant()->first();
        $settings = TenantAdminSetting::query()->where('tenant_id', auth()->user()->tenant_id)->first();

        $logoPath = null;
        if (! empty($settings?->logo_prefeitura_path)) {
            $candidate = storage_path('app/public/'.$settings->logo_prefeitura_path);
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $pdf = Pdf::loadView('admin.quizzes.print', [
            'quiz' => $quiz,
            'tenant' => $tenant,
            'logoPath' => $logoPath,
            'printedBy' => auth()->user()?->name,
            'printedAt' => now(),
        ])->setPaper('a4', 'portrait');

        $filename = 'quiz-'.str($quiz->title)->slug().'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * @param  array<int|string>  $courseClassIds
     */
    private function assertQuizClassesAllowed(array $courseClassIds): void
    {
        $allowed = ProfessorContext::assignedCourseClassIds();
        foreach ($courseClassIds as $id) {
            abort_unless(in_array((int) $id, $allowed, true), 403, 'Turma não permitida para este docente.');
        }
    }
}
