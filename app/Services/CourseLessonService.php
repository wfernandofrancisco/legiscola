<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseLesson;
use App\Support\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CourseLessonService
{
    public function create(Course $course, array $data): CourseLesson
    {
        $lesson = new CourseLesson($this->attributes($data));
        $lesson->course_id = $course->id;
        $lesson->tenant_id = $course->tenant_id ?: TenantContext::getTenantId();
        $lesson->ordem = $data['ordem'] ?? ((int) $course->lessons()->max('ordem') + 1);

        if (($video = $data['video_file'] ?? null) instanceof UploadedFile) {
            $this->attachVideo($lesson, $video);
        }

        if (($material = $data['material_file'] ?? null) instanceof UploadedFile) {
            $this->attachMaterial($lesson, $material);
        }

        $lesson->save();

        return $lesson;
    }

    public function update(CourseLesson $lesson, array $data): CourseLesson
    {
        $lesson->fill($this->attributes($data));

        if (($video = $data['video_file'] ?? null) instanceof UploadedFile) {
            $this->deleteFile($lesson->video_path);
            $this->attachVideo($lesson, $video);
        } elseif (! empty($data['remove_video'])) {
            $this->deleteFile($lesson->video_path);
            $lesson->video_path = null;
        }

        if (($material = $data['material_file'] ?? null) instanceof UploadedFile) {
            $this->deleteFile($lesson->material_file_path);
            $this->attachMaterial($lesson, $material);
        } elseif (! empty($data['remove_material'])) {
            $this->deleteFile($lesson->material_file_path);
            $lesson->material_file_path = null;
            $lesson->material_file_name = null;
        }

        $lesson->save();

        return $lesson;
    }

    public function delete(CourseLesson $lesson): void
    {
        $this->deleteFile($lesson->video_path);
        $this->deleteFile($lesson->material_file_path);
        $lesson->delete();
    }

    /**
     * @param  list<int>  $orderedIds
     */
    public function reorder(Course $course, array $orderedIds): void
    {
        DB::transaction(function () use ($course, $orderedIds): void {
            foreach (array_values($orderedIds) as $posicao => $lessonId) {
                $course->lessons()->whereKey($lessonId)->update(['ordem' => $posicao + 1]);
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(array $data): array
    {
        return [
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'video_url' => trim((string) ($data['video_url'] ?? '')) ?: null,
            'material_url' => trim((string) ($data['material_url'] ?? '')) ?: null,
        ];
    }

    private function attachVideo(CourseLesson $lesson, UploadedFile $file): void
    {
        $lesson->video_path = $file->store('course-lessons/'.$lesson->course_id.'/videos', 'public');
    }

    private function attachMaterial(CourseLesson $lesson, UploadedFile $file): void
    {
        $lesson->material_file_path = $file->store('course-lessons/'.$lesson->course_id.'/materials', 'public');
        $lesson->material_file_name = $file->getClientOriginalName();
    }

    private function deleteFile(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
