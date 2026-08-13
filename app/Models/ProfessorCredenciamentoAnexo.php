<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessorCredenciamentoAnexo extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'professores_credenciamento_anexos';

    protected $fillable = [
        'tenant_id',
        'professor_credenciamento_id',
        'titulo',
        'arquivo_path',
        'ordem',
    ];

    public function credenciamento(): BelongsTo
    {
        return $this->belongsTo(ProfessorCredenciamento::class, 'professor_credenciamento_id');
    }
}
