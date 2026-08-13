<?php

namespace App\Contracts\Services;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeacherServiceInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(array $data): Teacher;
    public function update(Teacher $teacher, array $data): bool;
    public function delete(Teacher $teacher): bool;
}
