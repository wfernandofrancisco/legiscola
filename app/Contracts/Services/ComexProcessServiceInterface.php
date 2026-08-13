<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface ComexProcessServiceInterface
{
    /**
     * @return array{
     *     processRuns: Collection,
     *     tenants: Collection,
     *     domainCounts: Collection<string, int>,
     *     manualDefaultPath: string
     * }
     */
    public function indexData(): array;

    public function enqueueImportFromManualFolder(
        ?string $manualDirRelative,
        ?int $requestedBy,
        int $coAno,
        bool $syncPaises = true,
        bool $syncSh = true,
    ): void;
}
