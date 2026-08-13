<?php

namespace App\Policies;

use App\Models\Noticia;
use App\Models\User;

class NoticiaPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->user_type, [User::TYPE_TENANT_ADMIN, User::TYPE_TENANT_MANAGER], true);
    }

    public function view(User $user, Noticia $noticia): bool
    {
        if ((int) $user->tenant_id !== (int) $noticia->tenant_id) {
            return false;
        }

        return in_array($user->user_type, [User::TYPE_TENANT_ADMIN, User::TYPE_TENANT_MANAGER], true);
    }

    public function create(User $user): bool
    {
        return in_array($user->user_type, [User::TYPE_TENANT_ADMIN, User::TYPE_TENANT_MANAGER], true);
    }

    public function update(User $user, Noticia $noticia): bool
    {
        if ((int) $user->tenant_id !== (int) $noticia->tenant_id) {
            return false;
        }

        return in_array($user->user_type, [User::TYPE_TENANT_ADMIN, User::TYPE_TENANT_MANAGER], true);
    }

    public function delete(User $user, Noticia $noticia): bool
    {
        if ((int) $user->tenant_id !== (int) $noticia->tenant_id) {
            return false;
        }

        return $user->user_type === User::TYPE_TENANT_ADMIN;
    }
}
