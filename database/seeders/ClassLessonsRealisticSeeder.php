<?php

namespace Database\Seeders;

use App\Models\ClassLesson;
use App\Models\CourseClass;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Cria aulas com títulos reais de programa típico de Escola do Legislativo / Câmara Municipal.
 *
 * Usa apenas turmas (course_classes) já existentes. Turmas que já têm alguma aula são ignoradas
 * (para não duplicar). Não usa Faker.
 *
 * Uso: php artisan db:seed --class=ClassLessonsRealisticSeeder
 */
class ClassLessonsRealisticSeeder extends Seeder
{
    /**
     * Encontro semanal (terça) com horário fixo de curso noturno.
     *
     * @return list<array{title: string, week_offset: int, is_online: bool|null}>
     */
    private function curriculum(): array
    {
        return [
            ['title' => 'Abertura — papel da Câmara Municipal na democracia representativa', 'week_offset' => 0, 'is_online' => null],
            ['title' => 'Competências legislativas e financeiras do Município (Constituição e Lei Orgânica)', 'week_offset' => 1, 'is_online' => null],
            ['title' => 'Mesa Diretora, lideranças partidárias e blocos parlamentares', 'week_offset' => 2, 'is_online' => null],
            ['title' => 'Projeto de lei: origem, redação, emendas e substitutivos', 'week_offset' => 3, 'is_online' => null],
            ['title' => 'Lei complementar, decreto legislativo e resolução: diferenças e exemplos práticos', 'week_offset' => 4, 'is_online' => null],
            ['title' => 'Tramitação interna, prazos regimentais e publicidade dos atos', 'week_offset' => 5, 'is_online' => null],
            ['title' => 'Fiscalização pelo Legislativo: requerimentos, convocações e tribuna popular', 'week_offset' => 6, 'is_online' => null],
            ['title' => 'Orçamento municipal, emendas parlamentares e transparência (LAI)', 'week_offset' => 7, 'is_online' => null],
            ['title' => 'Audiências públicas, conselhos municipais e participação social', 'week_offset' => 8, 'is_online' => null],
            ['title' => 'Comissões técnicas, pareceres e acompanhamento de políticas públicas', 'week_offset' => 9, 'is_online' => null],
            ['title' => 'Ética no exercício do mandato e decoro parlamentar', 'week_offset' => 10, 'is_online' => null],
            ['title' => 'Encerramento — certificação, avaliação e continuidade de estudos', 'week_offset' => 11, 'is_online' => null],
        ];
    }

    public function run(): void
    {
        $curriculum = $this->curriculum();

        $turmas = CourseClass::query()
            ->orderBy('tenant_id')
            ->orderBy('id')
            ->get();

        if ($turmas->isEmpty()) {
            $this->command?->warn('ClassLessonsRealisticSeeder: nenhuma turma encontrada. Cadastre turmas antes.');

            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($turmas as $turma) {
            $existing = ClassLesson::query()
                ->where('course_class_id', $turma->id)
                ->exists();

            if ($existing) {
                $skipped++;

                continue;
            }

            $tipoOnline = $turma->tipo_turma === 'online';
            $firstTuesday = $this->anchorTuesdayForTurma($turma->id);

            foreach ($curriculum as $row) {
                $date = $firstTuesday->copy()->addWeeks($row['week_offset']);
                $isOnline = $row['is_online'] ?? $tipoOnline;

                ClassLesson::withoutGlobalScopes()->create([
                    'tenant_id' => $turma->tenant_id,
                    'course_class_id' => $turma->id,
                    'title' => $row['title'],
                    'date' => $date->toDateString(),
                    'start_time' => '19:00:00',
                    'end_time' => '21:30:00',
                    'is_online' => $isOnline,
                    'video_url' => null,
                    'material_url' => null,
                ]);
                $created++;
            }
        }

        $this->command?->info("ClassLessonsRealisticSeeder: {$created} aula(s) criada(s); {$skipped} turma(s) ignorada(s) (já tinham aulas).");
    }

    /**
     * Primeira terça-feira da série, defasada por turma (em semanas) para espalhar calendários.
     */
    private function anchorTuesdayForTurma(int $turmaId): Carbon
    {
        $d = Carbon::now()->startOfDay()->subDays(90);
        while (! $d->isTuesday()) {
            $d->addDay();
        }
        $d->addWeeks($turmaId % 5);

        return $d->startOfDay();
    }
}
