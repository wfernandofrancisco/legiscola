<?php

namespace App\Repositories;

use App\Contracts\Repositories\NoticiaRepositoryInterface;
use App\Models\Noticia;
use App\Models\NoticiaFoto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class NoticiaRepository implements NoticiaRepositoryInterface
{
    public function __construct(
        protected Noticia $model,
        protected NoticiaFoto $fotoModel,
    ) {
    }

    public function paginateForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['user', 'fotos'])
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q
                    ->where('titulo', 'like', "%{$search}%")
                    ->orWhere('subtitulo', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%")
            )
            ->when(
                isset($filters['ativo']) && $filters['ativo'] !== '',
                fn ($q) => $q->where('ativo', (bool) $filters['ativo'])
            )
            ->latest('publicar_em')
            ->latest()
            ->paginate($perPage);
    }

    public function findById(int $id): ?Noticia
    {
        return $this->model->with(['user', 'fotos'])->find($id);
    }

    public function create(array $data): Noticia
    {
        return $this->model->create($data);
    }

    public function update(Noticia $noticia, array $data): bool
    {
        return $noticia->update($data);
    }

    public function delete(Noticia $noticia): bool
    {
        return $noticia->delete();
    }

    public function createFoto(array $data): NoticiaFoto
    {
        return $this->fotoModel->create($data);
    }

    public function deleteFoto(NoticiaFoto $foto): bool
    {
        return $foto->delete();
    }

    public function nextFotoOrder(int $noticiaId): int
    {
        $lastOrder = $this->fotoModel->query()
            ->where('noticia_id', $noticiaId)
            ->max('ordem');

        return ((int) $lastOrder) + 1;
    }
}
