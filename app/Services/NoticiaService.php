<?php

namespace App\Services;

use App\Contracts\Repositories\NoticiaRepositoryInterface;
use App\Contracts\Services\NoticiaServiceInterface;
use App\Models\Noticia;
use App\Models\NoticiaFoto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoticiaService implements NoticiaServiceInterface
{
    public function __construct(
        protected NoticiaRepositoryInterface $repository,
    ) {
    }

    public function paginateForAdmin(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($perPage, $filters);
    }

    public function findById(int $id): ?Noticia
    {
        return $this->repository->findById($id);
    }

    public function create(array $data, array $fotos = []): Noticia
    {
        return DB::transaction(function () use ($data, $fotos): Noticia {
            $data['user_id'] = auth()->id();
            $data['slug'] = $this->generateSlug($data['titulo'] ?? '');
            $data['tags'] = $this->normalizeTags($data['tags'] ?? null);

            $noticia = $this->repository->create($data);

            foreach ($fotos as $foto) {
                if ($foto instanceof UploadedFile) {
                    $this->storeFoto($noticia, $foto);
                }
            }

            return $noticia;
        });
    }

    public function update(Noticia $noticia, array $data, array $fotos = []): bool
    {
        return DB::transaction(function () use ($noticia, $data, $fotos): bool {
            $data['slug'] = $this->generateSlug($data['titulo'] ?? $noticia->titulo, $noticia->id);
            $data['tags'] = $this->normalizeTags($data['tags'] ?? null);

            $updated = $this->repository->update($noticia, $data);

            foreach ($fotos as $foto) {
                if ($foto instanceof UploadedFile) {
                    $this->storeFoto($noticia, $foto);
                }
            }

            return $updated;
        });
    }

    public function delete(Noticia $noticia): bool
    {
        return DB::transaction(function () use ($noticia): bool {
            foreach ($noticia->fotos as $foto) {
                Storage::disk('public')->delete($foto->path);
                $this->repository->deleteFoto($foto);
            }

            if ($noticia->foto_capa) {
                Storage::disk('public')->delete($noticia->foto_capa);
            }

            return $this->repository->delete($noticia);
        });
    }

    public function deleteFoto(Noticia $noticia, NoticiaFoto $foto): bool
    {
        if ((int) $foto->noticia_id !== (int) $noticia->id) {
            return false;
        }

        Storage::disk('public')->delete($foto->path);

        return $this->repository->deleteFoto($foto);
    }

    public function normalizeTags(?string $tags): ?string
    {
        if (!$tags) {
            return null;
        }

        $items = collect(explode(',', $tags))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->map(fn ($tag) => Str::slug($tag))
            ->unique()
            ->values()
            ->all();

        return $items ? implode(',', $items) : null;
    }

    public function generateSlug(string $titulo, ?int $ignoreId = null): string
    {
        $base = Str::slug($titulo) ?: 'noticia';
        $slug = $base;
        $suffix = 1;

        while (
            Noticia::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    public function storeFoto(Noticia $noticia, UploadedFile $file, ?string $legenda = null): NoticiaFoto
    {
        $tenantId = auth()->user()?->tenant_id;
        $path = $file->store("noticias/{$tenantId}", 'public');

        return $this->repository->createFoto([
            'noticia_id' => $noticia->id,
            'tenant_id' => $noticia->tenant_id,
            'path' => $path,
            'legenda' => $legenda,
            'ordem' => $this->repository->nextFotoOrder($noticia->id),
        ]);
    }

    public function storeCapa(UploadedFile $file): string
    {
        $tenantId = auth()->user()?->tenant_id;

        return $file->store("noticias/{$tenantId}/capas", 'public');
    }
}
