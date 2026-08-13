<?php

namespace App\Services;

use App\Contracts\Repositories\CertificateTemplateRepositoryInterface;
use App\Contracts\Services\CertificateTemplateServiceInterface;
use App\Models\CertificateTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CertificateTemplateService implements CertificateTemplateServiceInterface
{
    public function __construct(protected CertificateTemplateRepositoryInterface $repository) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage);
    }

    public function findById(int $id): ?CertificateTemplate
    {
        return $this->repository->findById($id);
    }

    public function create(array $data): CertificateTemplate
    {
        return $this->repository->create($data);
    }

    public function update(CertificateTemplate $template, array $data): bool
    {
        return $this->repository->update($template, $data);
    }

    public function delete(CertificateTemplate $template): bool
    {
        return $this->repository->delete($template);
    }
}
