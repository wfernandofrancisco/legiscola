<?php

namespace App\Contracts\Repositories;

use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface EventRepositoryInterface
{
    public function findById(int $id): ?Event;

    public function listOpenForEnrollment(): Collection;
    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator;
    public function create(array $data): Event;
    public function update(Event $event, array $data): bool;
    public function delete(Event $event): bool;

    public function countEnrollments(int $eventId): int;
}
