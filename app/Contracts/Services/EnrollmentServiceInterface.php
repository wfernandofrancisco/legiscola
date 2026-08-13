<?php

namespace App\Contracts\Services;

use App\Models\Enrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EnrollmentServiceInterface
{
    public function matricularEmTurma(int $studentId, int $courseClassId): Enrollment;
    public function matricularEmTurmaAdmin(int $studentId, int $courseClassId, string $status = 'inscrito', ?string $observations = null): Enrollment;

    public function inscreverEmEvento(int $studentId, int $eventId): void;

    public function paginateByCourseClass(int $courseClassId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator;

    public function statusSummary(int $courseClassId): array;

    public function updateStatus(Enrollment $enrollment, string $status, ?string $observations = null): bool;
}
