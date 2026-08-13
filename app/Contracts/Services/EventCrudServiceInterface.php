<?php

namespace App\Contracts\Services;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface EventCrudServiceInterface
{
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(array $data): Event;
    public function update(Event $event, array $data): bool;
    public function delete(Event $event): bool;
}
