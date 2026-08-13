<?php

namespace App\Models;

use App\Enums\EmpresaResponsibleClaimStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmpresaResponsibleClaim extends Model
{
    protected $fillable = [
        'tenant_id',
        'user_id',
        'empresa_id',
        'cnpj',
        'razao_social_informada',
        'mensagem',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => EmpresaResponsibleClaimStatus::class,
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
