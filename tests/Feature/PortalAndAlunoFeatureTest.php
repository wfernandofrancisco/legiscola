<?php

use App\Models\Certificate;
use App\Support\TenantWebEntryUrls;
use App\Models\Course;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
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
