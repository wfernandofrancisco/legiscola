<?php

use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware(PreventRequestForgery::class);
});

function aulaEditHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

it('admin edita aula mesmo com tenant_id da class_lesson divergente', function () {
    $tenant = Tenant::create([
        'name' => 'Câmara Aula Edit',
        'slug' => 'aula-edit-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'SP',
    ]);

    $outro = Tenant::create([
        'name' => 'Outra Câmara',
        'slug' => 'aula-outro-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'MG',
    ]);

    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Aula',
        'email' => 'admin-aula-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('tenant_admin');

    $course = Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Curso Aula',
        'description' => null,
        'workload_hours' => 10,
        'status' => 'draft',
        'admin_user_id' => null,
    ]);

    $turma = CourseClass::forceCreate([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Turma Aula',
        'tipo_turma' => 'presencial',
        'max_seats' => 30,
        'enrollment_start' => now()->subMonth(),
        'enrollment_end' => now()->addMonth(),
        'status' => 'em_andamento',
    ]);

    $lesson = ClassLesson::withoutGlobalScopes()->create([
        'tenant_id' => $outro->id,
        'course_class_id' => $turma->id,
        'title' => 'Aula com tenant_id errado',
        'date' => now()->toDateString(),
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
        'is_online' => false,
    ]);

    $host = aulaEditHost($tenant);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/aulas/'.$lesson->id.'/edit')
        ->assertOk()
        ->assertSee('Aula com tenant_id errado', false);
});

it('admin não edita aula de turma de outro tenant', function () {
    $tenantA = Tenant::create([
        'name' => 'Câmara A',
        'slug' => 'aula-a-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'SP',
    ]);

    $tenantB = Tenant::create([
        'name' => 'Câmara B',
        'slug' => 'aula-b-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => 'MG',
    ]);

    $adminA = User::create([
        'tenant_id' => $tenantA->id,
        'name' => 'Admin A',
        'email' => 'admin-a-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $adminA->assignRole('tenant_admin');

    $courseB = Course::forceCreate([
        'tenant_id' => $tenantB->id,
        'name' => 'Curso B',
        'description' => null,
        'workload_hours' => 10,
        'status' => 'draft',
        'admin_user_id' => null,
    ]);

    $turmaB = CourseClass::forceCreate([
        'tenant_id' => $tenantB->id,
        'course_id' => $courseB->id,
        'name' => 'Turma B',
        'tipo_turma' => 'presencial',
        'max_seats' => 30,
        'enrollment_start' => now()->subMonth(),
        'enrollment_end' => now()->addMonth(),
        'status' => 'em_andamento',
    ]);

    $lessonB = ClassLesson::withoutGlobalScopes()->create([
        'tenant_id' => $tenantB->id,
        'course_class_id' => $turmaB->id,
        'title' => 'Aula de outra câmara',
        'date' => now()->toDateString(),
        'start_time' => '19:00:00',
        'end_time' => '21:00:00',
        'is_online' => false,
    ]);

    $this->actingAs($adminA)
        ->get('http://'.aulaEditHost($tenantA).'/admin/escola/aulas/'.$lessonB->id.'/edit')
        ->assertNotFound();
});
