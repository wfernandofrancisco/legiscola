<?php

namespace App\Repositories;

use App\Contracts\Repositories\AttendanceRepositoryInterface;
use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AttendanceRepository implements AttendanceRepositoryInterface
{
    public function __construct(protected Attendance $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->with(['student.user', 'course'])->latest('class_date')->paginate($perPage);
    }

    public function findById(int $id): ?Attendance
    {
        return $this->model->query()->with(['student.user', 'course', 'curriculum'])->find($id);
    }

    public function create(array $data): Attendance
    {
        return $this->model->create($data);
    }

    public function update(Attendance $attendance, array $data): bool
    {
        return $attendance->update($data);
    }

    public function delete(Attendance $attendance): bool
    {
        return $attendance->delete();
    }
}
