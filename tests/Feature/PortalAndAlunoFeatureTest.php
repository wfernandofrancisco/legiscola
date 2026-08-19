<?php

use App\Models\Certificate;
use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantWebEntryUrls;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function paTenantHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

function paCreateTenant(string $slug): Tenant
{
    return Tenant::create([
        'name' => 'T '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);
}

it('portal: página validar certificado exibe formulário', function () {
    $tenant = paCreateTenant('pa-cert-'.uniqid());
    $host = paTenantHost($tenant);

    $this->get('http://'.$host.'/certificados/validar')
        ->assertOk()
        ->assertSee('Código de validação', false)
        ->assertSee('Consultar', false);
});

it('portal: consulta certificado válido e inválido', function () {
    $tenant = paCreateTenant('pa-val-'.uniqid());
    $host = paTenantHost($tenant);

    $course = Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Curso PA',
        'description' => null,
        'workload_hours' => 10,
        'status' => 'draft',
        'admin_user_id' => null,
    ]);

    $alunoUser = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno PA',
        'email' => 'aluno-pa-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $alunoUser->assignRole('tenant_user');

    $student = Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $alunoUser->id,
        'enrollment_number' => 'PA-'.fake()->unique()->numerify('######'),
    ]);

    $hash = 'pa-hash-'.bin2hex(random_bytes(16));

    Certificate::forceCreate([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'course_id' => $course->id,
        'certificate_template_id' => null,
        'validation_hash' => $hash,
        'issued_at' => now(),
        'status' => 'issued',
        'pdf_path' => null,
        'snapshot' => ['student_name' => 'Aluno PA', 'course_name' => 'Curso PA'],
    ]);

    $this->post('http://'.$host.'/certificados/validar', ['codigo' => $hash])
        ->assertOk()
        ->assertSee('Certificado válido', false)
        ->assertSee('Aluno PA', false);

    $this->post('http://'.$host.'/certificados/validar', ['codigo' => 'hash-inexistente-xyz'])
        ->assertOk()
        ->assertSee('Nenhum certificado encontrado', false);
});

it('portal: notícias index renderiza no layout do portal', function () {
    $tenant = paCreateTenant('pa-news-'.uniqid());
    $host = paTenantHost($tenant);

    $this->get('http://'.$host.'/noticias')
        ->assertOk();
});

it('aluno: dashboard renderiza para tenant_user', function () {
    $tenant = paCreateTenant('pa-aluno-'.uniqid());
    $host = paTenantHost($tenant);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno Dash',
        'email' => 'aluno-dash-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_user');

    $this->actingAs($user)->get('http://'.$host.'/aluno')
        ->assertOk()
        ->assertSee('Não encontramos seu cadastro de aluno', false);
});

it('aluno: tenant_admin não acessa área do aluno', function () {
    $tenant = paCreateTenant('pa-block-'.uniqid());
    $host = paTenantHost($tenant);

    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'email' => 'adm-block-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('tenant_admin');

    $this->actingAs($admin)->get('http://'.$host.'/aluno')
        ->assertRedirect(TenantWebEntryUrls::tenantPanelLoginAbsolute());
});

function paMakeAlunoUser(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Aluno Aula',
        'email' => 'aluno-aula-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_user');

    return $user;
}

function paSeedTurmaComAula(Tenant $tenant, User $alunoUser, string $enrollmentStatus = 'inscrito', ?int $lessonTenantId = null): array
{
    $student = Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $alunoUser->id,
        'enrollment_number' => 'AL-'.fake()->unique()->numerify('######'),
    ]);

    $course = Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Curso Aula',
        'description' => null,
        'workload_hours' => 10,
        'status' => 'draft',
        'admin_user_id' => null,
    ]);

    $courseClass = CourseClass::forceCreate([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Turma Aula',
        'tipo_turma' => 'online',
        'max_seats' => 30,
        'enrollment_start' => now()->subMonth(),
        'enrollment_end' => now()->addMonth(),
        'status' => 'em_andamento',
    ]);

    $lesson = ClassLesson::withoutGlobalScopes()->create([
        'tenant_id' => $lessonTenantId ?? $tenant->id,
        'course_class_id' => $courseClass->id,
        'title' => 'Aula 1 — Introdução',
        'date' => now()->toDateString(),
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
        'is_online' => true,
        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
    ]);

    Enrollment::forceCreate([
        'tenant_id' => $tenant->id,
        'student_id' => $student->id,
        'class_id' => null,
        'course_class_id' => $courseClass->id,
        'status' => $enrollmentStatus,
    ]);

    return [$student, $courseClass, $lesson];
}

it('aluno: inscrito abre a aula em vez de 404', function () {
    $tenant = paCreateTenant('pa-aula-'.uniqid());
    $host = paTenantHost($tenant);
    $user = paMakeAlunoUser($tenant);
    [, , $lesson] = paSeedTurmaComAula($tenant, $user, 'inscrito');

    $this->actingAs($user)
        ->get('http://'.$host.'/aluno/aulas/'.$lesson->id)
        ->assertOk()
        ->assertSee('Aula 1 — Introdução', false);
});

it('aluno: abre aula mesmo com tenant_id da class_lesson divergente', function () {
    $tenant = paCreateTenant('pa-aula-scope-'.uniqid());
    $outro = paCreateTenant('pa-aula-outro-'.uniqid());
    $host = paTenantHost($tenant);
    $user = paMakeAlunoUser($tenant);
    [, , $lesson] = paSeedTurmaComAula($tenant, $user, 'inscrito', $outro->id);

    $this->actingAs($user)
        ->get('http://'.$host.'/aluno/aulas/'.$lesson->id)
        ->assertOk()
        ->assertSee('Aula 1 — Introdução', false);
});

it('aluno: não inscrito na turma recebe 404 na aula', function () {
    $tenant = paCreateTenant('pa-aula-neg-'.uniqid());
    $host = paTenantHost($tenant);
    $user = paMakeAlunoUser($tenant);
    $outro = paMakeAlunoUser($tenant);
    [, , $lesson] = paSeedTurmaComAula($tenant, $outro, 'inscrito');

    Student::forceCreate([
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'enrollment_number' => 'AL-'.fake()->unique()->numerify('######'),
    ]);

    $this->actingAs($user)
        ->get('http://'.$host.'/aluno/aulas/'.$lesson->id)
        ->assertNotFound();
});

it('docente: tenant_admin não acessa painel docente', function () {
    $tenant = paCreateTenant('pa-doc-block-'.uniqid());
    $host = paTenantHost($tenant);

    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'email' => 'adm-doc-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('tenant_admin');

    $this->actingAs($admin)->get('http://'.$host.'/docente')->assertForbidden();
});
