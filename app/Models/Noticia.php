<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Noticia extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public const TIPO_COMPLETA = 'completa';

    public const TIPO_RAPIDA = 'rapida';

    public const TIPO_VIDEO = 'video';

    protected $fillable = [
        'tenant_id',
        'user_id',
        'titulo',
        'subtitulo',
        'slug',
        'tipo',
        'noticia',
        'fonte_url',
        'video_url',
        'tags',
        'foto_capa',
        'publicar_em',
        'is_destaque',
        'ativo',
    ];

    protected $casts = [
        'publicar_em' => 'datetime',
        'is_destaque' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(NoticiaFoto::class)->orderBy('ordem');
    }

    public function getFotoCapaUrlAttribute(): ?string
    {
        if ($this->foto_capa) {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_capa);
        }

        $videoId = $this->youtube_video_id;

        return $videoId ? "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg" : null;
    }

    public function getYoutubeVideoIdAttribute(): ?string
    {
        if (!$this->video_url) {
            return null;
        }

        $url = trim($this->video_url);
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $candidate = explode('/', $path)[0] ?? '';
        } elseif (in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com'], true)) {
            if ($path === 'watch') {
                parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
                $candidate = (string) ($query['v'] ?? '');
            } elseif (str_starts_with($path, 'embed/') || str_starts_with($path, 'shorts/')) {
                $candidate = explode('/', $path)[1] ?? '';
            } else {
                $candidate = '';
            }
        } else {
            return null;
        }

        return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) ? $candidate : null;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        return $this->youtube_video_id
            ? 'https://www.youtube-nocookie.com/embed/'.$this->youtube_video_id
            : null;
    }

    public function getTipoLabelAttribute(): string
    {
        return match ($this->tipo) {
            self::TIPO_RAPIDA => 'Notícia rápida',
            self::TIPO_VIDEO => 'Vídeo',
            default => 'Notícia completa',
        };
    }
}
