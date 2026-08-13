<?php

namespace App\Contracts\Repositories;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Student;

    public function findByUserId(int $userId): ?Student;

    public function findByEmail(string $email): ?Student;

    public function findByCpf(string $cpf): ?Student;

    public function create(array $data): Student;

    public function update(Student $student, array $data): bool;

    public function delete(Student $student): bool;
}
