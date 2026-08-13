<?php

namespace App\Support;

use App\Models\CourseClass;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class ProfessorContext
{
    public static function teacher(): ?Teacher
    {
        /** @var User|null $u */
        $u = Auth::user();

        return $u?->teacher;
    }

    public static function teacherOrAbort(): Teacher
    {
        $t = self::teacher();
        abort_if($t === null, 403, 'Conta sem cadastro de docente. Solicite à secretaria.');

        return $t;
    }

    public static function requireDocentePainel(): void
    {
        $user = Auth::user();
        abort_unless($user && $user->accessesDocentePortal(), 403);
    }

    /**
     * @return list<int>
     */
    public static function assignedCourseClassIds(): array
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user || ! $user->accessesDocentePortal()) {
            return [];
        }

        if ($user->isTenantManager() || $user->hasTenantRole(User::TYPE_TENANT_MANAGER)) {
            return CourseClass::query()
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();
        }

        $t = self::teacher();
        if (! $t) {
            return [];
        }

        return $t->courseClasses()->pluck('course_classes.id')->map(fn ($id) => (int) $id)->values()->all();
    }
}
