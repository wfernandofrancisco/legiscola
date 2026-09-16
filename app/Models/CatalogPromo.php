<?php

namespace App\Models;

use App\Enums\CatalogItemStatus;
use App\Scopes\TenantScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * Banner de aviso/promoção do diretor para o admin da câmara.
 */
class CatalogPromo extends Model
{
    public const ALCANCE_GERAL = 'geral';

    public const ALCANCE_ESPECIFICO = 'especifico';

    protected $fillable = [
        'director_user_id',
        'catalog_item_id',
        'titulo',
        'mensagem',
        'preco_de',
        'preco_por',
        'desconto_percentual',
        'alcance',
        'inicia_em',
        'termina_em',
        'ativo',
        'capa_path',
    ];

    protected function casts(): array
    {
        return [
            'preco_de' => 'decimal:2',
            'preco_por' => 'decimal:2',
            'desconto_percentual' => 'integer',
            'inicia_em' => 'date',
            'termina_em' => 'date',
            'ativo' => 'boolean',
        ];
    }

    public function director(): BelongsTo
    {
        return $this->belongsTo(User::class, 'director_user_id');
    }

    public function catalogItem(): BelongsTo
    {
        return $this->belongsTo(CatalogItem::class);
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'catalog_promo_tenant')
            ->withTimestamps();
    }

    public function dismissals(): HasMany
    {
        return $this->hasMany(CatalogPromoDismissal::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CatalogPromoContact::class);
    }

    public function isGeral(): bool
    {
        return $this->alcance === self::ALCANCE_GERAL;
    }

    public function coverUrl(): ?string
    {
        $path = $this->capa_path ?: $this->catalogItem?->capa_path;

        return filled($path) ? Storage::disk('public')->url($path) : null;
    }

    public function isWithinSchedule(?\DateTimeInterface $hoje = null): bool
    {
        $hoje = $hoje ? \Carbon\CarbonImmutable::parse($hoje)->startOfDay() : now()->startOfDay();

        if ($this->inicia_em && $hoje->lt($this->inicia_em->startOfDay())) {
            return false;
        }

        if ($this->termina_em && $hoje->gt($this->termina_em->endOfDay())) {
            return false;
        }

        return true;
    }

    /**
     * Avisos ativos na janela de datas e com item publicado.
     */
    public function scopeCurrentlyActive(Builder $query): Builder
    {
        $hoje = now()->toDateString();

        return $query
            ->where('ativo', true)
            ->where(fn (Builder $q) => $q->whereNull('inicia_em')->orWhereDate('inicia_em', '<=', $hoje))
            ->where(fn (Builder $q) => $q->whereNull('termina_em')->orWhereDate('termina_em', '>=', $hoje))
            ->whereHas('catalogItem', fn (Builder $q) => $q->where('status', CatalogItemStatus::Publicado));
    }

    /**
     * Visível para a câmara operacional do admin (status ativo), mesmo com cadastro pendente.
     * Alcance geral nas UFs do diretor, ou pivot específico.
     */
    public function scopeForTenant(Builder $query, int $tenantId): Builder
    {
        $tenant = Tenant::query()
            ->whereKey($tenantId)
            ->where('status', Tenant::STATUS_ATIVO)
            ->first();

        if (! $tenant || ! filled($tenant->estado)) {
            return $query->whereRaw('1 = 0');
        }

        $uf = strtoupper((string) $tenant->estado);

        return $query->where(function (Builder $q) use ($tenantId, $uf): void {
            $q->where(function (Builder $geral) use ($uf): void {
                // Diretor não tem tenant_id: sem remover o TenantScope a relação some no painel do admin.
                $geral->where('alcance', self::ALCANCE_GERAL)
                    ->whereHas('director', function (Builder $director) use ($uf): void {
                        $director->withoutGlobalScopes([TenantScope::class])
                            ->whereHas('directorUfs', fn (Builder $ufs) => $ufs->where('uf', $uf));
                    });
            })->orWhere(function (Builder $esp) use ($tenantId): void {
                $esp->where('alcance', self::ALCANCE_ESPECIFICO)
                    ->whereHas('tenants', fn (Builder $t) => $t->where('tenants.id', $tenantId));
            });
        });
    }

    /**
     * Ainda não foi fechado por este admin, ou o diretor editou depois do fechamento.
     */
    public function scopeNotDismissedBy(Builder $query, int $userId): Builder
    {
        return $query->whereDoesntHave('dismissals', function (Builder $q) use ($userId): void {
            $q->where('user_id', $userId)
                ->whereColumn('catalog_promo_dismissals.promo_updated_at', '>=', 'catalog_promos.updated_at');
        });
    }

    /**
     * @return list<string>
     */
    public static function alcanceOptions(): array
    {
        return [
            self::ALCANCE_GERAL => 'Todas as câmaras ativas da minha região',
            self::ALCANCE_ESPECIFICO => 'Câmaras específicas',
        ];
    }
}
