<?php

namespace App\Policies;

use App\Models\Grade;
use App\Models\User;

class GradePolicy
{
    public function view(User $user, Grade $grade): bool
    {
        if ($user->hasTenantRole(User::TYPE_TENANT_ADMIN) || $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return true;
        }

        return (int) $grade->student?->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasTenantRole(User::TYPE_TENANT_MANAGER);
    }

    public function update(User $user, Grade $grade): bool
    {
        if (! $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return false;
        }

        if (! $grade->curriculum || ! $grade->curriculum->responsible_user_id) {
            return true;
        }

        return (int) $grade->curriculum->responsible_user_id === (int) $user->id;
    }
}
