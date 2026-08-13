<?php

namespace App\Contracts\Repositories;

use App\Models\CentralProcessRun;
use Illuminate\Support\Collection;

interface CentralProcessRunRepositoryInterface
{
    public function latestWithRequester(int $limit = 30): Collection;

    /**
     * @return Collection<int, CentralProcessRun>
     */
    public function latestByType(string $type, int $limit = 30): Collection;

    public function create(array $data): CentralProcessRun;
}
