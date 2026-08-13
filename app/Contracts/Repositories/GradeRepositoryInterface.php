<?php

namespace App\Contracts\Repositories;

use App\Models\Grade;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface GradeRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Grade;

    public function create(array $data): Grade;

    public function update(Grade $grade, array $data): bool;

    public function delete(Grade $grade): bool;
}
