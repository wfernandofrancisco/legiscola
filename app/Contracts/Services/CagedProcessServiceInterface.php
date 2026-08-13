<?php

namespace App\Contracts\Services;

use Illuminate\Support\Collection;

interface CagedProcessServiceInterface
{
    /**
     * @return array{
     *     processRuns: Collection,
     *     tenants: Collection,
     *     domainCounts: Collection,
     *     recentMovimentacoes: Collection,
     *     manualDefaultPath: string
     * }
     */
    public function indexData(): array;

    public function enqueueImportFromManualFolder(?string $manualDirRelative, ?int $requestedBy, bool $syncLayout = true): void;
}
