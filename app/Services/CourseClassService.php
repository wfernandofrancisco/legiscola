<?php

namespace App\Services;

use App\Contracts\Repositories\CourseClassRepositoryInterface;
use App\Contracts\Services\CourseClassServiceInterface;
use App\Models\Attendance;
use App\Models\CourseClass;
use App\Models\CourseClassSchedule;
use App\Models\Enrollment;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CourseClassService implements CourseClassServiceInterface
{
    public function __construct(private CourseClassRepositoryInterface $courseClassRepository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return $this->courseClassRepository->paginateFiltered($perPage, $search, $status);
    }

    public function create(array $data): CourseClass
    {
        return DB::transaction(function () use ($data): CourseClass {
            $schedules = $data['schedules'] ?? [];
            if (($data['tipo_turma'] ?? 'presencial') === 'online') {
                $schedules = [];
            }
            unset($data['schedules'], $data['course_search']);
            $teacherIds = $this->normalizeTeacherIds($data['teacher_ids'] ?? []);
            unset($data['teacher_ids']);
            $data['tenant_id'] = TenantContext::getTenantId();
            $data['satisfaction_survey_id'] = $data['satisfaction_survey_id'] ?? null;
            $data['satisfaction_survey_required'] = (bool) ($data['satisfaction_survey_required'] ?? false);
            if (! $data['satisfaction_survey_id']) {
                $data['satisfaction_survey_required'] = false;
            }

            $courseClass = $this->courseClassRepository->create($data);
            $this->syncSchedules($courseClass, $schedules);
            $this->syncTeachers($courseClass, $teacherIds);

            return $courseClass;
        });
    }

    public function update(CourseClass $courseClass, array $data): bool
    {
        return DB::transaction(function () use ($courseClass, $data): bool {
            $schedules = $data['schedules'] ?? [];
            if (($data['tipo_turma'] ?? $courseClass->tipo_turma) === 'online') {
                $schedules = [];
            }
            unset($data['schedules'], $data['course_search']);
            $teacherIds = $this->normalizeTeacherIds($data['teacher_ids'] ?? []);
            unset($data['teacher_ids']);
            $data['satisfaction_survey_id'] = $data['satisfaction_survey_id'] ?? null;
            $data['satisfaction_survey_required'] = (bool) ($data['satisfaction_survey_required'] ?? false);
            if (! $data['satisfaction_survey_id']) {
                $data['satisfaction_survey_required'] = false;
            }
            $updated = $this->courseClassRepository->update($courseClass, $data);
            $this->syncSchedules($courseClass, $schedules);
            $this->syncTeachers($courseClass, $teacherIds);

            return $updated;
        });
    }

    public function delete(CourseClass $courseClass): bool
    {
        return $this->courseClassRepository->delete($courseClass);
    }

    public function completeClass(int $courseClassId, int $minimumAttendance = 75): void
    {
        DB::transaction(function () use ($courseClassId, $minimumAttendance): void {
            $courseClass = $this->courseClassRepository->findById($courseClassId);
            if (! $courseClass) {
                return;
            }

            $lessonIds = $courseClass->lessons->pluck('id');
            $totalLessons = max($lessonIds->count(), 1);

            Enrollment::query()
                ->where('course_class_id', $courseClassId)
                ->chunkById(100, function ($enrollments) use ($lessonIds, $totalLessons, $minimumAttendance): void {
                    foreach ($enrollments as $enrollment) {
                        $presentCount = Attendance::query()
                            ->whereNotNull('class_lesson_id')
                            ->whereIn('class_lesson_id', $lessonIds)
                            ->where('student_id', $enrollment->student_id)
                            ->where('is_present', true)
                            ->count();

                        $percent = ($presentCount / $totalLessons) * 100;
                        $status = $percent >= $minimumAttendance ? 'concluido' : 'baixa_presenca';

                        $enrollment->update(['status' => $status]);
                    }
                });

            $this->courseClassRepository->updateStatus($courseClassId, 'concluido');
        });
    }

    private function syncSchedules(CourseClass $courseClass, array $schedules): void
    {
        $courseClass->schedules()->delete();

        foreach ($schedules as $schedule) {
            if (empty($schedule['weekday']) && $schedule['weekday'] !== 0) {
                continue;
            }
            if (empty($schedule['start_time']) || empty($schedule['end_time'])) {
                continue;
            }
            if ($schedule['start_time'] >= $schedule['end_time']) {
                continue;
            }

            CourseClassSchedule::query()->create([
                'tenant_id' => TenantContext::getTenantId(),
                'course_class_id' => $courseClass->id,
                'weekday' => (int) $schedule['weekday'],
                'start_time' => $schedule['start_time'],
                'end_time' => $schedule['end_time'],
            ]);
        }
    }

    /**
     * @param  array<int, int|string>  $teacherIds
     * @return list<int>
     */
    private function normalizeTeacherIds(array $teacherIds): array
    {
        return collect($teacherIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * @param  list<int>  $teacherIds
     */
    private function syncTeachers(CourseClass $courseClass, array $teacherIds): void
    {
        $tenantId = (int) $courseClass->tenant_id;
        $sync = [];
        foreach ($teacherIds as $i => $teacherId) {
            $sync[(int) $teacherId] = [
                'tenant_id' => $tenantId,
                'sort_order' => (int) $i,
            ];
        }
        $courseClass->teachers()->sync($sync);
    }
}
