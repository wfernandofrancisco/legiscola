<?php

namespace App\Services;

use App\Models\CatalogLesson;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Models\CourseLesson;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;

/**
 * Monta a grade da turma: cria/atualiza `class_lessons` (presença continua nesse id).
 */
class TurmaGradeService
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function materialize(CourseClass $turma, array $rows): void
    {
        foreach ($rows as $row) {
            if (! $this->rowHasSchedule($row)) {
                continue;
            }

            $this->createFromRow($turma, $row);
        }
    }

    /**
     * Atualiza datas da grade e inclui aulas do curso/catálogo que ainda não estão na turma.
     * Não apaga class_lessons (presença fica no mesmo id).
     *
     * @param  list<array<string, mixed>>  $rows
     */
    public function sync(CourseClass $turma, array $rows): void
    {
        foreach ($rows as $row) {
            if (! $this->rowHasSchedule($row)) {
                continue;
            }

            $existingId = (int) ($row['class_lesson_id'] ?? 0);
            if ($existingId > 0) {
                $lesson = ClassLesson::query()
                    ->withoutGlobalScopes()
                    ->where('course_class_id', $turma->id)
                    ->whereKey($existingId)
                    ->first();

                if ($lesson) {
                    $lesson->update([
                        'date' => $row['date'],
                        'start_time' => $row['start_time'],
                        'end_time' => $row['end_time'],
                        'is_online' => $this->rowIsOnline($turma, $row),
                    ]);

                    continue;
                }
            }

            $this->createFromRow($turma, $row);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function createFromRow(CourseClass $turma, array $row): void
    {
        $courseLessonId = (int) ($row['course_lesson_id'] ?? 0) ?: null;
        $catalogLessonId = (int) ($row['catalog_lesson_id'] ?? 0) ?: null;

        if ($courseLessonId && $this->alreadyMaterialized($turma, 'course_lesson_id', $courseLessonId)) {
            return;
        }

        if ($catalogLessonId && $this->alreadyMaterialized($turma, 'catalog_lesson_id', $catalogLessonId)) {
            return;
        }

        $courseLesson = $courseLessonId ? CourseLesson::query()->find($courseLessonId) : null;
        $catalogLesson = $catalogLessonId ? CatalogLesson::query()->find($catalogLessonId) : null;

        $title = $courseLesson?->title
            ?? $catalogLesson?->titulo
            ?? (trim((string) ($row['title'] ?? '')) ?: 'Aula');

        ClassLesson::create([
            'tenant_id' => $turma->tenant_id ?: TenantContext::getTenantId(),
            'course_class_id' => $turma->id,
            'course_lesson_id' => $courseLesson?->id,
            'catalog_lesson_id' => $catalogLesson?->id,
            'title' => $title,
            'date' => $row['date'],
            'start_time' => $row['start_time'],
            'end_time' => $row['end_time'],
            'is_online' => $this->rowIsOnline($turma, $row),
        ]);
    }

    private function alreadyMaterialized(CourseClass $turma, string $column, int $id): bool
    {
        return ClassLesson::query()
            ->withoutGlobalScopes()
            ->where('course_class_id', $turma->id)
            ->where($column, $id)
            ->exists();
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowHasSchedule(array $row): bool
    {
        return filled($row['date'] ?? null)
            && filled($row['start_time'] ?? null)
            && filled($row['end_time'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private function rowIsOnline(CourseClass $turma, array $row): bool
    {
        if (array_key_exists('is_online', $row) && $row['is_online'] !== null && $row['is_online'] !== '') {
            return in_array($row['is_online'], [true, 1, '1'], true);
        }

        return ($turma->tipo_turma ?? 'presencial') !== 'presencial';
    }

    /**
     * Prefill de datas a partir da 1ª aula + intervalo (usado no catálogo se a grade não vier completa).
     *
     * @param  \Illuminate\Support\Collection<int, CatalogLesson>  $lessons
     * @param  array<string, mixed>  $data
     * @return list<array<string, mixed>>
     */
    public function rowsFromCatalogFormula($lessons, array $data, bool $aulaOnline): array
    {
        $inicio = CarbonImmutable::parse($data['data_inicio']);
        $intervalo = max(1, (int) ($data['intervalo_dias'] ?? 7));
        $horaInicio = $data['hora_inicio'] ?? '19:00';
        $horaFim = $data['hora_fim'] ?? '21:00';
        $gradeById = collect($data['grade'] ?? [])->keyBy(fn ($row) => (int) ($row['catalog_lesson_id'] ?? 0));

        $rows = [];
        foreach ($lessons->values() as $indice => $aula) {
            $override = $gradeById->get($aula->id, []);
            $rows[] = [
                'catalog_lesson_id' => $aula->id,
                'date' => $override['date'] ?? $inicio->addDays($indice * $intervalo)->toDateString(),
                'start_time' => $override['start_time'] ?? $horaInicio,
                'end_time' => $override['end_time'] ?? $horaFim,
                'is_online' => array_key_exists('is_online', $override) && $override['is_online'] !== null && $override['is_online'] !== ''
                    ? in_array($override['is_online'], [true, 1, '1'], true)
                    : $aulaOnline,
            ];
        }

        return $rows;
    }
}
