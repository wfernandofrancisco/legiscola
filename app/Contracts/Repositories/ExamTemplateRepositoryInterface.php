<?php

namespace App\Contracts\Repositories;

use App\Models\ExamTemplate;

interface ExamTemplateRepositoryInterface
{
    public function create(array $data): ExamTemplate;

    public function findById(int $id): ?ExamTemplate;
}
