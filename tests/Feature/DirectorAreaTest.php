<?php

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\DirectorUf;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DirectorInsightsService;
use App\Support\DirectorContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * A área /diretor roda sem TenantContext, então o TenantScope não protege nada.
 * Estes testes garantem que a abrangência por UF é o que segura o isolamento.
 */
function directorCreateTenant(string $slug, string $uf): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'cidade' => 'Cidade '.$slug,
        'estado' => $uf,
    ]);
}

/**
 * @param  list<string>  $ufs
 */
function directorCreateDirector(array $ufs): User
{
    $director = User::create([
        'tenant_id' => null,
        'name' => 'Diretor '.implode('', $ufs ?: ['sem-uf']),
        'email' => 'diretor-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_DIRECTOR,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $director->assignRole(User::TYPE_TENANT_DIRECTOR);

    foreach ($ufs as $uf) {
        DirectorUf::create(['user_id' => $director->id, 'uf' => $uf]);
    }

    return $director;
}

/**
 * Cria curso + turma + N matrículas para uma câmara, para os agregados terem o que somar.
 */
function directorSeedActivity(Tenant $tenant, string $status = 'em_andamento', int $matriculas = 2): Course
{
    $course = Course::forceCreate([
        'tenant_id' => $tenant->id,
        'name' => 'Curso de '.$tenant->slug,
        'description' => null,
        'workload_hours' => 20,
        'status' => 'ativo',
        'admin_user_id' => null,
    ]);

    $turma = CourseClass::forceCreate([
        'tenant_id' => $tenant->id,
        'course_id' => $course->id,
        'name' => 'Turma de '.$tenant->slug,
        'tipo_turma' => 'presencial',
        'max_seats' => 30,
        'enrollment_start' => now()->subMonth(),
        'enrollment_end' => now()->addMonth(),
        'status' => $status,
    ]);

    for ($i = 0; $i < $matriculas; $i++) {
        $alunoUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Aluno '.$i.' '.$tenant->slug,
            'email' => 'aluno-'.fake()->unique()->safeEmail(),
            'password' => Hash::make('password'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
            'email_verified_at' => now(),
        ]);

        $student = Student::forceCreate([
            'tenant_id' => $tenant->id,
            'user_id' => $alunoUser->id,
            'email' => $alunoUser->email,
            'enrollment_number' => $tenant->id.'-'.$i.'-'.uniqid(),
            'status' => 'ativo',
        ]);

        Enrollment::forceCreate([
            'tenant_id' => $tenant->id,
            'student_id' => $student->id,
            'course_class_id' => $turma->id,
            'status' => 'cursando',
        ]);
    }

    return $course;
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('manda visitante do /diretor para o login do diretor', function () {
    $this->get('/diretor')->assertRedirect(route('diretor.login'));
});

it('bloqueia admin de tenant na área do diretor', function () {
    $tenant = directorCreateTenant('araras-sp', 'SP');

    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'email' => 'admin-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole(User::TYPE_TENANT_ADMIN);

    $this->actingAs($admin)->get('/diretor')->assertRedirect('/');
});

it('bloqueia diretor sem nenhuma UF atribuída', function () {
    $director = directorCreateDirector([]);

    $this->actingAs($director)->get('/diretor')->assertForbidden();
});

it('mostra apenas as câmaras das UFs do diretor', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    $spDois = directorCreateTenant('leme-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');

    $director = directorCreateDirector(['SP']);

    $response = $this->actingAs($director)->get('/diretor');

    $response->assertOk()
        ->assertSee($sp->name)
        ->assertSee($spDois->name)
        ->assertDontSee($mg->name);
});

it('acumula as câmaras quando o diretor responde por mais de uma UF', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');
    $pr = directorCreateTenant('curitiba-pr', 'PR');

    $director = directorCreateDirector(['SP', 'MG']);

    $response = $this->actingAs($director)->get('/diretor');

    $response->assertOk()
        ->assertSee($sp->name)
        ->assertSee($mg->name)
        ->assertDontSee($pr->name);
});

it('restringe DirectorContext aos tenants da abrangência', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');

    $director = directorCreateDirector(['SP']);

    $this->actingAs($director);

    DirectorContext::forget();

    expect(DirectorContext::ufs())->toBe(['SP'])
        ->and(DirectorContext::tenantIds())->toBe([$sp->id])
        ->and(DirectorContext::allows($sp->id))->toBeTrue()
        ->and(DirectorContext::allows($mg->id))->toBeFalse();
});

it('soma os agregados apenas das câmaras da abrangência', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');

    directorSeedActivity($sp, 'em_andamento', 3);
    directorSeedActivity($mg, 'em_andamento', 7);

    $director = directorCreateDirector(['SP']);
    $this->actingAs($director);
    DirectorContext::forget();

    $service = app(DirectorInsightsService::class);
    $resumo = $service->summary();

    // Só as 3 matrículas de SP; as 7 de MG ficam de fora.
    expect($resumo['camaras'])->toBe(1)
        ->and($resumo['matriculas'])->toBe(3)
        ->and($resumo['alunos'])->toBe(3)
        ->and($resumo['cursos'])->toBe(1)
        ->and($resumo['turmas_em_andamento'])->toBe(1);
});

it('separa as turmas por situação no panorama', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');

    directorSeedActivity($sp, 'inscricao', 1);
    directorSeedActivity($sp, 'em_andamento', 1);
    directorSeedActivity($sp, 'concluido', 1);

    $director = directorCreateDirector(['SP']);
    $this->actingAs($director);
    DirectorContext::forget();

    $resumo = app(DirectorInsightsService::class)->summary();

    expect($resumo['turmas_inscricao'])->toBe(1)
        ->and($resumo['turmas_em_andamento'])->toBe(1)
        ->and($resumo['turmas_concluidas'])->toBe(1);
});

it('abre a ficha de uma câmara da região', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    directorSeedActivity($sp);

    $director = directorCreateDirector(['SP']);

    $this->actingAs($director)
        ->get(route('diretor.clientes.show', $sp))
        ->assertOk()
        ->assertSee($sp->name);
});

it('devolve 404 na ficha de câmara fora da abrangência', function () {
    directorCreateTenant('araras-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');
    directorSeedActivity($mg);

    $director = directorCreateDirector(['SP']);

    $this->actingAs($director)
        ->get(route('diretor.clientes.show', $mg))
        ->assertNotFound();
});

it('não vaza câmara de outra UF na listagem', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    $mg = directorCreateTenant('uberaba-mg', 'MG');

    $director = directorCreateDirector(['SP']);

    $this->actingAs($director)
        ->get(route('diretor.clientes.index'))
        ->assertOk()
        ->assertSee($sp->name)
        ->assertDontSee($mg->name);
});

it('não expõe nome de aluno no painel do diretor', function () {
    $sp = directorCreateTenant('araras-sp', 'SP');
    directorSeedActivity($sp, 'em_andamento', 2);

    $aluno = User::where('user_type', User::TYPE_TENANT_USER)->first();

    $director = directorCreateDirector(['SP']);

    $this->actingAs($director)
        ->get(route('diretor.clientes.show', $sp))
        ->assertOk()
        ->assertDontSee($aluno->name);
});

it('não dá abrangência a quem não é diretor', function () {
    directorCreateTenant('araras-sp', 'SP');

    $tenant = Tenant::first();
    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'email' => 'admin-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin);

    DirectorContext::forget();

    expect(DirectorContext::tenantIds())->toBe([])
        ->and(DirectorContext::hasScope())->toBeFalse();
});
