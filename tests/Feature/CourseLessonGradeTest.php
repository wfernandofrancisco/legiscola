<?php

use App\Models\Attendance;
use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseLesson;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CourseClassService;
use App\Services\CourseLessonService;
use App\Services\LicenseActivationService;
use App\Services\TurmaGradeService;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

function gradeTenant(string $slug): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'SP',
    ]);
}

function gradeAdmin(Tenant $tenant): User
{
    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Grade',
        'email' => 'admin-grade-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('tenant_admin');

    return $admin;
}

function gradeCourse(Tenant $tenant): Course
{
    return Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Processo Legislativo',
        'description' => null,
        'workload_hours' => 12,
        'status' => 'ativo',
        'admin_user_id' => null,
    ]);
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

it('reusa as aulas do curso em duas turmas com agendas diferentes', function () {
    $tenant = gradeTenant('grade-'.uniqid());
    TenantContext::set($tenant->id);
    $course = gradeCourse($tenant);

    $aula1 = app(CourseLessonService::class)->create($course, [
        'title' => 'História do legislativo',
        'video_url' => 'https://www.youtube.com/watch?v=aaaaaaa',
    ]);
    $aula2 = app(CourseLessonService::class)->create($course, [
        'title' => 'Redação de leis',
        'video_url' => 'https://www.youtube.com/watch?v=bbbbbbb',
    ]);

    $payload = fn (string $name, string $d1, string $d2): array => [
        'course_id' => $course->id,
        'name' => $name,
        'tipo_turma' => 'online',
        'max_seats' => 30,
        'enrollment_start' => '2027-01-01 08:00:00',
        'enrollment_end' => '2027-02-01 18:00:00',
        'status' => 'inscricao',
        'grade' => [
            ['course_lesson_id' => $aula1->id, 'date' => $d1, 'start_time' => '19:00', 'end_time' => '21:00', 'is_online' => '1'],
            ['course_lesson_id' => $aula2->id, 'date' => $d2, 'start_time' => '19:00', 'end_time' => '21:00', 'is_online' => '1'],
        ],
    ];

    $turmaA = app(CourseClassService::class)->create($payload('Turma manhã', '2027-03-01', '2027-03-08'));
    $turmaB = app(CourseClassService::class)->create($payload('Turma noite', '2027-04-01', '2027-04-15'));

    $aulasA = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turmaA->id)->orderBy('date')->get();
    $aulasB = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turmaB->id)->orderBy('date')->get();

    expect($aulasA)->toHaveCount(2)
        ->and($aulasB)->toHaveCount(2)
        ->and($aulasA->pluck('course_lesson_id')->all())->toBe([$aula1->id, $aula2->id])
        ->and($aulasB->pluck('course_lesson_id')->all())->toBe([$aula1->id, $aula2->id])
        ->and($aulasA->pluck('date')->map->toDateString()->all())->toBe(['2027-03-01', '2027-03-08'])
        ->and($aulasB->pluck('date')->map->toDateString()->all())->toBe(['2027-04-01', '2027-04-15'])
        ->and($aulasA->first()->id)->not->toBe($aulasB->first()->id)
        ->and($aulasA->first()->effectiveVideoUrl())->toBe('https://www.youtube.com/watch?v=aaaaaaa');
});

it('mantém o id da aula da grade ao mudar a data (presença no mesmo registro)', function () {
    $tenant = gradeTenant('presenca-'.uniqid());
    TenantContext::set($tenant->id);
    $course = gradeCourse($tenant);
    $content = app(CourseLessonService::class)->create($course, ['title' => 'Aula 1']);

    $turma = app(CourseClassService::class)->create([
        'course_id' => $course->id,
        'name' => 'Turma 1',
        'tipo_turma' => 'presencial',
        'max_seats' => 20,
        'enrollment_start' => '2027-01-01 08:00:00',
        'enrollment_end' => '2027-02-01 18:00:00',
        'status' => 'em_andamento',
        'schedules' => [['weekday' => 2, 'start_time' => '19:00', 'end_time' => '21:00']],
        'grade' => [
            ['course_lesson_id' => $content->id, 'date' => '2027-03-02', 'start_time' => '19:00', 'end_time' => '21:00', 'is_online' => '0'],
        ],
    ]);

    $aula = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->first();
    $idOriginal = $aula->id;

    $aluno = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno Grade',
        'email' => 'aluno-grade-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $student = \App\Models\Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $aluno->id,
        'enrollment_number' => 'GR-'.fake()->unique()->numerify('######'),
        'status' => 'ativo',
    ]);

    Attendance::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'course_id' => $course->id,
        'class_lesson_id' => $aula->id,
        'class_date' => '2027-03-02',
        'status' => 'presente',
        'is_present' => true,
    ]);

    app(TurmaGradeService::class)->sync($turma, [[
        'class_lesson_id' => $aula->id,
        'course_lesson_id' => $content->id,
        'date' => '2027-03-09',
        'start_time' => '20:00',
        'end_time' => '22:00',
        'is_online' => '0',
    ]]);

    $aula->refresh();

    expect($aula->id)->toBe($idOriginal)
        ->and($aula->date->toDateString())->toBe('2027-03-09')
        ->and(Attendance::withoutGlobalScopes()->where('class_lesson_id', $idOriginal)->count())->toBe(1);
});

it('abre turma do catálogo com horário diferente por aula', function () {
    $tenant = consumoTenant('grade-cat-'.uniqid());
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 2);
    $lessons = $licenca->catalogItem->lessons()->orderBy('ordem')->get();

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, [
        'name' => 'Turma regional',
        'tipo_turma' => 'online',
        'max_seats' => 20,
        'data_inicio' => '2027-05-01',
        'intervalo_dias' => 7,
        'hora_inicio' => '19:00',
        'hora_fim' => '21:00',
        'grade' => [
            [
                'catalog_lesson_id' => $lessons[0]->id,
                'date' => '2027-05-03',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'is_online' => '1',
            ],
            [
                'catalog_lesson_id' => $lessons[1]->id,
                'date' => '2027-05-10',
                'start_time' => '14:00',
                'end_time' => '16:00',
                'is_online' => '0',
            ],
        ],
    ]);

    $aulas = ClassLesson::withoutGlobalScopes()
        ->where('course_class_id', $turma->id)
        ->orderBy('date')
        ->get();

    expect($aulas)->toHaveCount(2)
        ->and($aulas[0]->date->toDateString())->toBe('2027-05-03')
        ->and(substr((string) $aulas[0]->start_time, 0, 5))->toBe('09:00')
        ->and($aulas[1]->is_online)->toBeFalse()
        ->and($aulas[0]->catalog_lesson_id)->toBe($lessons[0]->id);
});
