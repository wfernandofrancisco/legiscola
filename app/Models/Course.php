<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'workload_hours',
        'status',
        'admin_user_id',
        'catalog_item_id',
        'catalog_license_id',
    ];

    public function curricula(): HasMany
    {
        return $this->hasMany(Curriculum::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function turmas(): HasMany
    {
        return $this->hasMany(Turma::class, 'course_id');
    }

    public function courseClasses(): HasMany
    {
        return $this->hasMany(CourseClass::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function catalogLicense(): BelongsTo
    {
        return $this->belongsTo(CatalogLicense::class);
    }

    /**
     * Curso veio do catálogo de um diretor (e não foi criado pela própria câmara).
     */
    public function isFromCatalog(): bool
    {
        return $this->catalog_license_id !== null;
    }

    /**
     * Quem ministra o conteúdo regional.
     *
     * Na licença é só um nome: a câmara compra o curso pronto e não escolhe o professor.
     */
    public function catalogProfessorNome(): ?string
    {
        return $this->catalogLicense?->professor_nome;
    }

    /**
     * Aparece no portal / listagens de inscrição.
     *
     * Conteúdo próprio da câmara sempre aparece. Conteúdo do catálogo some depois de exibir_ate.
     */
    public function scopeVisibleOnPortal(Builder $query): Builder
    {
        return $query->where(function (Builder $q): void {
            $q->whereNull('catalog_license_id')
                ->orWhereHas('catalogLicense', fn (Builder $l) => $l->stillVisible());
        });
    }
}
