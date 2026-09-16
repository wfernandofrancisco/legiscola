<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Support\VideoEmbed;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Aula de conteúdo de um curso da câmara.
 *
 * Não tem data nem horário: isso entra na grade da turma (`class_lessons`).
 */
class CourseLesson extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'course_id',
        'ordem',
        'title',
        'description',
        'video_url',
        'video_path',
        'material_url',
        'material_file_path',
        'material_file_name',
    ];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classLessons(): HasMany
    {
        return $this->hasMany(ClassLesson::class);
    }

    public function hasVideo(): bool
    {
        return filled($this->video_url) || filled($this->video_path);
    }

    public function isUploadedVideo(): bool
    {
        return filled($this->video_path);
    }

    public function videoPublicUrl(): ?string
    {
        if (! filled($this->video_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->video_path);
    }

    public function effectiveVideoUrl(): ?string
    {
        return $this->videoPublicUrl() ?: $this->video_url;
    }

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
}
