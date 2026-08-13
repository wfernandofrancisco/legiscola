<?php

namespace App\Contracts\Repositories;

use App\Models\ClassLesson;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ClassLessonRepositoryInterface
{
    /**
     * @param  list<int>|null  $onlyCourseClassIds  When set, only aulas destas turmas.
     */
    public function paginateFiltered(int $perPage = 15, ?string $search = null, ?int $courseClassId = null, ?array $onlyCourseClassIds = null): LengthAwarePaginator;
    public function create(array $data): ClassLesson;
    public function update(ClassLesson $classLesson, array $data): bool;
    public function delete(ClassLesson $classLesson): bool;
}
