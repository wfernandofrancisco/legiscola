<?php

namespace App\Policies;

use App\Models\CatalogPromo;
use App\Models\User;

class CatalogPromoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function view(User $user, CatalogPromo $promo): bool
    {
        if ($user->isTenantDirector()) {
            return (int) $promo->director_user_id === (int) $user->id;
        }

        // Admin da câmara: só se o aviso estiver visível para o tenant dele.
        if (! $user->isTenantAdmin() || ! $user->tenant_id) {
            return false;
        }

        return CatalogPromo::query()
            ->whereKey($promo->id)
            ->currentlyActive()
            ->forTenant((int) $user->tenant_id)
            ->exists();
    }

    public function create(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function update(User $user, CatalogPromo $promo): bool
    {
        return $user->isTenantDirector() && (int) $promo->director_user_id === (int) $user->id;
    }

    public function delete(User $user, CatalogPromo $promo): bool
    {
        return $this->update($user, $promo);
    }

    public function dismiss(User $user, CatalogPromo $promo): bool
    {
        return $user->isTenantAdmin() && $this->view($user, $promo);
    }
}
