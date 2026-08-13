<?php

use App\Models\Course;
use App\Support\TenantWebEntryUrls;
use App\Models\CourseClass;
use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Integração multi-tenant entre os módulos Central, Admin e Docente.
 */

function isolationTenantHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

function isolationCreateTenant(string $slug): Tenant
{
    return Tenant::create([
        'name' => 'Tenant '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);
}

function isolationMakeTenantAdmin(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin '.$tenant->slug,
        'email' => 'admin-'.$tenant->slug.'-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_admin');

    return $user;
}

function isolationMakeProfessor(Tenant $tenant): User
{
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Professor '.$tenant->slug,
        'email' => 'prof-'.$tenant->slug.'-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_RESPONSIBLE,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('tenant_professor');

    return $user;
}

/** @return array{0: Course, 1: CourseClass} */
function isolationSeedCourseAndClass(Tenant $tenant): array
{
    $course = Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Curso teste '.$tenant->slug,
        'description' => null,
        'workload_hours' => 40,
        'status' => 'draft',
        'admin_user_id' => null,
    ]);

    $class = CourseClass::forceCreate([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Turma teste '.$tenant->slug,
        'tipo_turma' => 'presencial',
        'max_seats' => 30,
        'enrollment_start' => now()->subMonth(),
        'enrollment_end' => now()->addMonth(),
        'status' => 'em_andamento',
    ]);

    return [$course, $class];
}

function isolationAttachProfessorToClass(User $professor, CourseClass $class): void
{
    $teacher = Teacher::forceCreate([
        'tenant_id' => $professor->tenant_id,
        'user_id' => $professor->id,
        'full_name' => $professor->name,
        'email' => $professor->email,
        'status' => 'ativo',
        'bio' => null,
        'specialities' => null,
    ]);

    $teacher->courseClasses()->attach($class->id, [
        'tenant_id' => $professor->tenant_id,
        'sort_order' => 0,
    ]);
}

it('Central: super-admin acessa dashboard; admin de tenant não', function () {
    $tenantA = isolationCreateTenant('iso-cent-'.uniqid());
    $super = User::create([
        'tenant_id' => null,
        'name' => 'Super ISO',
        'email' => 'super-iso-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_SUPER_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $super->assignRole('central_super_admin');

    $adminA = isolationMakeTenantAdmin($tenantA);

    $this->actingAs($super)->get(route('central.dashboard'))->assertOk();

    $response = $this->actingAs($adminA)->get(route('central.dashboard'));
    $response->assertRedirect('/');
});

it('Admin: usuário do tenant A obtém 403 no subdomínio do tenant B', function () {
    $tenantA = isolationCreateTenant('iso-a-'.uniqid());
    $tenantB = isolationCreateTenant('iso-b-'.uniqid());
    $adminA = isolationMakeTenantAdmin($tenantA);

    $urlB = 'http://'.isolationTenantHost($tenantB).'/admin';

    $this->actingAs($adminA)->get($urlB)
        ->assertRedirect(TenantWebEntryUrls::tenantPanelLoginAbsolute());
});

it('Admin: admin vê turma do próprio tenant e 404 para id de outro tenant', function () {
    $tenantA = isolationCreateTenant('iso-adm-a-'.uniqid());
    $tenantB = isolationCreateTenant('iso-adm-b-'.uniqid());
    [, $classA] = isolationSeedCourseAndClass($tenantA);
    [, $classB] = isolationSeedCourseAndClass($tenantB);

    $adminA = isolationMakeTenantAdmin($tenantA);

    $hostA = isolationTenantHost($tenantA);
    $editOwn = 'http://'.$hostA.'/admin/escola/turmas/'.$classA->id.'/edit';

    $this->actingAs($adminA)->get($editOwn)->assertOk();

    $editForeignId = 'http://'.$hostA.'/admin/escola/turmas/'.$classB->id.'/edit';

    $this->actingAs($adminA)->get($editForeignId)->assertNotFound();
});

it('Docente: professor vinculado acessa turma; outra turma no mesmo tenant retorna 403', function () {
    $tenant = isolationCreateTenant('iso-doc-'.uniqid());
    [, $classAssigned] = isolationSeedCourseAndClass($tenant);
    [, $classOther] = isolationSeedCourseAndClass($tenant);

    $professor = isolationMakeProfessor($tenant);
    isolationAttachProfessorToClass($professor, $classAssigned);

    $host = isolationTenantHost($tenant);
    $base = 'http://'.$host;

    $this->actingAs($professor)->get($base.'/docente/turmas/'.$classAssigned->id)
        ->assertOk();

    $this->actingAs($professor)->get($base.'/docente/turmas/'.$classOther->id)
        ->assertForbidden();
});

it('Docente: professor não enxerga turma de outro tenant mesmo conhecendo o id', function () {
    $tenantA = isolationCreateTenant('iso-d2a-'.uniqid());
    $tenantB = isolationCreateTenant('iso-d2b-'.uniqid());
    [, $classB] = isolationSeedCourseAndClass($tenantB);

    $professorA = isolationMakeProfessor($tenantA);
    $hostA = isolationTenantHost($tenantA);

    $this->actingAs($professorA)->get('http://'.$hostA.'/docente/turmas/'.$classB->id)
        ->assertNotFound();
});
