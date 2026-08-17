<?php

namespace App\Http\Controllers\Aluno;

use App\Contracts\Services\StudentServiceInterface;
use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\SatisfactionSurvey;
use App\Models\SatisfactionSurveyAnswer;
use App\Models\SatisfactionSurveyResponse;
use App\Models\Student;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PesquisaSatisfacaoController extends Controller
{
    public function __construct(
        private StudentServiceInterface $studentService
    ) {}

    public function index(): View
    {
        $student = $this->requireStudent();

        $enrollments = Enrollment::query()
            ->where('student_id', $student->id)
            ->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca'])
            ->with(['courseClass.course', 'courseClass.satisfactionSurvey'])
            ->latest('id')
            ->get()
            ->filter(fn (Enrollment $e) => $e->courseClass?->satisfaction_survey_id);

        $completedKeys = SatisfactionSurveyResponse::query()
            ->where('student_id', $student->id)
            ->get(['satisfaction_survey_id', 'course_class_id'])
            ->mapWithKeys(fn ($r) => [$r->satisfaction_survey_id.':'.$r->course_class_id => true])
            ->all();

        $items = $enrollments->map(function (Enrollment $enrollment) use ($completedKeys) {
            $turma = $enrollment->courseClass;
            $key = $turma->satisfaction_survey_id.':'.$turma->id;

            return [
                'enrollment' => $enrollment,
                'turma' => $turma,
                'survey' => $turma->satisfactionSurvey,
                'completed' => isset($completedKeys[$key]),
                'required' => (bool) $turma->satisfaction_survey_required,
            ];
        })->filter(fn ($row) => $row['survey'] && $row['survey']->is_active)->values();

        return view('aluno.pesquisas-satisfacao.index', compact('student', 'items'));
    }

    public function show(CourseClass $turma): View
    {
        $student = $this->requireStudent();
        $this->assertEnrolled($student, $turma);

        abort_unless($turma->satisfaction_survey_id, 404);

        $survey = SatisfactionSurvey::query()
            ->with(['questions.options'])
            ->findOrFail($turma->satisfaction_survey_id);

        abort_unless($survey->is_active, 404);

        $alreadyAnswered = $turma->studentCompletedSatisfactionSurvey($student->id);

        return view('aluno.pesquisas-satisfacao.show', [
            'student' => $student,
            'turma' => $turma->load('course'),
            'survey' => $survey,
            'alreadyAnswered' => $alreadyAnswered,
        ]);
    }

    public function store(Request $request, CourseClass $turma): RedirectResponse
    {
        $student = $this->requireStudent();
        $this->assertEnrolled($student, $turma);

        abort_unless($turma->satisfaction_survey_id, 404);

        if ($turma->studentCompletedSatisfactionSurvey($student->id)) {
            return redirect()
                ->route('app.pesquisas-satisfacao.show', $turma)
                ->with('info', 'Você já respondeu esta pesquisa.');
        }

        $survey = SatisfactionSurvey::query()
            ->with(['questions.options'])
            ->findOrFail($turma->satisfaction_survey_id);

        abort_unless($survey->is_active, 404);

        $answers = (array) $request->input('answers', []);
        $errors = [];

        foreach ($survey->questions as $question) {
            $payload = $answers[$question->id] ?? null;

            if ($question->isFreeText()) {
                $text = trim((string) ($payload['free_text'] ?? $payload ?? ''));
                if ($text === '') {
                    $errors["answers.{$question->id}"] = 'Responda a pergunta: '.$question->question;
                }
            } else {
                $optionId = (int) (is_array($payload) ? ($payload['option_id'] ?? 0) : $payload);
                $valid = $question->options->contains(fn ($opt) => (int) $opt->id === $optionId);
                if (! $valid) {
                    $errors["answers.{$question->id}"] = 'Selecione uma opção para: '.$question->question;
                }
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        DB::transaction(function () use ($survey, $turma, $student, $answers): void {
            $tenantId = (int) ($student->tenant_id ?: TenantContext::getTenantId());

            $response = SatisfactionSurveyResponse::query()->create([
                'tenant_id' => $tenantId,
                'satisfaction_survey_id' => $survey->id,
                'course_class_id' => $turma->id,
                'student_id' => $student->id,
                'submitted_at' => now(),
            ]);

            foreach ($survey->questions as $question) {
                $payload = $answers[$question->id] ?? null;

                if ($question->isFreeText()) {
                    SatisfactionSurveyAnswer::query()->create([
                        'tenant_id' => $tenantId,
                        'satisfaction_survey_response_id' => $response->id,
                        'satisfaction_survey_question_id' => $question->id,
                        'satisfaction_survey_option_id' => null,
                        'free_text' => trim((string) ($payload['free_text'] ?? $payload ?? '')),
                    ]);
                } else {
                    SatisfactionSurveyAnswer::query()->create([
                        'tenant_id' => $tenantId,
                        'satisfaction_survey_response_id' => $response->id,
                        'satisfaction_survey_question_id' => $question->id,
                        'satisfaction_survey_option_id' => (int) (is_array($payload) ? ($payload['option_id'] ?? 0) : $payload),
                        'free_text' => null,
                    ]);
                }
            }
        });

        return redirect()
            ->route('app.pesquisas-satisfacao.index')
            ->with('success', 'Pesquisa enviada. Obrigado pela sua avaliação!');
    }

    private function assertEnrolled(Student $student, CourseClass $turma): void
    {
        Enrollment::query()
            ->where('student_id', $student->id)
            ->where('course_class_id', $turma->id)
            ->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca'])
            ->firstOrFail();
    }

    private function requireStudent(): Student
    {
        $student = $this->studentService->findByUserId((int) auth()->id());
        abort_unless($student instanceof Student, 404);

        return $student;
    }
}
