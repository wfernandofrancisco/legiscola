<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    /**
     * Gestor/admin pela coluna user_type OU role Spatie — evita 403 só por falta de role sincronizada.
     */
    private function isTenantAdminOrGestor(User $user): bool
    {
        return $user->isTenantAdmin()
            || $user->isTenantManager()
            || $user->hasTenantRole(User::TYPE_TENANT_ADMIN)
            || $user->hasTenantRole(User::TYPE_TENANT_MANAGER);
    }

    public function viewAny(User $user): bool
    {
        return $this->isTenantAdminOrGestor($user)
            || $user->isTenantProfessor();
    }

    public function create(User $user): bool
    {
        return $this->isTenantAdminOrGestor($user)
            || $user->isTenantProfessor();
    }

    public function view(User $user, Quiz $quiz): bool
    {
        if ((int) $user->tenant_id !== (int) $quiz->tenant_id) {
            return false;
        }

        if ($this->isTenantAdminOrGestor($user)) {
            return true;
        }

        if ($user->isTenantProfessor()) {
            return $this->professorTouchesQuiz($user, $quiz);
        }

        return false;
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($this->isTenantAdminOrGestor($user)) {
            return $this->view($user, $quiz) && $this->create($user);
        }

        return $user->isTenantProfessor() && $this->professorTouchesQuiz($user, $quiz);
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $this->view($user, $quiz) && ($user->isTenantAdmin() || $user->hasTenantRole(User::TYPE_TENANT_ADMIN));
    }

    private function professorTouchesQuiz(User $user, Quiz $quiz): bool
    {
        $teacher = $user->teacher;
        if (! $teacher) {
            return false;
        }

        if ($quiz->course_class_id) {
            $cc = $quiz->courseClass;
            if ($cc && $user->teachesCourseClass($cc)) {
                return true;
            }
        }

        return $quiz->courseClasses()->whereHas('teachers', fn ($q) => $q->where('teachers.id', $teacher->id))->exists();
    }
}

