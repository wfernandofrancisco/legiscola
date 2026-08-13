<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SobreEscola extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'institucional',
        'objetivos',
        'quem_somos',
        'projeto_pedagogico',
        'legislacao',
    ];

    public function eixos(): HasMany
    {
        return $this->hasMany(SobreEscolaEixo::class)
            ->orderBy('ordem')
            ->orderBy('id');
    }

    public function pessoas(): HasMany
    {
        return $this->hasMany(SobreEscolaPessoa::class)
            ->orderBy('ordem')
            ->orderBy('id');
    }
}
