<?php

namespace App\Services;

use App\Contracts\Repositories\QuizRepositoryInterface;
use App\Contracts\Services\QuizServiceInterface;
use App\Models\Quiz;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class QuizService implements QuizServiceInterface
{
    public function __construct(private QuizRepositoryInterface $quizRepository) {}

    public function listQuizzes(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->quizRepository->paginateByTenant(
            tenantId: TenantContext::getTenantId(),
            perPage: $perPage,
            search: $filters['search'] ?? null,
            status: $filters['status'] ?? null
        );
    }

    public function createQuiz(array $payload): Quiz
    {
        return DB::transaction(function () use ($payload): Quiz {
            $quiz = $this->quizRepository->createQuiz([
                'tenant_id' => TenantContext::getTenantId(),
                'course_class_id' => $payload['course_class_ids'][0],
                'title' => $payload['title'],
                'min_score_to_pass' => $payload['min_score_to_pass'],
                'is_active' => (bool) ($payload['is_active'] ?? false),
            ]);

            $this->quizRepository->syncCourseClasses(
                $quiz,
                $payload['course_class_ids'],
                (bool) ($payload['is_active'] ?? false)
            );

            $this->quizRepository->replaceQuestions($quiz, $payload['questions']);

            return $quiz;
        });
    }

    public function updateQuiz(Quiz $quiz, array $payload): Quiz
    {
        return DB::transaction(function () use ($quiz, $payload): Quiz {
            $updatedQuiz = $this->quizRepository->updateQuiz($quiz, [
                'course_class_id' => $payload['course_class_ids'][0],
                'title' => $payload['title'],
                'min_score_to_pass' => $payload['min_score_to_pass'],
                'is_active' => (bool) ($payload['is_active'] ?? false),
            ]);

            $this->quizRepository->syncCourseClasses(
                $updatedQuiz,
                $payload['course_class_ids'],
                (bool) ($payload['is_active'] ?? false)
            );

            $this->quizRepository->replaceQuestions($updatedQuiz, $payload['questions']);

            return $updatedQuiz;
        });
    }

    public function deleteQuiz(Quiz $quiz): void
    {
        $this->quizRepository->deleteQuiz($quiz);
    }

    public function toggleClassStatus(Quiz $quiz, int $courseClassId, bool $isActive): void
    {
        $quiz->courseClasses()->updateExistingPivot($courseClassId, [
            'is_active' => $isActive,
        ]);
    }
}
