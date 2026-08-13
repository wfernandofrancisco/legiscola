<?php

namespace App\Repositories;

use App\Contracts\Repositories\ClassLessonRepositoryInterface;
use App\Models\ClassLesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ClassLessonRepository implements ClassLessonRepositoryInterface
{
    public function __construct(private ClassLesson $model) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?int $courseClassId = null, ?array $onlyCourseClassIds = null): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('courseClass')
            ->when($onlyCourseClassIds !== null, function ($query) use ($onlyCourseClassIds): void {
                if ($onlyCourseClassIds === []) {
                    $query->whereRaw('1 = 0');
                } else {
                    $query->whereIn('course_class_id', $onlyCourseClassIds);
                }
            })
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($courseClassId, fn ($query) => $query->where('course_class_id', $courseClassId))
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): ClassLesson
    {
        return $this->model->create($data);
    }

    public function update(ClassLesson $classLesson, array $data): bool
    {
        return $classLesson->update($data);
    }

    public function delete(ClassLesson $classLesson): bool
    {
        return $classLesson->delete();
    }
}
