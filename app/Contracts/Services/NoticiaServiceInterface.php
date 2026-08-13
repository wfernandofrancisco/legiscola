<?php

namespace App\Contracts\Services;

use App\Models\Noticia;
use App\Models\NoticiaFoto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;

interface NoticiaServiceInterface
{
    public function paginateForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findById(int $id): ?Noticia;

    public function create(array $data, array $fotos = []): Noticia;

    public function update(Noticia $noticia, array $data, array $fotos = []): bool;

    public function delete(Noticia $noticia): bool;

    public function deleteFoto(Noticia $noticia, NoticiaFoto $foto): bool;

    public function normalizeTags(?string $tags): ?string;

    public function generateSlug(string $titulo, ?int $ignoreId = null): string;

    public function storeFoto(Noticia $noticia, UploadedFile $file, ?string $legenda = null): NoticiaFoto;

    public function storeCapa(UploadedFile $file): string;
}
