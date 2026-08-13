<?php

namespace App\Policies;

use App\Models\CourseClass;
use App\Models\User;

class CourseClassPolicy
{
    public function update(User $user, CourseClass $courseClass): bool
    {
        return $user->hasTenantRole(User::TYPE_TENANT_ADMIN) || $user->hasTenantRole(User::TYPE_TENANT_MANAGER);
    }

    /**
     * Docente vinculado à turma (cadastro de professores da turma).
     */
    public function interactAsAssignedProfessor(User $user, CourseClass $courseClass): bool
    {
        if ((int) $user->tenant_id !== (int) $courseClass->tenant_id) {
            return false;
        }

        if ($user->isTenantManager() || $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return true;
        }

        if (! $user->isTenantProfessor()) {
            return false;
        }

        return $user->teachesCourseClass($courseClass);
    }
}
