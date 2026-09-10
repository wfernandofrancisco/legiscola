<?php

namespace App\Models;

use App\Support\BrazilianStates;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * UF sob responsabilidade de um diretor regional.
 *
 * Não usa BelongsToTenant: o registro pertence ao diretor, que não tem tenant.
 */
class DirectorUf extends Model
{
    use HasFactory;

    protected $table = 'director_ufs';

    protected $fillable = [
        'user_id',
        'uf',
    ];

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function setUfAttribute(?string $value): void
    {
        $this->attributes['uf'] = BrazilianStates::normalize($value);
    }

    public function getEstadoNomeAttribute(): string
    {
        return BrazilianStates::name($this->uf) ?? (string) $this->uf;
    }
}
