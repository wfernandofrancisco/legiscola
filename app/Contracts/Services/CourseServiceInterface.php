<?php

namespace App\Contracts\Services;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Course;

    public function create(array $data): Course;

    public function update(Course $course, array $data): bool;

    public function delete(Course $course): bool;
}
