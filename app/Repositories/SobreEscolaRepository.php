<?php

namespace App\Repositories;

use App\Contracts\Repositories\SobreEscolaRepositoryInterface;
use App\Models\SobreEscola;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SobreEscolaRepository implements SobreEscolaRepositoryInterface
{
    public function __construct(private SobreEscola $model) {}

    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['eixos', 'pessoas'])
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?SobreEscola
    {
        return $this->model->query()->with(['eixos', 'pessoas'])->find($id);
    }

    public function create(array $data): SobreEscola
    {
        return $this->model->create($data);
    }

    public function update(SobreEscola $sobreEscola, array $data): bool
    {
        return $sobreEscola->update($data);
    }

    public function delete(SobreEscola $sobreEscola): bool
    {
        return $sobreEscola->delete();
    }
}
