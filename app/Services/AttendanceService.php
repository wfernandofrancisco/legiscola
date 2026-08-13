<?php

namespace App\Services;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Contracts\Services\AttendanceServiceInterface;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class AttendanceService implements AttendanceServiceInterface
{
    public function __construct(protected AttendanceRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Attendance
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): Attendance
    {
        $data['recorded_by_user_id'] = auth()->id();
        return $this->repository->create($data);
    }

    public function update(Attendance $attendance, array $data): bool
    {
        return $this->repository->update($attendance, $data);
    }

    public function delete(Attendance $attendance): bool
    {
        return $this->repository->delete($attendance);
    }

    public function registrarPresencasEmLote(int $classScheduleId, array $presencas): void
    {
        $schedule = ClassSchedule::query()->with('turma.enrollments')->findOrFail($classScheduleId);
        $matriculados = $schedule->turma->enrollments->pluck('student_id')->all();

        foreach ($presencas as $item) {
            if (! in_array((int) $item['student_id'], $matriculados, true)) {
                throw ValidationException::withMessages([
                    'presencas' => 'Aluno informado não está matriculado nesta turma.',
                ]);
            }

            Attendance::query()->updateOrCreate(
                [
                    'tenant_id' => TenantContext::getTenantId(),
                    'class_schedule_id' => $classScheduleId,
                    'student_id' => (int) $item['student_id'],
                ],
                [
                    'course_id' => $schedule->turma->course_id,
                    'class_date' => $schedule->date,
                    'status' => $item['is_present'] ? 'presente' : 'falta',
                    'is_present' => (bool) $item['is_present'],
                    'recorded_by_user_id' => auth()->id(),
                ]
            );
        }
    }
}
