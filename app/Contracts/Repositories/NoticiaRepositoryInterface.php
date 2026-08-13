<?php

namespace App\Contracts\Repositories;

use App\Models\Noticia;
use App\Models\NoticiaFoto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface NoticiaRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Noticia;

    public function create(array $data): Noticia;

    public function update(Noticia $noticia, array $data): bool;

    public function delete(Noticia $noticia): bool;

    public function createFoto(array $data): NoticiaFoto;

    public function deleteFoto(NoticiaFoto $foto): bool;

    public function nextFotoOrder(int $noticiaId): int;
}
