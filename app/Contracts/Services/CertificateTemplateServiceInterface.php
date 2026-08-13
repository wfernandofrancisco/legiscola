<?php

namespace App\Contracts\Services;

use App\Models\CertificateTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CertificateTemplateServiceInterface
{
    public function paginate(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?CertificateTemplate;

    public function create(array $data): CertificateTemplate;

    public function update(CertificateTemplate $template, array $data): bool;

    public function delete(CertificateTemplate $template): bool;
}
