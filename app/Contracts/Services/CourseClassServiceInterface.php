<?php

namespace App\Contracts\Services;

use App\Models\CourseClass;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CourseClassServiceInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;
    public function create(array $data): CourseClass;
    public function update(CourseClass $courseClass, array $data): bool;
    public function delete(CourseClass $courseClass): bool;
    public function completeClass(int $courseClassId, int $minimumAttendance = 75): void;
}
