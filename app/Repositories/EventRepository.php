<?php

namespace App\Repositories;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Models\Event;
use App\Models\EventEnrollment;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EventRepository implements EventRepositoryInterface
{
    public function __construct(private Event $model) {}

    public function findById(int $id): ?Event
    {
        return $this->model->query()->find($id);
    }

    public function listOpenForEnrollment(): Collection
    {
        $now = CarbonImmutable::now();

        return $this->model->query()
            ->where('allow_online_registration', true)
            ->where('date_time', '>=', $now)
            ->where(function ($q) use ($now): void {
                $q->where(function ($inner) use ($now): void {
                    $inner->where('registration_starts_at', '<=', $now)
                        ->where('registration_ends_at', '>=', $now);
                })->orWhere(function ($inner): void {
                    $inner->whereNull('registration_starts_at')
                        ->whereNull('registration_ends_at');
                });
            })
            ->latest('date_time')
            ->get();
    }

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->model->query()
            ->withCount('enrollments')
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->latest('date_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Event
    {
        return $this->model->create($data);
    }

    public function update(Event $event, array $data): bool
    {
        return $event->update($data);
    }

    public function delete(Event $event): bool
    {
        return $event->delete();
    }

    public function countEnrollments(int $eventId): int
    {
        return EventEnrollment::query()->where('event_id', $eventId)->count();
    }
}
