<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Verify if the authenticated user can access another user in their tenant.
     */
    public function tenantAccess(User $authUser, User $targetUser): bool
    {
        return $authUser->tenant_id === $targetUser->tenant_id;
    }

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $authUser): bool
    {
        return $authUser->hasRole('tenant_admin') || $authUser->hasRole('tenant_manager');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $authUser, User $targetUser): bool
    {
        return $this->tenantAccess($authUser, $targetUser) &&
               ($authUser->hasRole('tenant_admin') || $authUser->hasRole('tenant_manager'));
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $authUser): bool
    {
        return $authUser->hasRole('tenant_admin');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $authUser, User $targetUser): bool
    {
        return $this->tenantAccess($authUser, $targetUser) &&
               $authUser->hasRole('tenant_admin') &&
               !$this->isSuperUser($targetUser);
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $authUser, User $targetUser): bool
    {
        return $this->tenantAccess($authUser, $targetUser) &&
               $authUser->hasRole('tenant_admin') &&
               $authUser->id !== $targetUser->id && // Não pode deletar a si mesmo
               !$this->isSuperUser($targetUser);
    }

    /**
     * Check if user is a super user (cannot be modified by tenant admins).
     */
    private function isSuperUser(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->hasRole('central_super_admin');
    }
}
