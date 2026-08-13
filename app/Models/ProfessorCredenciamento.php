<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfessorCredenciamento extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'professores_credenciamentos';

    protected $fillable = [
        'tenant_id',
        'titulo',
        'ano_referencia',
        'texto',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function anexos(): HasMany
    {
        return $this->hasMany(ProfessorCredenciamentoAnexo::class)
            ->orderBy('ordem')
            ->orderBy('id');
    }
}
