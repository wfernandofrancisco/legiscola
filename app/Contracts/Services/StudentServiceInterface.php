<?php

namespace App\Contracts\Services;

use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StudentServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Student;

    public function findByUserId(int $userId): ?Student;

    public function create(array $data): Student;

    /**
     * Reusa aluno já cadastrado (e-mail ou CPF) ou cria usuário + aluno.
     *
     * @param  array<string, mixed>  $data
     */
    public function findOrCreateForManualEnrollment(array $data): Student;

    public function update(Student $student, array $data): bool;

    public function delete(Student $student): bool;
}
