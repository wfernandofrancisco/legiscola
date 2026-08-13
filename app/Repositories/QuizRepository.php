<?php

namespace App\Repositories;

use App\Contracts\Repositories\QuizRepositoryInterface;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QuizRepository implements QuizRepositoryInterface
{
    public function __construct(private Quiz $model) {}

    public function paginateByTenant(int $tenantId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->with(['questions.answers', 'courseClasses:id,name,tipo_turma'])
            ->where('tenant_id', $tenantId)
            ->when($search, fn ($query) => $query->where('title', 'like', '%' . $search . '%'))
            ->when($status !== null && $status !== '', fn ($query) => $query->where('is_active', (bool) $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createQuiz(array $data): Quiz
    {
        return $this->model->create($data);
    }

    public function updateQuiz(Quiz $quiz, array $data): Quiz
    {
        $quiz->update($data);

        return $quiz->refresh();
    }

    public function syncCourseClasses(Quiz $quiz, array $courseClassIds, bool $isActive): void
    {
        $existing = DB::table('course_class_quiz')
            ->where('quiz_id', $quiz->id)
            ->get()
            ->keyBy('course_class_id');

        $syncData = [];
        foreach ($courseClassIds as $courseClassId) {
            $id = (int) $courseClassId;
            $prev = $existing->get($id);
            $syncData[$id] = [
                'tenant_id' => TenantContext::getTenantId(),
                'is_active' => $isActive,
                'opens_at' => $prev?->opens_at,
                'closes_at' => $prev?->closes_at,
            ];
        }

        $quiz->courseClasses()->sync($syncData);
    }

    public function replaceQuestions(Quiz $quiz, array $questions): void
    {
        $quiz->questions()->delete();

        foreach ($questions as $questionIndex => $questionData) {
            $question = QuizQuestion::query()->create([
                'tenant_id' => TenantContext::getTenantId(),
                'quiz_id' => $quiz->id,
                'question' => $questionData['text'],
                'position' => $questionIndex + 1,
            ]);

            foreach ($questionData['answers'] as $answerIndex => $answerData) {
                $question->answers()->create([
                    'tenant_id' => TenantContext::getTenantId(),
                    'answer' => $answerData['text'],
                    'is_correct' => $answerIndex === (int) $questionData['correct_answer'],
                    'position' => $answerIndex + 1,
                ]);
            }
        }
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        $quiz->delete();
    }
}
