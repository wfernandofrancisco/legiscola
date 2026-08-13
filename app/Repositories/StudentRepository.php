<?php

namespace App\Repositories;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class StudentRepository implements StudentRepositoryInterface
{
    public function __construct(protected Student $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->with('user')->latest()->paginate($perPage);
    }

    public function findById(int $id): ?Student
    {
        return $this->model->query()->with('user')->find($id);
    }

    public function findByUserId(int $userId): ?Student
    {
        return $this->model->query()->where('user_id', $userId)->first();
    }

    public function findByEmail(string $email): ?Student
    {
        return $this->model->query()->where('email', $email)->first();
    }

    public function findByCpf(string $cpf): ?Student
    {
        return $this->model->query()->where('cpf', $cpf)->first();
    }

    public function create(array $data): Student
    {
        return $this->model->create($data);
    }

    public function update(Student $student, array $data): bool
    {
        return $student->update($data);
    }

    public function delete(Student $student): bool
    {
        return $student->delete();
    }
}
