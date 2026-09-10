<?php

namespace App\Services;

use App\Enums\CatalogVideoProvider;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\User;
use App\Support\VideoEmbed;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class CatalogService
{
    public function createItem(User $owner, array $data): CatalogItem
    {
        $item = new CatalogItem($this->itemAttributes($data));
        $item->owner_user_id = $owner->id;

        if (($capa = $data['capa'] ?? null) instanceof UploadedFile) {
            $item->capa_path = $this->storeCover($owner, $capa);
        }

        $item->save();

        return $item;
    }

    public function updateItem(CatalogItem $item, array $data): CatalogItem
    {
        $item->fill($this->itemAttributes($data));

        if (($capa = $data['capa'] ?? null) instanceof UploadedFile) {
            $this->deleteFile($item->capa_path);
            $item->capa_path = $this->storeCover($item->owner, $capa);
        } elseif (! empty($data['remove_capa'])) {
            $this->deleteFile($item->capa_path);
            $item->capa_path = null;
        }

        $item->save();

        return $item;
    }

    public function deleteItem(CatalogItem $item): void
    {
        DB::transaction(function () use ($item): void {
            $this->deleteFile($item->capa_path);

            $item->lessons()->get()->each(function (CatalogLesson $lesson): void {
                $this->deleteFile($lesson->material_file_path);
                $this->deleteFile($lesson->video_path);
            });

            $item->delete();
        });
    }

    public function createLesson(CatalogItem $item, array $data): CatalogLesson
    {
        $lesson = new CatalogLesson($this->lessonAttributes($data));
        $lesson->catalog_item_id = $item->id;
        $lesson->ordem = $data['ordem'] ?? ((int) $item->lessons()->max('ordem') + 1);

        if (($video = $data['video_file'] ?? null) instanceof UploadedFile) {
            $this->attachVideo($lesson, $item, $video);
        }

        if (($material = $data['material_file'] ?? null) instanceof UploadedFile) {
            $this->attachMaterial($lesson, $item, $material);
        }

        $this->syncVideoProvider($lesson);
        $lesson->save();

        return $lesson;
    }

    public function updateLesson(CatalogLesson $lesson, array $data): CatalogLesson
    {
        $lesson->fill($this->lessonAttributes($data));

        if (($video = $data['video_file'] ?? null) instanceof UploadedFile) {
            $this->deleteFile($lesson->video_path);
            $this->attachVideo($lesson, $lesson->catalogItem, $video);
        } elseif (! empty($data['remove_video'])) {
            $this->deleteFile($lesson->video_path);
            $lesson->video_path = null;
        }

        if (($material = $data['material_file'] ?? null) instanceof UploadedFile) {
            $this->deleteFile($lesson->material_file_path);
            $this->attachMaterial($lesson, $lesson->catalogItem, $material);
        } elseif (! empty($data['remove_material'])) {
            $this->deleteFile($lesson->material_file_path);
            $lesson->material_file_path = null;
            $lesson->material_file_name = null;
        }

        $this->syncVideoProvider($lesson);
        $lesson->save();

        return $lesson;
    }

    public function deleteLesson(CatalogLesson $lesson): void
    {
        $this->deleteFile($lesson->material_file_path);
        $this->deleteFile($lesson->video_path);

        $lesson->delete();
    }

    /**
     * @param  list<int>  $orderedIds
     */
    public function reorderLessons(CatalogItem $item, array $orderedIds): void
    {
        DB::transaction(function () use ($item, $orderedIds): void {
            foreach (array_values($orderedIds) as $posicao => $lessonId) {
                $item->lessons()->whereKey($lessonId)->update(['ordem' => $posicao + 1]);
            }
        });
    }

    private function itemAttributes(array $data): array
    {
        return [
            'tipo' => $data['tipo'],
            'titulo' => $data['titulo'],
            'resumo' => $data['resumo'] ?? null,
            'descricao' => $data['descricao'] ?? null,
            'workload_hours' => $data['workload_hours'] ?? null,
            'status' => $data['status'],
            'preco_sugerido' => $data['preco_sugerido'] ?? null,
        ];
    }

    private function lessonAttributes(array $data): array
    {
        $videoUrl = trim((string) ($data['video_url'] ?? '')) ?: null;

        return [
            'titulo' => $data['titulo'],
            'descricao' => $data['descricao'] ?? null,
            'video_url' => $videoUrl,
            'video_duracao_segundos' => $data['video_duracao_segundos'] ?? null,
            'material_url' => trim((string) ($data['material_url'] ?? '')) ?: null,
        ];
    }

    private function storeCover(User $owner, UploadedFile $file): string
    {
        return $file->store('catalog/covers/'.$owner->id, 'public');
    }

    private function attachMaterial(CatalogLesson $lesson, CatalogItem $item, UploadedFile $file): void
    {
        $lesson->material_file_path = $file->store('catalog/materials/'.$item->id, 'public');
        $lesson->material_file_name = $file->getClientOriginalName();
    }

    private function attachVideo(CatalogLesson $lesson, CatalogItem $item, UploadedFile $file): void
    {
        $lesson->video_path = $file->store('catalog/videos/'.$item->id, 'public');
    }

    /**
     * Arquivo enviado manda no provedor; sem arquivo, deriva do link.
     */
    private function syncVideoProvider(CatalogLesson $lesson): void
    {
        if (filled($lesson->video_path)) {
            $lesson->video_provider = CatalogVideoProvider::Upload;

            return;
        }

        $lesson->video_provider = VideoEmbed::detectProvider($lesson->video_url);
    }

    private function deleteFile(?string $path): void
    {
        if (filled($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
