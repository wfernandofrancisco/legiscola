<?php

namespace App\Services;

use App\Contracts\Repositories\CertificateRepositoryInterface;
use App\Contracts\Services\CertificateServiceInterface;
use App\Models\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class CertificateService implements CertificateServiceInterface
{
    public function __construct(protected CertificateRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?Certificate
    {
        return $this->repository->findById($id);
    }

    public function findByValidationHash(string $hash): ?Certificate
    {
        return $this->repository->findByValidationHash($hash);
    }

    public function issue(array $data): Certificate
    {
        $data['validation_hash'] = hash('sha256', Str::uuid() . now()->timestamp);
        $data['issued_at'] = now();
        $data['status'] = 'issued';

        return $this->repository->create($data);
    }

    public function revoke(Certificate $certificate, string $reason): bool
    {
        $snapshot = $certificate->snapshot ?? [];
        $snapshot['revocation_reason'] = $reason;

        return $this->repository->update($certificate, [
            'status' => 'revoked',
            'snapshot' => $snapshot,
        ]);
    }
}
