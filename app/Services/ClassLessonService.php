<?php

namespace App\Services;

use App\Contracts\Repositories\ClassLessonRepositoryInterface;
use App\Contracts\Services\ClassLessonServiceInterface;
use App\Models\ClassLesson;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
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
        $videoFile = $data['video_file'] ?? null;
        $materialFile = $data['material_file'] ?? null;
        unset($data['video_file'], $data['material_file'], $data['remove_video'], $data['remove_material_file']);

        $data['tenant_id'] = TenantContext::getTenantId();
        $data['is_online'] = (bool) ($data['is_online'] ?? false);
        $data['video_url'] = $this->normalizeNullableUrl($data['video_url'] ?? null);

        $lesson = $this->classLessonRepository->create($data);

        $dirty = false;
        if ($videoFile instanceof UploadedFile) {
            $this->attachVideo($lesson, $videoFile);
            $dirty = true;
        }
        if ($materialFile instanceof UploadedFile) {
            $this->attachMaterial($lesson, $materialFile);
            $dirty = true;
        }
        if ($dirty) {
            $lesson->save();
        }

        return $lesson;
    }

    public function update(ClassLesson $classLesson, array $data): bool
    {
        $videoFile = $data['video_file'] ?? null;
        $materialFile = $data['material_file'] ?? null;
        $removeVideo = ! empty($data['remove_video']);
        $removeMaterial = ! empty($data['remove_material_file']);
        unset($data['video_file'], $data['material_file'], $data['remove_video'], $data['remove_material_file']);

        $data['is_online'] = (bool) ($data['is_online'] ?? false);
        if (array_key_exists('video_url', $data)) {
            $data['video_url'] = $this->normalizeNullableUrl($data['video_url']);
        }

        if ($videoFile instanceof UploadedFile) {
            $this->deleteFile($classLesson->video_path);
            $this->attachVideo($classLesson, $videoFile);
            $data['video_path'] = $classLesson->video_path;
        } elseif ($removeVideo) {
            $this->deleteFile($classLesson->video_path);
            $data['video_path'] = null;
        }

        if ($materialFile instanceof UploadedFile) {
            $this->deleteFile($classLesson->material_file_path);
            $this->attachMaterial($classLesson, $materialFile);
            $data['material_file_path'] = $classLesson->material_file_path;
            $data['material_file_name'] = $classLesson->material_file_name;
        } elseif ($removeMaterial) {
            $this->deleteFile($classLesson->material_file_path);
            $data['material_file_path'] = null;
            $data['material_file_name'] = null;
        }

        return $this->classLessonRepository->update($classLesson, $data);
    }

    public function delete(ClassLesson $classLesson): bool
    {
        $this->deleteFile($classLesson->material_file_path);
        $this->deleteFile($classLesson->video_path);

        return $this->classLessonRepository->delete($classLesson);
    }

    private function attachVideo(ClassLesson $lesson, UploadedFile $file): void
    {
        $tenantId = (int) ($lesson->tenant_id ?: TenantContext::getTenantId() ?: 0);
        $lesson->video_path = $file->store('class-lessons/'.$tenantId.'/videos', 'public');
    }

    private function attachMaterial(ClassLesson $lesson, UploadedFile $file): void
    {
        $tenantId = (int) ($lesson->tenant_id ?: TenantContext::getTenantId() ?: 0);
        $lesson->material_file_path = $file->store('class-lessons/'.$tenantId, 'public');
        $lesson->material_file_name = $file->getClientOriginalName();
    }

    private function deleteFile(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function normalizeNullableUrl(mixed $value): ?string
    {
        $url = trim((string) $value);

        return $url !== '' ? $url : null;
    }
}
