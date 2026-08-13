<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAttemptAnswer;
use App\Models\Student;
use App\Support\CourseClassQuizAvailability;
use App\Support\TenantContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class QuizController extends Controller
{
    public function index(): View
    {
        $student = Student::query()->where('user_id', auth()->id())->firstOrFail();

        $courseClassIds = Enrollment::query()
            ->where('student_id', $student->id)
            ->pluck('course_class_id')
            ->filter()
            ->values();

        $quizzes = Quiz::query()
            ->with([
                'courseClasses' => fn ($query) => $query
                    ->whereIn('course_classes.id', $courseClassIds)
                    ->select('course_classes.id', 'course_classes.name'),
                'attempts' => fn ($query) => $query->where('student_id', $student->id)->latest(),
            ])
            ->whereHas('courseClasses', function ($query) use ($courseClassIds): void {
                $query->whereIn('course_classes.id', $courseClassIds)
                    ->where('course_class_quiz.is_active', true);
            })
            ->where('is_active', true)
            ->orderByDesc('id')
            ->get();

        return view('app.quizzes.index', compact('quizzes', 'courseClassIds'));
    }

    public function show(Quiz $quiz): View|RedirectResponse
    {
        $student = Student::query()->where('user_id', auth()->id())->firstOrFail();
        [$courseClass, $pivot] = $this->resolveStudentQuizAccess($quiz, $student->id);

        if (! CourseClassQuizAvailability::isOpenNow($pivot->opens_at, $pivot->closes_at)) {
            return redirect()->route('app.quizzes.index')
                ->with('error', 'Este quiz não está no período de disponibilidade configurado para a sua turma.');
        }

        $quiz->load('questions.answers');

        return view('app.quizzes.show', compact('quiz', 'courseClass'));
    }

    public function submit(Request $request, Quiz $quiz): RedirectResponse
    {
        $student = Student::query()->where('user_id', auth()->id())->firstOrFail();
        [$courseClass, $pivot] = $this->resolveStudentQuizAccess($quiz, $student->id);

        if (! CourseClassQuizAvailability::isOpenNow($pivot->opens_at, $pivot->closes_at)) {
            return redirect()->route('app.quizzes.index')
                ->with('error', 'O prazo deste quiz já encerrou ou ainda não começou.');
        }

        $quiz->load('questions.answers');

        $rules = [];
        foreach ($quiz->questions as $question) {
            $rules['answers.' . $question->id] = ['required', 'integer'];
        }
        $validated = $request->validate($rules);

        DB::transaction(function () use ($quiz, $courseClass, $student, $validated): void {
            $tenantId = TenantContext::getTenantId();
            $correctAnswers = 0;
            $totalQuestions = $quiz->questions->count();

            $attempt = QuizAttempt::query()->create([
                'tenant_id' => $tenantId,
                'quiz_id' => $quiz->id,
                'course_class_id' => $courseClass->id,
                'student_id' => $student->id,
                'submitted_at' => now(),
            ]);

            foreach ($quiz->questions as $question) {
                $selectedAnswerId = (int) ($validated['answers'][$question->id] ?? 0);
                $selectedAnswer = $question->answers->firstWhere('id', $selectedAnswerId);
                $isCorrect = (bool) ($selectedAnswer?->is_correct ?? false);

                if ($isCorrect) {
                    $correctAnswers++;
                }

                QuizAttemptAnswer::query()->create([
                    'tenant_id' => $tenantId,
                    'quiz_attempt_id' => $attempt->id,
                    'quiz_question_id' => $question->id,
                    'quiz_answer_id' => $selectedAnswer?->id,
                    'is_correct' => $isCorrect,
                ]);
            }

            $score = $totalQuestions > 0 ? round(($correctAnswers / $totalQuestions) * 100, 2) : 0;

            $attempt->update([
                'score' => $score,
                'correct_answers' => $correctAnswers,
                'total_questions' => $totalQuestions,
                'passed' => $score >= (float) $quiz->min_score_to_pass,
            ]);
        });

        return redirect()->route('app.quizzes.index')
            ->with('success', 'Quiz enviado com sucesso.');
    }

    /**
     * @return array{0: CourseClass, 1: object}
     */
    private function resolveStudentQuizAccess(Quiz $quiz, int $studentId): array
    {
        $courseClass = Enrollment::query()
            ->where('student_id', $studentId)
            ->whereIn('course_class_id', function ($query) use ($quiz): void {
                $query->from('course_class_quiz')
                    ->select('course_class_id')
                    ->where('quiz_id', $quiz->id)
                    ->where('is_active', true);
            })
            ->with('courseClass:id,name,tipo_turma')
            ->firstOrFail()
            ?->courseClass;

        abort_unless($quiz->is_active && $courseClass instanceof CourseClass, 404);

        $link = $courseClass->linkedQuizzes()->where('quizzes.id', $quiz->id)->first();
        abort_unless($link, 404);

        return [$courseClass, $link->pivot];
    }
}
