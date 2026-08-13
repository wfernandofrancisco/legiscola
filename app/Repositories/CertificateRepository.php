<?php

namespace App\Repositories;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Models\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CertificateRepository implements CertificateRepositoryInterface
{
    public function __construct(protected Certificate $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->with(['student.user', 'course', 'event'])->latest('issued_at')->paginate($perPage);
    }

    public function findById(int $id): ?Certificate
    {
        return $this->model->query()->with(['student.user', 'course', 'event', 'template'])->find($id);
    }

    public function findByValidationHash(string $hash): ?Certificate
    {
        return $this->model->query()->with(['student.user', 'course', 'event', 'template'])->where('validation_hash', $hash)->first();
    }

    public function create(array $data): Certificate
    {
        return $this->model->create($data);
    }

    public function update(Certificate $certificate, array $data): bool
    {
        return $certificate->update($data);
    }

    public function delete(Certificate $certificate): bool
    {
        return $certificate->delete();
    }
}
