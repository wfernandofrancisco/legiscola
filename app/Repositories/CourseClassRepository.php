<?php

namespace App\Repositories;

use App\Contracts\Repositories\CourseClassRepositoryInterface;
use App\Models\CourseClass;
use App\Models\Enrollment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CourseClassRepository implements CourseClassRepositoryInterface
{
    public function __construct(private CourseClass $model) {}

    public function findById(int $id): ?CourseClass
    {
        return $this->model->query()->with(['course', 'lessons'])->find($id);
    }

    public function listOpenForEnrollment(): Collection
    {
        $now = CarbonImmutable::now();
        return $this->model->query()
            ->with(['course', 'teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
            ->where('status', 'inscricao')
            ->where('enrollment_start', '<=', $now)
            ->where('enrollment_end', '>=', $now)
            ->latest('id')
            ->get();
    }

    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('course')
            ->when($search, function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): CourseClass
    {
        return $this->model->create($data);
    }

    public function update(CourseClass $courseClass, array $data): bool
    {
        return $courseClass->update($data);
    }

    public function delete(CourseClass $courseClass): bool
    {
        return $courseClass->delete();
    }

    public function countEnrollments(int $courseClassId): int
    {
        return Enrollment::query()
            ->where('course_class_id', $courseClassId)
            ->count();
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (bool) $this->model->query()->whereKey($id)->update(['status' => $status]);
    }
}
