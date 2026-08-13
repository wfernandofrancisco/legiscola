<?php

namespace App\Contracts\Repositories;

use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeacherRepositoryInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function findByEmail(string $email, ?int $exceptTeacherId = null): ?Teacher;
    public function create(array $data): Teacher;
    public function update(Teacher $teacher, array $data): bool;
    public function delete(Teacher $teacher): bool;
}
