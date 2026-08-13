<?php

namespace App\Services;

use App\Contracts\Repositories\GradeRepositoryInterface;
use App\Contracts\Services\GradeServiceInterface;
use App\Models\Grade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class GradeService implements GradeServiceInterface
{
    public function __construct(protected GradeRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Grade
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Grade
    {
        $data['graded_by_user_id'] = auth()->id();
        return $this->repository->create($data);
    }

    public function update(Grade $grade, array $data): bool
    {
        return $this->repository->update($grade, $data);
    }

    public function delete(Grade $grade): bool
    {
        return $this->repository->delete($grade);
    }
}
