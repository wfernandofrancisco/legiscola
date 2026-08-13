<?php

namespace App\Services;

use App\Contracts\Repositories\ClassLessonRepositoryInterface;
use App\Contracts\Services\ClassLessonServiceInterface;
use App\Models\ClassLesson;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ClassLessonService implements ClassLessonServiceInterface
{
    public function __construct(private ClassLessonRepositoryInterface $classLessonRepository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?int $courseClassId = null, ?array $onlyCourseClassIds = null): LengthAwarePaginator
    {
        return $this->classLessonRepository->paginateFiltered($perPage, $search, $courseClassId, $onlyCourseClassIds);
    }

    public function create(array $data): ClassLesson
    {
        $data['tenant_id'] = TenantContext::getTenantId();
        $data['is_online'] = (bool) ($data['is_online'] ?? false);
        return $this->classLessonRepository->create($data);
    }

    public function update(ClassLesson $classLesson, array $data): bool
    {
        $data['is_online'] = (bool) ($data['is_online'] ?? false);
        return $this->classLessonRepository->update($classLesson, $data);
    }

    public function delete(ClassLesson $classLesson): bool
    {
        if ($classLesson->material_file_path) {
            Storage::disk('public')->delete($classLesson->material_file_path);
        }

        return $this->classLessonRepository->delete($classLesson);
    }
}
