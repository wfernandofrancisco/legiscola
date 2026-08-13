<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Orquestra dados de demonstração: professores, eventos, cursos/turmas/presenças.
 *
 * Ordem recomendada de dependências no banco: TenantSeeder → (StudentSeeder opcional)
 * → AcademicDemoDataSeeder (este chama TeacherSeeder antes do acadêmico).
 */
class AcademicDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            TeacherSeeder::class,
            EventSeeder::class,
            CourseTurmasAttendanceSeeder::class,
            ClassLessonSeeder::class,
        ]);
    }
}
