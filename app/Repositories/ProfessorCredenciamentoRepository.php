<?php

namespace App\Repositories;

use App\Contracts\Repositories\ProfessorCredenciamentoRepositoryInterface;
use App\Models\ProfessorCredenciamento;
use App\Models\ProfessorCredenciamentoAnexo;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProfessorCredenciamentoRepository implements ProfessorCredenciamentoRepositoryInterface
{
    public function __construct(
        private ProfessorCredenciamento $model,
        private ProfessorCredenciamentoAnexo $anexoModel
    ) {}

    public function paginateFiltered(int $perPage = 15, ?string $search = null): LengthAwarePaginator
    {
        $search = $search !== null ? trim($search) : null;

        return $this->model->query()
            ->with('anexos')
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('titulo', 'like', "%{$search}%")
                        ->orWhere('texto', 'like', "%{$search}%")
                        ->orWhere('ano_referencia', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?ProfessorCredenciamento
    {
        return $this->model->query()->with('anexos')->find($id);
    }

    public function create(array $data): ProfessorCredenciamento
    {
        return $this->model->create($data);
    }

    public function update(ProfessorCredenciamento $credenciamento, array $data): bool
    {
        return $credenciamento->update($data);
    }

    public function delete(ProfessorCredenciamento $credenciamento): bool
    {
        return $credenciamento->delete();
    }

    public function createAnexo(array $data): ProfessorCredenciamentoAnexo
    {
        return $this->anexoModel->create($data);
    }

    public function deleteAnexo(ProfessorCredenciamentoAnexo $anexo): bool
    {
        return $anexo->delete();
    }
}
