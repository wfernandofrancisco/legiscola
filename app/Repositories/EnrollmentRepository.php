<?php

namespace App\Repositories;

use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Models\CourseClass;
use App\Models\Enrollment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EnrollmentRepository implements EnrollmentRepositoryInterface
{
    public function __construct(private Enrollment $model) {}

    public function findByStudentAndCourseClass(int $studentId, int $courseClassId): ?Enrollment
    {
        return $this->model->query()
            ->where('student_id', $studentId)
            ->where('course_class_id', $courseClassId)
            ->first();
    }

    public function countByCourseClass(int $courseClassId): int
    {
        return $this->model->query()
            ->where('course_class_id', $courseClassId)
            ->whereIn('status', ['inscrito', 'cursando'])
            ->count();
    }

    public function paginateByCourseClass(int $courseClassId, int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        $tenantId = CourseClass::query()->whereKey($courseClassId)->value('tenant_id');

        $base = $this->model->query()->where('course_class_id', $courseClassId);

        if ($tenantId !== null) {
            $base->withStudentForAttendanceSheet((int) $tenantId);
        } else {
            $base->with(['student.user']);
        }

        return $base
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($q) use ($search): void {
                    $q->whereHas('student', function ($studentQuery) use ($search): void {
                        $studentQuery->where('email', 'like', "%{$search}%");
                    })->orWhereHas('student.user', function ($userQuery) use ($search): void {
                        $userQuery->where(function ($uq) use ($search): void {
                            $uq->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    });
                });
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByStatus(int $courseClassId, string $status): int
    {
        return $this->model->query()
            ->where('course_class_id', $courseClassId)
            ->where('status', $status)
            ->count();
    }

    public function updateEnrollment(Enrollment $enrollment, array $data): bool
    {
        return $enrollment->update($data);
    }

    public function create(array $data): Enrollment
    {
        return $this->model->create($data);
    }
}
