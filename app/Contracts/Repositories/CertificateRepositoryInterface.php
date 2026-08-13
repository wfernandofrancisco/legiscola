<?php

namespace App\Contracts\Repositories;

use App\Models\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CertificateRepositoryInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Certificate;

    public function findByValidationHash(string $hash): ?Certificate;

    public function create(array $data): Certificate;

    public function update(Certificate $certificate, array $data): bool;

    public function delete(Certificate $certificate): bool;
}
