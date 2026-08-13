<?php

namespace Database\Seeders;

use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Garante um mínimo de aulas (class_lessons) por turma.
 *
 * Antes: só criava quando a turma tinha **zero** aulas — após o seed de turmas/cursos
 * (1 aula por turma), este seeder não fazia nada.
 *
 * Agora: para cada turma com menos de {@see TARGET_LESSONS_PER_CLASS} aulas,
 * cria as faltantes (datas após a última aula já cadastrada, ou a partir da semana passada).
 */
class ClassLessonSeeder extends Seeder
{
    /** Meta de aulas por turma após o seed (completar até este número). */
    private const TARGET_LESSONS_PER_CLASS = 6;

    public function run(): void
    {
        $tenants = Tenant::query()->orderBy('id')->get();
        if ($tenants->isEmpty()) {
            $this->command?->warn('ClassLessonSeeder: nenhum tenant. Execute TenantSeeder antes.');

            return;
        }

        $created = 0;
        $turmasTotal = 0;
        $turmasTopped = 0;
        $turmasAlreadyFull = 0;

        foreach ($tenants as $tenant) {
            $turmas = CourseClass::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->get();

            foreach ($turmas as $turma) {
                $turmasTotal++;
                $count = ClassLesson::withoutGlobalScopes()
                    ->where('course_class_id', $turma->id)
                    ->count();

                if ($count >= self::TARGET_LESSONS_PER_CLASS) {
                    $turmasAlreadyFull++;

                    continue;
                }

                $need = self::TARGET_LESSONS_PER_CLASS - $count;
                $turmasTopped++;

                $latest = ClassLesson::withoutGlobalScopes()
                    ->where('course_class_id', $turma->id)
                    ->orderByDesc('date')
                    ->orderByDesc('id')
                    ->first();

                $defaultBase = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeek();
                $cursor = ($latest && $latest->date)
                    ? Carbon::parse($latest->date)->startOfDay()->addDay()
                    : $defaultBase->copy();

                for ($i = 0; $i < $need; $i++) {
                    $d = $cursor->copy()->addDays($i);
                    $lessonNumber = $count + $i + 1;

                    ClassLesson::withoutGlobalScopes()->create([
                        'tenant_id' => (int) $turma->tenant_id,
                        'course_class_id' => $turma->id,
                        'title' => 'Aula '.$lessonNumber.' (seed) — '.$turma->name,
                        'date' => $d->toDateString(),
                        'start_time' => '19:00:00',
                        'end_time' => '21:00:00',
                        'is_online' => ($turma->tipo_turma ?? 'presencial') === 'online',
                        'video_url' => null,
                        'material_url' => null,
                    ]);
                    $created++;
                }
            }
        }

        if ($turmasTotal === 0) {
            $this->command?->warn('ClassLessonSeeder: nenhuma turma (course_classes) encontrada em nenhum tenant.');
        }

        $this->command?->info(sprintf(
            'ClassLessonSeeder: %d aula(s) criada(s). Turmas: %d total; %d completadas até %d aula(s); %d já tinham %d+ aula(s).',
            $created,
            $turmasTotal,
            $turmasTopped,
            self::TARGET_LESSONS_PER_CLASS,
            $turmasAlreadyFull,
            self::TARGET_LESSONS_PER_CLASS
        ));
    }
}
