<?php

namespace App\Contracts\Repositories;

use App\Models\Quiz;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface QuizRepositoryInterface
{
    public function paginateByTenant(int $tenantId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    public function createQuiz(array $data): Quiz;

    public function updateQuiz(Quiz $quiz, array $data): Quiz;

    public function syncCourseClasses(Quiz $quiz, array $courseClassIds, bool $isActive): void;

    public function replaceQuestions(Quiz $quiz, array $questions): void;

    public function deleteQuiz(Quiz $quiz): void;
}
