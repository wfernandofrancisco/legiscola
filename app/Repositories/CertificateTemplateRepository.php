<?php

namespace App\Repositories;

use App\Contracts\Repositories\CertificateTemplateRepositoryInterface;
use App\Models\CertificateTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CertificateTemplateRepository implements CertificateTemplateRepositoryInterface
{
    public function __construct(protected CertificateTemplate $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()->latest()->paginate($perPage);
    }

    public function findById(int $id): ?CertificateTemplate
    {
        return $this->model->find($id);
    }

    public function create(array $data): CertificateTemplate
    {
        return $this->model->create($data);
    }

    public function update(CertificateTemplate $template, array $data): bool
    {
        return $template->update($data);
    }

    public function delete(CertificateTemplate $template): bool
    {
        return $template->delete();
    }
}
