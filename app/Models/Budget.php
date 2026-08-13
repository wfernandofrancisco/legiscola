<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Budget extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    const STATUS_PENDENTE = 'pendente';

    const STATUS_APROVADO = 'aprovado';

    const STATUS_REJEITADO = 'rejeitado';

    const STATUS_CANCELADO = 'cancelado';

    protected $fillable = [
        'tenant_id',
        'titulo',
        'descricao',
        'user_id',
        'subtotal',
        'desconto',
        'total',
        'status',
        'validade',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'desconto' => 'decimal:2',
            'total' => 'decimal:2',
            'validade' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['titulo', 'status', 'total'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $eventName) => "Orçamento '{$this->titulo}' foi {$eventName}")
            ->useLogName('budgets');
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
