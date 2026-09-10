<?php

namespace App\Policies;

use App\Models\CatalogItem;
use App\Models\User;

/**
 * O catálogo é por diretor: cada um só enxerga e edita o que ele mesmo criou.
 */
class CatalogItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function view(User $user, CatalogItem $item): bool
    {
        return $this->owns($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function update(User $user, CatalogItem $item): bool
    {
        return $this->owns($user, $item);
    }

    public function delete(User $user, CatalogItem $item): bool
    {
        return $this->owns($user, $item);
    }

    private function owns(User $user, CatalogItem $item): bool
    {
        return $user->isTenantDirector() && (int) $item->owner_user_id === (int) $user->id;
    }
}
