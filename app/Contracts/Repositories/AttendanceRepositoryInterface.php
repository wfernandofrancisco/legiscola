<?php

namespace App\Contracts\Repositories;

use App\Models\Attendance;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface AttendanceRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Attendance;

    public function create(array $data): Attendance;

    public function update(Attendance $attendance, array $data): bool;

    public function delete(Attendance $attendance): bool;
}
