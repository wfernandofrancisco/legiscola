<?php

namespace App\Contracts\Repositories;

use App\Models\Enrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EnrollmentRepositoryInterface
{
    public function findByStudentAndCourseClass(int $studentId, int $courseClassId): ?Enrollment;

    public function countByCourseClass(int $courseClassId): int;

    public function paginateByCourseClass(int $courseClassId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    public function countByStatus(int $courseClassId, string $status): int;

    public function updateEnrollment(Enrollment $enrollment, array $data): bool;

    public function create(array $data): Enrollment;
}
