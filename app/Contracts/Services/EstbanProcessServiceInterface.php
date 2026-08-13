<?php

namespace App\Contracts\Services;

interface EstbanProcessServiceInterface
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array;

    public function enqueueImportFromManualYear(
        ?string $manualDirRelative,
        ?int $requestedBy,
        int $ano,
    ): void;
}
