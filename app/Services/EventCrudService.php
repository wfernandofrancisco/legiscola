<?php

namespace App\Services;

use App\Contracts\Repositories\EventRepositoryInterface;
use App\Contracts\Services\EventCrudServiceInterface;
use App\Models\Event;
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
        return $this->eventRepository->create($this->normalize($data, true, null));
    }

    public function update(Event $event, array $data): bool
    {
        return $this->eventRepository->update($event, $this->normalize($data, false, $event));
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
}
