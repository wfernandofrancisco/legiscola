<?php

namespace App\Models;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Curso ou palestra do catálogo de um diretor regional.
 *
 * Não usa BelongsToTenant de propósito: o item não pertence a nenhum cliente. O vínculo com
 * um tenant acontece só através de CatalogLicense.
 */
class CatalogItem extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'owner_user_id',
        'tipo',
        'titulo',
        'resumo',
        'descricao',
        'workload_hours',
        'capa_path',
        'status',
        'preco_sugerido',
    ];

    protected function casts(): array
    {
        return [
            'tipo' => CatalogItemTipo::class,
            'status' => CatalogItemStatus::class,
            'preco_sugerido' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CatalogLesson::class)->orderBy('ordem')->orderBy('id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(CatalogLicense::class);
    }

    public function isCurso(): bool
    {
        return $this->tipo === CatalogItemTipo::Curso;
    }

    public function isPalestra(): bool
    {
        return $this->tipo === CatalogItemTipo::Palestra;
    }

    /**
     * Só item publicado pode ser liberado para um cliente.
     */
    public function isLicensable(): bool
    {
        return $this->status->isLicensable();
    }
}
