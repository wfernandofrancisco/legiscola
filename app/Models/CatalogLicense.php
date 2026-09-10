<?php

namespace App\Models;

use App\Enums\CatalogLicenseModalidade;
use App\Enums\CatalogLicensePagamentoStatus;
use App\Enums\CatalogLicenseStatus;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Licença de um item do catálogo para uma câmara.
 *
 * Tem tenant_id mas não usa BelongsToTenant: quem mais lê esta tabela é o diretor, que roda
 * sem TenantContext. O filtro por cliente é feito explicitamente em cada consulta — no lado do
 * cliente, pelo escopo {@see scopeForTenant}.
 */
class CatalogLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog_item_id',
        'tenant_id',
        'director_user_id',
        'status',
        'liberado_em',
        'exibir_ate',
        'max_turmas',
        'modalidade',
        'palestra_em',
        'max_inscritos',
        'professor_nome',
        'observacoes',
        'valor',
        'pagamento_status',
        'vencimento_em',
        'pago_em',
        'forma_pagamento',
        'nota_fiscal_numero',
        'nota_fiscal_emitida_em',
        'nota_fiscal_arquivo_path',
    ];

    protected function casts(): array
    {
        return [
            'status' => CatalogLicenseStatus::class,
            'pagamento_status' => CatalogLicensePagamentoStatus::class,
            'modalidade' => CatalogLicenseModalidade::class,
            'liberado_em' => 'datetime',
            'exibir_ate' => 'date',
            'palestra_em' => 'datetime',
            'vencimento_em' => 'date',
            'pago_em' => 'date',
            'nota_fiscal_emitida_em' => 'date',
            'valor' => 'decimal:2',
            'max_turmas' => 'integer',
            'max_inscritos' => 'integer',
        ];
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_user_id');
    }

    /**
     * O curso que a câmara criou ao ativar esta licença (um por licença).
     */
    public function course(): HasOne
    {
        return $this->hasOne(Course::class, 'catalog_license_id');
    }

    /**
     * Turmas já abertas com esta licença — base do limite max_turmas.
     */
    public function courseClasses(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseClass::class,
            Course::class,
            'catalog_license_id',
            'course_id'
        );
    }

    /**
     * Edições da palestra já agendadas pela câmara com esta licença.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'catalog_license_id');
    }

    public function eventosAgendados(): int
    {
        return $this->events()
            ->withoutGlobalScopes([TenantScope::class])
            ->count();
    }

    public function eventosRestantes(): ?int
    {
        if ($this->max_turmas === null) {
            return null;
        }

        return max(0, $this->max_turmas - $this->eventosAgendados());
    }

    /**
     * A câmara ainda pode agendar uma edição desta palestra.
     */
    public function canOpenEvento(): bool
    {
        return $this->isUsable() && ($this->eventosRestantes() === null || $this->eventosRestantes() > 0);
    }

    public function turmasAbertas(): int
    {
        return $this->courseClasses()
            ->withoutGlobalScopes([TenantScope::class])
            ->count();
    }

    public function turmasRestantes(): ?int
    {
        if ($this->max_turmas === null) {
            return null;
        }

        return max(0, $this->max_turmas - $this->turmasAbertas());
    }

    /**
     * A câmara ainda pode abrir turma com esta licença.
     */
    public function canOpenTurma(): bool
    {
        return $this->isUsable() && ($this->turmasRestantes() === null || $this->turmasRestantes() > 0);
    }

    /**
     * Licença ainda dentro do prazo de exibição (ou sem prazo).
     */
    public function scopeStillVisible(Builder $query): Builder
    {
        return $query->where(
            fn (Builder $q) => $q->whereNull('exibir_ate')->orWhereDate('exibir_ate', '>=', now()->toDateString())
        );
    }

    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        return $query->where('catalog_licenses.tenant_id', $tenantId);
    }

    /**
     * Licenças que o cliente pode usar agora.
     */
    public function scopeUsable(Builder $query): Builder
    {
        return $query
            ->where('status', CatalogLicenseStatus::Ativa)
            ->where(fn (Builder $q) => $q->whereNull('exibir_ate')->orWhereDate('exibir_ate', '>=', now()->toDateString()));
    }

    public function isExpired(): bool
    {
        return $this->exibir_ate !== null && $this->exibir_ate->isBefore(now()->startOfDay());
    }

    /**
     * A licença está válida para o cliente abrir/exibir turmas hoje.
     */
    public function isUsable(): bool
    {
        return $this->status->isUsable() && ! $this->isExpired();
    }
}
