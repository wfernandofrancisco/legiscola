<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseClassAnnouncement;
use App\Models\CourseClassAnnouncementDelivery;
use App\Models\CourseClassSchedule;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Cursos, turmas (CourseClass), matrículas, cronogramas, avisos com entregas,
 * aulas (ClassLesson) e ficha de presença (Attendance) para demonstração.
 *
 * Pré-requisitos: TenantSeeder; recomenda-se StudentSeeder (alunos LM-SEED-*)
 * e TeacherSeeder (professores). Este seeder complementa alunos até 600 se necessário.
 */
class CourseTurmasAttendanceSeeder extends Seeder
{
    private const DESCRIPTION_MARKER = 'SEED:academic_course_turmas_v1';

    private const STUDENTS_PER_TURMA = 15;

    private const TOTAL_TURMAS = 40;

    /** @var list<int> número de turmas por curso (12 cursos, soma 40) */
    private const TURMAS_POR_CURSO = [4, 4, 4, 4, 3, 3, 3, 3, 3, 3, 3, 3];

    public function run(): void
    {
        $tenant = Tenant::query()->orderBy('id')->first();
        if ($tenant === null) {
            $this->command?->warn('CourseTurmasAttendanceSeeder: nenhum tenant. Execute TenantSeeder antes.');

            return;
        }

        $markerCount = Course::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('description', 'like', self::DESCRIPTION_MARKER.'%')
            ->count();

        if ($markerCount >= 12) {
            $this->command?->info('CourseTurmasAttendanceSeeder: dados acadêmicos de seed já existem para este tenant.');

            return;
        }

        $teachers = Teacher::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('status', 'ativo')
            ->orderBy('id')
            ->get();

        if ($teachers->isEmpty()) {
            $this->command?->warn('CourseTurmasAttendanceSeeder: nenhum professor encontrado. Execute TeacherSeeder antes.');

            return;
        }

        $staffUser = User::query()
            ->where('tenant_id', $tenant->id)
            ->whereIn('user_type', [User::TYPE_TENANT_ADMIN, User::TYPE_TENANT_MANAGER])
            ->orderBy('id')
            ->first()
            ?? User::query()
                ->where('tenant_id', $tenant->id)
                ->orderBy('id')
                ->first();

        $totalStudentsNeeded = self::TOTAL_TURMAS * self::STUDENTS_PER_TURMA;
        $students = $this->ensureStudentPool($tenant, $totalStudentsNeeded);

        $courseNames = [
            'Direito Constitucional Aplicado',
            'Processo Legislativo Municipal',
            'Orçamento Público e Transparência',
            'Administração Legislativa',
            'Redação Normativa',
            'Ética e Conduta Parlamentar',
            'Comunicação e Relacionamento com a Sociedade',
            'Participação Popular e Audiências Públicas',
            'Gestão de Equipes na Câmara',
            'Legislação Urbana e Meio Ambiente',
            'Direitos Humanos e Políticas Públicas',
            'Planejamento Estratégico Institucional',
        ];

        DB::transaction(function () use ($tenant, $teachers, $staffUser, $students, $courseNames): void {
            $courses = [];
            foreach ($courseNames as $idx => $name) {
                $courses[] = Course::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $name,
                    'description' => self::DESCRIPTION_MARKER."\nCarga horária de exemplo para demonstração do painel.",
                    'workload_hours' => 40 + ($idx * 4),
                    'status' => 'ativo',
                    'admin_user_id' => $staffUser?->id,
                ]);
            }

            $turmasMeta = [];
            $globalTurma = 0;

            foreach ($courses as $cIdx => $course) {
                $nTurmas = self::TURMAS_POR_CURSO[$cIdx] ?? 3;
                for ($t = 1; $t <= $nTurmas; $t++) {
                    $globalTurma++;
                    $tipo = $globalTurma % 2 === 0 ? 'online' : 'presencial';
                    $turma = CourseClass::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'course_id' => $course->id,
                        'name' => sprintf('Turma %s — %02d', mb_substr($course->name, 0, 24), $t),
                        'tipo_turma' => $tipo,
                        'max_seats' => 30,
                        'enrollment_start' => now()->subMonths(2),
                        'enrollment_end' => now()->addMonth(),
                        'status' => 'em_andamento',
                    ]);

                    $teacher = $teachers[($globalTurma - 1) % $teachers->count()];
                    $turma->teachers()->attach($teacher->id, [
                        'tenant_id' => $tenant->id,
                        'sort_order' => 0,
                    ]);

                    $wdA = 2 + ($globalTurma % 4);
                    $wdB = ($wdA + 2) % 7;
                    CourseClassSchedule::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'course_class_id' => $turma->id,
                        'weekday' => $wdA,
                        'start_time' => '19:00',
                        'end_time' => '21:00',
                    ]);
                    CourseClassSchedule::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'course_class_id' => $turma->id,
                        'weekday' => $wdB,
                        'start_time' => '19:00',
                        'end_time' => '21:30',
                    ]);

                    $turmasMeta[] = ['course' => $course, 'turma' => $turma];
                }
            }

            $studentQueue = $students->values()->all();
            mt_srand(42_069);
            shuffle($studentQueue);
            $queueIndex = 0;

            foreach ($turmasMeta as $meta) {
                /** @var CourseClass $turma */
                $turma = $meta['turma'];

                for ($s = 0; $s < self::STUDENTS_PER_TURMA; $s++) {
                    if ($queueIndex >= count($studentQueue)) {
                        break 2;
                    }
                    $student = $studentQueue[$queueIndex];
                    $queueIndex++;

                    Enrollment::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'student_id' => $student->id,
                        'class_id' => null,
                        'course_class_id' => $turma->id,
                        'status' => 'cursando',
                        'observations' => null,
                    ]);
                }
            }

            $lessonDate = Carbon::now()->startOfWeek(Carbon::MONDAY)->subWeek();

            foreach ($turmasMeta as $meta) {
                $course = $meta['course'];
                $turma = $meta['turma'];

                $lesson = ClassLesson::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'course_class_id' => $turma->id,
                    'title' => 'Aula presencial — frequência (seed)',
                    'date' => $lessonDate->toDateString(),
                    'start_time' => '19:00:00',
                    'end_time' => '21:00:00',
                    'is_online' => $turma->tipo_turma === 'online',
                    'video_url' => null,
                    'material_url' => null,
                ]);

                $enrollments = Enrollment::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('course_class_id', $turma->id)
                    ->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca'])
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $isPresent = ($enrollment->id % 7) !== 0;

                    Attendance::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'class_lesson_id' => $lesson->id,
                        'class_schedule_id' => null,
                        'course_id' => $course->id,
                        'curriculum_id' => null,
                        'student_id' => $enrollment->student_id,
                        'class_date' => $lessonDate->toDateString(),
                        'status' => $isPresent ? 'presente' : 'falta',
                        'is_present' => $isPresent,
                        'recorded_by_user_id' => $staffUser?->id,
                    ]);
                }
            }

            foreach (array_slice($turmasMeta, 0, 8) as $meta) {
                $turma = $meta['turma'];

                $announcement = CourseClassAnnouncement::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'course_class_id' => $turma->id,
                    'reference_date' => $lessonDate->toDateString(),
                    'subject' => 'Aviso de aula (demonstração seed)',
                    'body' => 'Lembretes pedagógicos gerados pelo CourseTurmasAttendanceSeeder.',
                    'channels' => ['email'],
                    'consent_acknowledged' => true,
                    'created_by' => $staffUser?->id,
                    'processed_at' => now(),
                ]);

                $enrollments = Enrollment::withoutGlobalScopes()
                    ->where('tenant_id', $tenant->id)
                    ->where('course_class_id', $turma->id)
                    ->get();

                foreach ($enrollments as $enrollment) {
                    $student = $enrollment->student;
                    if ($student === null) {
                        continue;
                    }
                    $email = trim((string) ($student->email ?? $student->user?->email ?? ''));
                    CourseClassAnnouncementDelivery::withoutGlobalScopes()->create([
                        'tenant_id' => $tenant->id,
                        'course_class_announcement_id' => $announcement->id,
                        'enrollment_id' => $enrollment->id,
                        'student_id' => $student->id,
                        'channel' => 'email',
                        'destination' => $email !== '' ? $email : null,
                        'status' => $email !== '' ? 'sent' : 'skipped',
                        'error_message' => $email !== '' ? null : 'Sem e-mail no cadastro (seed).',
                    ]);
                }
            }
        });

        $this->command?->info(sprintf(
            'CourseTurmasAttendanceSeeder: %d cursos, %d turmas, matrículas (%d alunos/turma), cronogramas, aulas, presenças e avisos (8 turmas) criados.',
            count(self::TURMAS_POR_CURSO),
            self::TOTAL_TURMAS,
            self::STUDENTS_PER_TURMA
        ));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Student>
     */
    private function ensureStudentPool(Tenant $tenant, int $needed): \Illuminate\Support\Collection
    {
        $existing = Student::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->get();

        if ($existing->count() >= $needed) {
            return $existing->take($needed)->values();
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(
            ['name' => 'tenant_user', 'guard_name' => 'web'],
            ['type' => 'tenant']
        );

        $next = $existing->count() + 1;
        $toCreate = $needed - $existing->count();

        for ($k = 0; $k < $toCreate; $k++, $next++) {
            $email = sprintf('ac_aluno_seed_%d_%d@seed.invalid', $next, $tenant->id);

            $user = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => sprintf('Aluno Extra Seed %d', $next),
                'email' => $email,
                'password' => Hash::make('password'),
                'user_type' => User::TYPE_TENANT_USER,
                'status' => User::STATUS_ATIVO,
            ]);
            $user->assignRole('tenant_user');

            Student::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'email' => $email,
                'enrollment_number' => sprintf('AC-SEED-%05d', $next),
                'birth_date' => now()->subYears(22)->subDays($next % 200),
                'sexo' => 'nao_informado',
                'cpf' => $this->fakeUniqueCpfDigits($tenant->id, $next),
                'telefone' => null,
                'celular' => sprintf('(19) 9%04d-%04d', $next % 10000, ($next * 3) % 10000),
                'cep' => '13610-000',
                'logradouro' => 'Rua Seed Acadêmico',
                'numero' => (string) (($next % 900) + 1),
                'bairro' => 'Centro',
                'cidade' => 'Leme',
                'uf' => 'SP',
                'latitude' => -22.185556,
                'longitude' => -47.390278,
                'profissao' => null,
                'escolaridade' => null,
                'photo_path' => null,
                'status' => 'ativo',
            ]);
        }

        return Student::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->orderBy('id')
            ->take($needed)
            ->get()
            ->values();
    }

    private function fakeUniqueCpfDigits(int $tenantId, int $seq): string
    {
        $base = str_pad((string) (($tenantId * 1_000_000) + $seq % 99_999_999), 9, '0', STR_PAD_LEFT);

        return substr($base, 0, 3).'.'.substr($base, 3, 3).'.'.substr($base, 6, 3).'-'.sprintf('%02d', $seq % 100);
    }
}
