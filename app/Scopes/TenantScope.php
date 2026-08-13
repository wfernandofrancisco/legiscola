<?php

namespace App\Scopes;

use App\Support\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Filtra automaticamente por tenant_id quando TenantContext::getTenantId() está definido.
 *
 * No módulo Central (ou jobs/console sem contexto), não há filtro — use apenas nos models
 * que possuem coluna tenant_id.
 */
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $tenantId = TenantContext::getTenantId();

        if ($tenantId === null) {
            return;
        }

        $builder->where($model->getTable() . '.tenant_id', $tenantId);
    }
}
