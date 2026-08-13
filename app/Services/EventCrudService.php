<?php

namespace App\Services;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\EventCrudServiceInterface;
use App\Models\Event;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventCrudService implements EventCrudServiceInterface
{
    public function __construct(private EventRepositoryInterface $eventRepository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->eventRepository->paginateFiltered($perPage, $search);
    }

    public function create(array $data): Event
    {
        $data['tenant_id'] = TenantContext::getTenantId();
        $data['allow_online_registration'] = (bool) ($data['allow_online_registration'] ?? false);
        $data['com_certificado'] = (bool) ($data['com_certificado'] ?? false);
        if (! $data['allow_online_registration']) {
            $data['registration_starts_at'] = null;
            $data['registration_ends_at'] = null;
        }

        return $this->eventRepository->create($data);
    }

    public function update(Event $event, array $data): bool
    {
        $data['allow_online_registration'] = (bool) ($data['allow_online_registration'] ?? false);
        $data['com_certificado'] = (bool) ($data['com_certificado'] ?? false);
        if (! $data['allow_online_registration']) {
            $data['registration_starts_at'] = null;
            $data['registration_ends_at'] = null;
        }

        return $this->eventRepository->update($event, $data);
    }

    public function delete(Event $event): bool
    {
        return $this->eventRepository->delete($event);
    }
}
