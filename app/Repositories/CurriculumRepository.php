<?php

namespace App\Repositories;

use App\Contracts\Repositories\CurriculumRepositoryInterface;
use App\Models\Curriculum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CurriculumRepository implements CurriculumRepositoryInterface
{
    public function __construct(protected Curriculum $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->with('course')->orderBy('position')->paginate($perPage);
    }

    public function findById(int $id): ?Curriculum
    {
        return $this->model->query()->with(['course', 'responsible'])->find($id);
    }

    public function create(array $data): Curriculum
    {
        return $this->model->create($data);
    }

    public function update(Curriculum $curriculum, array $data): bool
    {
        return $curriculum->update($data);
    }

    public function delete(Curriculum $curriculum): bool
    {
        return $curriculum->delete();
    }
}
