<?php

namespace App\Contracts\Services;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttendanceServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Attendance;

    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): bool;

    public function delete(Attendance $attendance): bool;

    /** @param array<int, array{student_id:int,is_present:bool}> $presencas */
    public function registrarPresencasEmLote(int $classScheduleId, array $presencas): void;
}
