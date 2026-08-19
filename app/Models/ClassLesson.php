<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ClassLesson extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'course_class_id',
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
