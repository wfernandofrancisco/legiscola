<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Scopes\TenantScope;
use App\Support\TenantContext;
use App\Support\VideoEmbed;
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
        'course_lesson_id',
        'title',
        'date',
        'start_time',
        'end_time',
        'is_online',
        'video_url',
        'video_path',
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

    public function courseLesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class);
    }

    /**
     * Aula materializada a partir do catálogo de um diretor.
     */
    public function isFromCatalog(): bool
    {
        return $this->catalog_lesson_id !== null;
    }

    /**
     * Aula da grade ligada a uma aula de conteúdo do curso da câmara.
     */
    public function isFromCourseContent(): bool
    {
        return $this->course_lesson_id !== null;
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

    /**
     * Vídeo exibido para o aluno.
     *
     * Prioridade: arquivo na turma → link na turma → aula do curso → catálogo regional.
     */
    public function effectiveVideoUrl(): ?string
    {
        if (filled($this->video_path)) {
            return $this->videoPublicUrl();
        }

        if (filled($this->video_url)) {
            return $this->video_url;
        }

        return $this->courseLesson?->effectiveVideoUrl()
            ?: $this->catalogLesson?->effectiveVideoUrl();
    }

    /**
     * Player HTML5 (&lt;video&gt;) em vez de iframe — MP4 anexado na câmara ou no catálogo.
     */
    public function effectiveVideoIsNative(): bool
    {
        if (filled($this->video_path)) {
            return true;
        }

        // Link local da câmara continua como URL externa (YouTube/Vimeo/arquivo).
        if (filled($this->video_url)) {
            return false;
        }

        return (bool) ($this->courseLesson?->isUploadedVideo() ?: $this->catalogLesson?->isUploadedVideo());
    }

    /**
     * Rótulo da origem do vídeo (YouTube, Vimeo, arquivo anexado, catálogo…).
     */
    public function effectiveVideoSourceLabel(): ?string
    {
        if (filled($this->video_path)) {
            return 'Arquivo anexado';
        }

        if (filled($this->video_url)) {
            return VideoEmbed::detectProvider($this->video_url)?->label() ?? 'Link externo';
        }

        if ($this->courseLesson?->isUploadedVideo()) {
            return 'Arquivo do curso';
        }

        if ($this->courseLesson?->hasVideo()) {
            return VideoEmbed::detectProvider($this->courseLesson->video_url)?->label() ?? 'Aula do curso';
        }

        if ($this->catalogLesson?->isUploadedVideo()) {
            return 'Arquivo do catálogo';
        }

        if ($this->catalogLesson?->hasVideo()) {
            return $this->catalogLesson->video_provider?->label() ?? 'Catálogo regional';
        }

        return null;
    }

    public function effectiveMaterialUrl(): ?string
    {
        if (filled($this->material_file_path)) {
            return Storage::disk('public')->url($this->material_file_path);
        }

        if (filled($this->material_url)) {
            return $this->material_url;
        }

        return $this->courseLesson?->materialDownloadUrl()
            ?: $this->catalogLesson?->materialDownloadUrl();
    }

    public function effectiveMaterialName(): ?string
    {
        if (filled($this->material_file_path)) {
            return $this->material_file_name;
        }

        if (filled($this->material_url)) {
            return $this->material_file_name ?: 'Material da aula';
        }

        if ($this->courseLesson?->hasMaterial()) {
            return $this->courseLesson->material_file_name ?: 'Material da aula';
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

    /**
     * Binding de rota: ignora TenantScope em class_lessons (tenant_id da aula pode divergir
     * após importação/catálogo), mas exige que a turma da aula pertença ao tenant da request.
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        $field ??= $this->getRouteKeyName();

        $lesson = static::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->where($field, $value)
            ->first();

        if (! $lesson) {
            return null;
        }

        $tenantId = TenantContext::getTenantId();
        if ($tenantId === null) {
            return $lesson;
        }

        $turmaDoTenant = CourseClass::query()
            ->whereKey($lesson->course_class_id)
            ->exists();

        return $turmaDoTenant ? $lesson : null;
    }
}
