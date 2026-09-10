<?php

namespace App\Policies;

use App\Models\CatalogLicense;
use App\Models\User;
use App\Support\DirectorContext;

/**
 * Uma licença só é do diretor que a emitiu — e só enquanto a câmara continuar na abrangência dele.
 * Se a UF sair da região do diretor, a licença deixa de ser gerenciável por ele.
 */
class CatalogLicensePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function view(User $user, CatalogLicense $license): bool
    {
        return $this->owns($user, $license);
    }

    public function create(User $user): bool
    {
        return $user->isTenantDirector();
    }

    public function update(User $user, CatalogLicense $license): bool
    {
        return $this->owns($user, $license);
    }

    public function delete(User $user, CatalogLicense $license): bool
    {
        return $this->owns($user, $license);
    }

    private function owns(User $user, CatalogLicense $license): bool
    {
        return $user->isTenantDirector()
            && (int) $license->director_user_id === (int) $user->id
            && DirectorContext::allows((int) $license->tenant_id);
    }
}
