<?php

namespace App\Repositories;

use App\Contracts\Repositories\ExamTemplateRepositoryInterface;
use App\Models\ExamTemplate;

class ExamTemplateRepository implements ExamTemplateRepositoryInterface
{
    public function __construct(private ExamTemplate $model) {}

    public function create(array $data): ExamTemplate
    {
        return $this->model->create($data);
    }

    public function findById(int $id): ?ExamTemplate
    {
        return $this->model->query()->with(['questions', 'attachments', 'turma.course'])->find($id);
    }
}
