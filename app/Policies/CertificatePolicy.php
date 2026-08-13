<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;

class CertificatePolicy
{
    public function view(User $user, Certificate $certificate): bool
    {
        if ($user->hasTenantRole(User::TYPE_TENANT_ADMIN) || $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return true;
        }

        return (int) $certificate->student?->user_id === (int) $user->id;
    }
}
