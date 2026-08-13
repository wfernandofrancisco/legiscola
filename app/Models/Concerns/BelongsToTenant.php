<?php

namespace App\Models\Concerns;

use App\Scopes\TenantScope;
use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Escopo global por tenant_id + preenchimento automático em creates quando há contexto.
 *
 * Sem TenantContext (ex.: módulo Central), as queries não filtram por tenant.
 */
trait BelongsToTenant
{
    public function initializeBelongsToTenant(): void
    {
        if (! in_array('tenant_id', $this->fillable, true)) {
            $this->fillable[] = 'tenant_id';
        }
    }

    protected static function bootBelongsToTenant(): void
    {
        static::addGlobalScope(new TenantScope());

        static::creating(function (Model $model): void {
            if ($model->getAttribute('tenant_id') !== null) {
                return;
            }

            $tenantId = TenantContext::getTenantId() ?? Auth::user()?->tenant_id;
            if ($tenantId !== null) {
                $model->setAttribute('tenant_id', $tenantId);
            }
        });
    }
}
