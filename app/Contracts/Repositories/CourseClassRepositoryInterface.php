<?php

namespace App\Contracts\Repositories;

use App\Models\CourseClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CourseClassRepositoryInterface
{
    public function findById(int $id): ?CourseClass;

    public function listOpenForEnrollment(): Collection;
    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;
    public function create(array $data): CourseClass;
    public function update(CourseClass $courseClass, array $data): bool;
    public function delete(CourseClass $courseClass): bool;

    public function countEnrollments(int $courseClassId): int;

    public function updateStatus(int $id, string $status): bool;
}
