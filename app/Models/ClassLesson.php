<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ClassLesson extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'course_class_id',
        'catalog_lesson_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'is_online',
        'video_url',
        'material_url',
        'material_file_path',
        'material_file_name',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'is_online' => 'boolean',
        ];
    }

    public function courseClass(): BelongsTo
    {
        return $this->belongsTo(CourseClass::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function catalogLesson(): BelongsTo
    {
        return $this->belongsTo(CatalogLesson::class);
    }

    /**
     * Aula materializada a partir do catálogo de um diretor.
     */
    public function isFromCatalog(): bool
    {
        return $this->catalog_lesson_id !== null;
    }

    /**
     * Vídeo exibido para o aluno.
     *
     * Em aula vinda do catálogo o vídeo não é copiado: fica no item de origem, então corrigir o
     * link lá corrige em todas as câmaras. Um link preenchido localmente tem precedência, para a
     * câmara poder substituir por uma gravação própria.
     */
    public function effectiveVideoUrl(): ?string
    {
        if (filled($this->video_url)) {
            return $this->video_url;
        }

        return $this->catalogLesson?->effectiveVideoUrl();
    }

    /**
     * Player HTML5 (&lt;video&gt;) em vez de iframe — arquivo MP4 do catálogo.
     */
    public function effectiveVideoIsNative(): bool
    {
        // Link local da câmara continua como URL externa (YouTube/Vimeo/arquivo).
        if (filled($this->video_url)) {
            return false;
        }

        return (bool) $this->catalogLesson?->isUploadedVideo();
    }

    public function effectiveMaterialUrl(): ?string
    {
        if (filled($this->material_file_path)) {
            return Storage::disk('public')->url($this->material_file_path);
        }

        if (filled($this->material_url)) {
            return $this->material_url;
        }

        return $this->catalogLesson?->materialDownloadUrl();
    }

    public function effectiveMaterialName(): ?string
    {
        if (filled($this->material_file_path)) {
            return $this->material_file_name;
        }

        if (filled($this->material_url)) {
            return $this->material_file_name ?: 'Material da aula';
        }

        return $this->catalogLesson?->material_file_name ?: ($this->catalogLesson?->material_url ? 'Material da aula' : null);
    }

    /**
     * Aulas da turma, ignorando TenantScope em class_lessons (evita lista vazia na ficha
     * quando tenant_id da linha diverge do TenantContext ou de dados importados).
     *
     * @return Collection<int, ClassLesson>
     */
    public static function orderedForCourseClass(int $courseClassId): Collection
    {
        return static::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->where('course_class_id', $courseClassId)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();
    }

    public static function findForTurmaOrFail(int $courseClassId, int $lessonId): self
    {
        return static::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->where('course_class_id', $courseClassId)
            ->where('id', $lessonId)
            ->firstOrFail();
    }

    public static function findByIdIgnoringTenantScope(int $id): self
    {
        return static::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->whereKey($id)
            ->firstOrFail();
    }
}
