<?php

namespace App\Services;

use App\Contracts\Repositories\CurriculumRepositoryInterface;
use App\Contracts\Services\CurriculumServiceInterface;
use App\Models\Curriculum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CurriculumService implements CurriculumServiceInterface
{
    public function __construct(protected CurriculumRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Curriculum
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Curriculum
    {
        return $this->repository->create($data);
    }

    public function update(Curriculum $curriculum, array $data): bool
    {
        return $this->repository->update($curriculum, $data);
    }

    public function delete(Curriculum $curriculum): bool
    {
        return $this->repository->delete($curriculum);
    }
}
