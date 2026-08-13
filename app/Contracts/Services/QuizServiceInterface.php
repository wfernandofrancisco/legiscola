<?php

namespace App\Contracts\Services;

use App\Models\Quiz;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface QuizServiceInterface
{
    public function listQuizzes(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function createQuiz(array $payload): Quiz;

    public function updateQuiz(Quiz $quiz, array $payload): Quiz;

    public function deleteQuiz(Quiz $quiz): void;

    public function toggleClassStatus(Quiz $quiz, int $courseClassId, bool $isActive): void;
}
