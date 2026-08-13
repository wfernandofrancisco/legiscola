<?php

namespace App\Contracts\Services;

use App\Models\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CertificateServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Certificate;

    public function findByValidationHash(string $hash): ?Certificate;

    public function issue(array $data): Certificate;

    public function revoke(Certificate $certificate, string $reason): bool;
}
