<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface CnpjProcessServiceInterface
{
    /**
     * @return array{processRuns:Collection,tenants:Collection}
     */
    public function indexData(): array;

    public function enqueueImport(array $data, ?int $requestedBy = null): void;

    public function enqueueNormalize(array $data, ?int $requestedBy = null): void;

    public function enqueueGeocode(array $data, ?int $requestedBy = null): void;
}
