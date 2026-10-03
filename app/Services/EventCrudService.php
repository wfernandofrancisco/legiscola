<?php

namespace App\Services;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\EventCrudServiceInterface;
use App\Models\Event;
use App\Models\EventPalestra;
use App\Support\TenantContext;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class EventCrudService implements EventCrudServiceInterface
{
    public function __construct(private EventRepositoryInterface $eventRepository) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        return $this->eventRepository->paginateFiltered($perPage, $search);
    }

    public function create(array $data): Event
    {
        $palestras = $data['palestras'] ?? [];
        unset($data['palestras']);
        if (! filled($data['date_time'] ?? null) && is_array($palestras)) {
            $first = collect($palestras)->first(fn ($row) => filled($row['date_time'] ?? null));
            if ($first) {
                $data['date_time'] = $first['date_time'];
            }
        }

        $event = $this->eventRepository->create($this->normalize($data, true, null));
        $this->syncPalestras($event, is_array($palestras) ? $palestras : []);

        return $event->fresh(['palestras']);
    }

    public function update(Event $event, array $data): bool
    {
        $palestras = $data['palestras'] ?? [];
        unset($data['palestras']);
        if (! filled($data['date_time'] ?? null) && is_array($palestras)) {
            $first = collect($palestras)->first(fn ($row) => filled($row['date_time'] ?? null));
            if ($first) {
                $data['date_time'] = $first['date_time'];
            } else {
                unset($data['date_time']);
            }
        }
        $this->syncPalestras($event, is_array($palestras) ? $palestras : []);

        return $ok;
    }

    public function delete(Event $event): bool
    {
        return $this->eventRepository->delete($event);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalize(array $data, bool $creating, ?Event $event): array
    {
        if ($creating) {
            $data['tenant_id'] = TenantContext::getTenantId();
        }

        unset($data['photo']);

        $data['allow_online_registration'] = (bool) ($data['allow_online_registration'] ?? false);
        $data['com_certificado'] = (bool) ($data['com_certificado'] ?? false);
        $data['chamada_georreferencia'] = (bool) ($data['chamada_georreferencia'] ?? false);
        $data['certificado_disponivel_ate'] = $data['certificado_disponivel_ate'] ?? null;

        if (! $data['allow_online_registration']) {
            $data['registration_starts_at'] = null;
            $data['registration_ends_at'] = null;
        }

        if (! $data['chamada_georreferencia']) {
            $data['latitude'] = null;
            $data['longitude'] = null;
            $data['geofence_raio_metros'] = null;
            $data['presenca_inicio_em'] = null;
            $data['presenca_fim_em'] = null;
        }

        $speakerName = trim((string) ($data['palestrante_nome'] ?? ''));
        if ($speakerName === '') {
            $data['palestrante_nome'] = null;
            $data['palestrante_cpf'] = null;
            $data['palestrante_senha'] = null;
        } else {
            $data['palestrante_nome'] = $speakerName;
            $data['palestrante_cpf'] = $data['palestrante_cpf'] ?? null;

            $plainPassword = $data['palestrante_senha'] ?? null;
            if (filled($plainPassword)) {
                $data['palestrante_senha'] = Hash::make((string) $plainPassword);
            } elseif ($event && filled($event->palestrante_senha)) {
                unset($data['palestrante_senha']);
            } else {
                $data['palestrante_senha'] = null;
            }
        }

        return $data;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncPalestras(Event $event, array $rows): void
    {
        $keepIds = [];
        $firstDate = null;

        foreach (array_values($rows) as $index => $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $dateTime = $row['date_time'] ?? null;
            if ($title === '' || ! filled($dateTime)) {
                continue;
            }

            $existing = null;
            if (! empty($row['id'])) {
                $existing = EventPalestra::query()
                    ->where('event_id', $event->id)
                    ->whereKey((int) $row['id'])
                    ->first();
            }

            $payload = [
                'tenant_id' => $event->tenant_id,
                'event_id' => $event->id,
                'ordem' => (int) ($row['ordem'] ?? $index + 1),
                'title' => $title,
                'date_time' => $dateTime,
                'com_certificado' => (bool) ($row['com_certificado'] ?? false),
                'palestrante_nome' => filled($row['palestrante_nome'] ?? null) ? trim((string) $row['palestrante_nome']) : null,
                'palestrante_cpf' => $row['palestrante_cpf'] ?? null,
            ];

            if (! $payload['palestrante_nome']) {
                $payload['palestrante_cpf'] = null;
                $payload['palestrante_senha'] = null;
            } elseif (filled($row['palestrante_senha'] ?? null)) {
                $payload['palestrante_senha'] = Hash::make((string) $row['palestrante_senha']);
            } elseif ($existing && filled($existing->palestrante_senha)) {
                unset($payload['palestrante_senha']);
            } else {
                $payload['palestrante_senha'] = null;
            }

            $palestra = $existing
                ? tap($existing, fn (EventPalestra $model) => $model->fill($payload)->save())
                : EventPalestra::query()->create($payload);

            $keepIds[] = $palestra->id;

            $at = $palestra->date_time;
            if ($at && ($firstDate === null || $at->lt($firstDate))) {
                $firstDate = $at;
            }
        }

        EventPalestra::query()
            ->where('event_id', $event->id)
            ->when($keepIds !== [], fn ($q) => $q->whereNotIn('id', $keepIds), fn ($q) => $q)
            ->delete();

        if ($firstDate) {
            $event->forceFill(['date_time' => $firstDate])->save();
        }
    }
}
