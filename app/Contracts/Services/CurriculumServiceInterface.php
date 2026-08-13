<?php

namespace App\Contracts\Services;

use App\Models\Curriculum;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CurriculumServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Curriculum;

    public function create(array $data): Curriculum;

    public function update(Curriculum $curriculum, array $data): bool;

    public function delete(Curriculum $curriculum): bool;
}
