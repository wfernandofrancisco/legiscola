<?php

namespace App\Repositories;

use App\Contracts\Repositories\TeacherRepositoryInterface;
use App\Models\Teacher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeacherRepository implements TeacherRepositoryInterface
{
    public function __construct(private Teacher $model) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['user'])
            ->when($search, function ($query) use ($search): void {
                $query->where('specialities', 'like', "%{$search}%")
                    ->orWhere('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($q) => $q->where('name', 'like', "%{$search}%"));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByEmail(string $email, ?int $exceptTeacherId = null): ?Teacher
    {
        return $this->model->query()
            ->when($exceptTeacherId, fn ($query) => $query->where('id', '!=', $exceptTeacherId))
            ->where('email', $email)
            ->first();
    }

    public function create(array $data): Teacher
    {
        return $this->model->create($data);
    }

    public function update(Teacher $teacher, array $data): bool
    {
        return $teacher->update($data);
    }

    public function delete(Teacher $teacher): bool
    {
        return $teacher->delete();
    }
}
