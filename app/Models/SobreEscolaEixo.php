<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SobreEscolaEixo extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sobre_escola_id',
        'titulo',
        'descricao',
        'ordem',
    ];

    public function sobreEscola(): BelongsTo
    {
        return $this->belongsTo(SobreEscola::class);
    }
}
