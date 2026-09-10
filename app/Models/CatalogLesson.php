<?php

namespace App\Models;

use App\Enums\CatalogVideoProvider;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Aula de um item do catálogo.
 *
 * É a fonte de verdade do vídeo e do material: as aulas materializadas nas turmas dos clientes
 * apontam para cá em vez de copiar o conteúdo, então corrigir um vídeo aqui propaga para todos.
 */
class CatalogLesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_item_id',
        'ordem',
        'titulo',
        'descricao',
        'video_provider',
        'video_url',
        'video_path',
        'video_duracao_segundos',
        'material_url',
        'material_file_path',
        'material_file_name',
    ];

    protected function casts(): array
    {
        return [
            'video_provider' => CatalogVideoProvider::class,
            'ordem' => 'integer',
            'video_duracao_segundos' => 'integer',
        ];
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function hasVideo(): bool
    {
        return filled($this->video_url) || filled($this->video_path);
    }

    /**
     * Arquivo MP4 (ou similar) hospedado no storage público.
     */
    public function isUploadedVideo(): bool
    {
        return filled($this->video_path);
    }

    /**
     * URL pública do arquivo enviado, se houver.
     */
    public function videoPublicUrl(): ?string
    {
        if (! filled($this->video_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }

    /**
     * URL efetiva do vídeo desta aula: arquivo enviado tem precedência sobre o link.
     */
    public function effectiveVideoUrl(): ?string
    {
        return $this->videoPublicUrl() ?: $this->video_url;
    }

    /**
     * URL para <iframe>, quando o provedor permite embed (YouTube/Vimeo).
     * Arquivo enviado não usa iframe — usa o player nativo.
     */
    public function videoEmbedUrl(): ?string
    {
        if ($this->isUploadedVideo()) {
            return null;
        }

        return VideoEmbed::embedUrl($this->video_url);
    }

    public function hasMaterial(): bool
    {
        return filled($this->material_url) || filled($this->material_file_path);
    }

    public function materialDownloadUrl(): ?string
    {
        if (filled($this->material_file_path)) {
            return Storage::disk('public')->url($this->material_file_path);
        }

        return $this->material_url;
    }

    public function duracaoFormatada(): ?string
    {
        if (! $this->video_duracao_segundos) {
            return null;
        }

        $minutos = intdiv($this->video_duracao_segundos, 60);
        $segundos = $this->video_duracao_segundos % 60;

        return sprintf('%d min %02d s', $minutos, $segundos);
    }
}
