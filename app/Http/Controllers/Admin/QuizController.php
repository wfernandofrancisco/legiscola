<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\QuizServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreQuizRequest;
use App\Http\Requests\Admin\UpdateQuizRequest;
use App\Models\CourseClass;
use App\Models\Quiz;
use App\Models\TenantAdminSetting;
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

        $quizzes = $this->quizService->listQuizzes(
            filters: $request->only(['search', 'status']),
            perPage: 15
        );

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Quizzes'],
        ];

        return view('admin.quizzes.index', compact('quizzes', 'breadcrumbs'));
    }

    public function create(): View
    {
        $this->authorize('create', Quiz::class);

        $classes = CourseClass::query()->orderBy('name')->get(['id', 'name', 'tipo_turma']);
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Quizzes', 'href' => route('admin.quizzes.index')],
            ['label' => 'Novo quiz'],
        ];

        return view('admin.quizzes.create', compact('classes', 'breadcrumbs'));
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $this->authorize('create', Quiz::class);

        $this->quizService->createQuiz($request->validated());

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz criado com sucesso.');
    }

    public function show(Quiz $quiz): View
    {
        $this->authorize('view', $quiz);
        $quiz->load(['questions.answers', 'courseClasses:id,name,tipo_turma']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Quizzes', 'href' => route('admin.quizzes.index')],
            ['label' => 'Visualizar'],
        ];

        return view('admin.quizzes.show', compact('quiz', 'breadcrumbs'));
    }

    public function edit(Quiz $quiz): View
    {
        $this->authorize('update', $quiz);
        $quiz->load(['questions.answers', 'courseClasses:id,name,tipo_turma']);

        $classes = CourseClass::query()->orderBy('name')->get(['id', 'name', 'tipo_turma']);
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Quizzes', 'href' => route('admin.quizzes.index')],
            ['label' => 'Editar quiz'],
        ];

        return view('admin.quizzes.edit', compact('quiz', 'classes', 'breadcrumbs'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->authorize('update', $quiz);

        $this->quizService->updateQuiz($quiz, $request->validated());

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz atualizado com sucesso.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->authorize('delete', $quiz);

        $this->quizService->deleteQuiz($quiz);

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz removido com sucesso.');
    }

    public function toggleClassStatus(Request $request, Quiz $quiz, CourseClass $courseClass): RedirectResponse
    {
        $this->authorize('update', $quiz);

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
            $candidate = storage_path('app/public/' . $settings->logo_prefeitura_path);
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

        $filename = 'quiz-' . str($quiz->title)->slug() . '.pdf';

        return $pdf->stream($filename);
    }
}
