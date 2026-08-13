<?php

namespace App\Services;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Services\CourseServiceInterface;
use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CourseService implements CourseServiceInterface
{
    public function __construct(protected CourseRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Course
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Course
    {
        $data['admin_user_id'] = auth()->id();
        return $this->repository->create($data);
    }

    public function update(Course $course, array $data): bool
    {
        return $this->repository->update($course, $data);
    }

    public function delete(Course $course): bool
    {
        return $this->repository->delete($course);
    }
}
