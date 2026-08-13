<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function view(User $user, Attendance $attendance): bool
    {
        if ($user->hasTenantRole(User::TYPE_TENANT_ADMIN) || $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return true;
        }

        return (int) $attendance->student?->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->hasTenantRole(User::TYPE_TENANT_MANAGER);
    }

    public function update(User $user, Attendance $attendance): bool
    {
        if (! $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return false;
        }

        if (! $attendance->curriculum || ! $attendance->curriculum->responsible_user_id) {
            return true;
        }

        return (int) $attendance->curriculum->responsible_user_id === (int) $user->id;
    }

    public function manageSheet(User $user, int $recordedByUserId): bool
    {
        if ((int) $user->id === (int) $recordedByUserId) {
            return true;
        }

        return $user->isTenantManager() || $user->hasTenantRole(User::TYPE_TENANT_MANAGER);
    }
}
