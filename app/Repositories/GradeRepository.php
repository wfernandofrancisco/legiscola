<?php

namespace App\Repositories;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Models\Grade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GradeRepository implements GradeRepositoryInterface
{
    public function __construct(protected Grade $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->with(['student.user', 'course', 'curriculum'])->latest('evaluated_at')->paginate($perPage);
    }

    public function findById(int $id): ?Grade
    {
        return $this->model->query()->with(['student.user', 'course', 'curriculum'])->find($id);
    }

    public function create(array $data): Grade
    {
        return $this->model->create($data);
    }

    public function update(Grade $grade, array $data): bool
    {
        return $grade->update($data);
    }

    public function delete(Grade $grade): bool
    {
        return $grade->delete();
    }
}
