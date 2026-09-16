<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Exceptions\LicenseNotAvailableException;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\CatalogLicense;
use App\Models\ClassLesson;
use App\Models\Course;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LicenseActivationService;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function consumoTenant(string $slug, string $uf = 'SP'): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => $uf,
    ]);
}

function consumoDirector(string $uf = 'SP'): User
{
    $director = User::create([
        'tenant_id' => null,
        'name' => 'Diretor '.$uf,
        'email' => 'diretor-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_DIRECTOR,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $director->assignRole(User::TYPE_TENANT_DIRECTOR);
    DirectorUf::create(['user_id' => $director->id, 'uf' => $uf]);

    return $director;
}

function consumoAdmin(Tenant $tenant): User
{
    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin '.$tenant->slug,
        'email' => 'admin-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $admin->assignRole(User::TYPE_TENANT_ADMIN);

    return $admin;
}

function consumoLicense(User $director, Tenant $tenant, int $aulas = 3, array $overrides = []): CatalogLicense
{
    $item = CatalogItem::create([
        'owner_user_id' => $director->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Processo Legislativo',
        'descricao' => 'Conteúdo produzido pela direção regional',
        'workload_hours' => 12,
        'status' => CatalogItemStatus::Publicado,
    ]);

    for ($i = 1; $i <= $aulas; $i++) {
        CatalogLesson::create([
            'catalog_item_id' => $item->id,
            'ordem' => $i,
            'titulo' => "Aula {$i}",
            'video_url' => "https://www.youtube.com/watch?v=video{$i}",
            'material_url' => "https://exemplo.com/material{$i}.pdf",
        ]);
    }

    return CatalogLicense::create(array_merge([
        'catalog_item_id' => $item->id,
        'tenant_id' => $tenant->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
    ], $overrides));
}

function consumoTurmaPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Turma 1',
        'tipo_turma' => 'online',
        'max_seats' => 30,
        'data_inicio' => '2027-03-01',
        'intervalo_dias' => 7,
        'hora_inicio' => '19:00',
        'hora_fim' => '21:00',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    TenantContext::clear();
});

afterEach(function () {
    TenantContext::clear();
});

it('ativa a licença criando um curso ligado ao catálogo', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant);

    $course = app(LicenseActivationService::class)->activate($licenca, $tenant->id);

    expect($course->tenant_id)->toBe($tenant->id)
        ->and($course->catalog_item_id)->toBe($licenca->catalog_item_id)
        ->and($course->catalog_license_id)->toBe($licenca->id)
        ->and($course->name)->toBe('Processo Legislativo')
        ->and($course->isFromCatalog())->toBeTrue();
});

it('não duplica o curso ao ativar a mesma licença de novo', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant);

    $service = app(LicenseActivationService::class);
    $primeiro = $service->activate($licenca, $tenant->id);
    $segundo = $service->activate($licenca, $tenant->id);

    expect($segundo->id)->toBe($primeiro->id)
        ->and(Course::withoutGlobalScopes()->where('catalog_license_id', $licenca->id)->count())->toBe(1);
});

it('gera as aulas da turma a partir do catálogo com datas espaçadas', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 3);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    $aulas = ClassLesson::withoutGlobalScopes()
        ->where('course_class_id', $turma->id)
        ->orderBy('date')
        ->get();

    expect($aulas)->toHaveCount(3)
        ->and($aulas->pluck('title')->all())->toBe(['Aula 1', 'Aula 2', 'Aula 3'])
        ->and($aulas->pluck('date')->map->toDateString()->all())->toBe(['2027-03-01', '2027-03-08', '2027-03-15'])
        ->and($aulas->every(fn ($a) => $a->catalog_lesson_id !== null))->toBeTrue()
        ->and($aulas->every(fn ($a) => $a->tenant_id === $tenant->id))->toBeTrue();
});

it('lê o vídeo e o material da aula direto do catálogo', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    $aula = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->first();

    expect($aula->video_url)->toBeNull()
        ->and($aula->isFromCatalog())->toBeTrue()
        ->and($aula->effectiveVideoUrl())->toBe('https://www.youtube.com/watch?v=video1')
        ->and($aula->effectiveMaterialUrl())->toBe('https://exemplo.com/material1.pdf');
});

it('reproduz o MP4 enviado no catálogo como vídeo nativo na turma', function () {
    Storage::fake('public');

    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1);

    $catalogLesson = $licenca->catalogItem->lessons->first();
    $catalogLesson->update([
        'video_url' => null,
        'video_path' => 'catalog/videos/demo/aula.mp4',
        'video_provider' => 'upload',
    ]);
    Storage::disk('public')->put($catalogLesson->video_path, 'fake-mp4');

    $turma = app(LicenseActivationService::class)->openTurma($licenca->fresh(), $tenant->id, consumoTurmaPayload());
    $aula = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->first();
    $aula->load('catalogLesson');

    expect($aula->effectiveVideoIsNative())->toBeTrue()
        ->and($aula->effectiveVideoUrl())->toContain('/storage/catalog/videos/demo/aula.mp4');
});

it('deixa a câmara sobrepor o vídeo do catálogo com um link próprio', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    $aula = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->first();
    $aula->update(['video_url' => 'https://vimeo.com/999888']);

    expect($aula->fresh()->effectiveVideoUrl())->toBe('https://vimeo.com/999888');
});

it('corrigir o vídeo no catálogo reflete na aula da câmara', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    $licenca->catalogItem->lessons->first()->update(['video_url' => 'https://www.youtube.com/watch?v=corrigido']);

    $aula = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->first();

    expect($aula->effectiveVideoUrl())->toBe('https://www.youtube.com/watch?v=corrigido');
});

it('respeita o limite de turmas da licença', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1, overrides: ['max_turmas' => 1]);

    $service = app(LicenseActivationService::class);
    $service->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    expect($licenca->fresh()->turmasRestantes())->toBe(0);

    $service->openTurma($licenca->fresh(), $tenant->id, consumoTurmaPayload(['name' => 'Turma 2']));
})->throws(LicenseNotAvailableException::class);

it('bloqueia abrir turma de licença com prazo vencido', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1, overrides: [
        'exibir_ate' => now()->subDay()->toDateString(),
    ]);

    app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());
})->throws(LicenseNotAvailableException::class);

it('bloqueia abrir turma de curso sem aulas no catálogo', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 0);

    app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());
})->throws(LicenseNotAvailableException::class);

it('esconde o formulário de turma quando o curso do catálogo não tem aulas', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 0);

    $admin = consumoAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id)
        ->assertOk()
        ->assertSee('ainda não cadastrou as aulas deste curso')
        ->assertDontSee('Criar turma com as aulas');
});

it('marca as aulas como presenciais quando a turma do catálogo é presencial', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 2);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload([
        'tipo_turma' => 'presencial',
    ]));

    $aulas = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->get();

    expect($aulas)->toHaveCount(2)
        ->and($aulas->every(fn (ClassLesson $aula) => $aula->is_online === false))->toBeTrue();
});

it('mantém as aulas online quando a turma do catálogo é online', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 2);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());

    $aulas = ClassLesson::withoutGlobalScopes()->where('course_class_id', $turma->id)->get();

    expect($aulas->every(fn (ClassLesson $aula) => $aula->is_online === true))->toBeTrue();
});

it('bloqueia abrir turma de licença suspensa', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1, overrides: ['status' => 'suspensa']);

    app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload());
})->throws(LicenseNotAvailableException::class);

it('impede usar licença de outra câmara', function () {
    $araras = consumoTenant('araras-sp');
    $leme = consumoTenant('leme-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $araras, aulas: 1);

    app(LicenseActivationService::class)->openTurma($licenca, $leme->id, consumoTurmaPayload());
})->throws(LicenseNotAvailableException::class);

it('mostra ao admin apenas o conteúdo liberado para a própria câmara', function () {
    $araras = consumoTenant('araras-sp');
    $leme = consumoTenant('leme-sp');
    $director = consumoDirector();

    $licencaAraras = consumoLicense($director, $araras, aulas: 1);
    $licencaAraras->catalogItem->update(['titulo' => 'Conteudo de Araras']);

    $licencaLeme = consumoLicense($director, $leme, aulas: 1);
    $licencaLeme->catalogItem->update(['titulo' => 'Conteudo de Leme']);

    $admin = consumoAdmin($araras);
    $host = $araras->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional')
        ->assertOk()
        ->assertSee('Conteudo de Araras')
        ->assertSee(route('admin.catalogo-regional.show', $licencaAraras), false)
        ->assertDontSee(route('admin.catalogo-regional.show', $licencaLeme), false);
});

it('devolve 404 quando o admin abre licença de outra câmara', function () {
    $araras = consumoTenant('araras-sp');
    $leme = consumoTenant('leme-sp');
    $director = consumoDirector();

    $licencaLeme = consumoLicense($director, $leme, aulas: 1);

    $admin = consumoAdmin($araras);
    $host = $araras->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/'.$licencaLeme->id)
        ->assertNotFound();
});

it('abre a turma pela tela do admin', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 2);

    $admin = consumoAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id.'/turmas', consumoTurmaPayload())
        ->assertRedirect();

    $course = Course::withoutGlobalScopes()->where('catalog_license_id', $licenca->id)->first();

    expect($course)->not->toBeNull()
        ->and($course->courseClasses()->withoutGlobalScopes()->count())->toBe(1)
        ->and(ClassLesson::withoutGlobalScopes()->whereNotNull('catalog_lesson_id')->count())->toBe(2);
});

it('oculta no portal o curso do catálogo após exibir_ate', function () {
    $tenant = consumoTenant('araras-sp');
    $director = consumoDirector();
    $licenca = consumoLicense($director, $tenant, aulas: 1, overrides: [
        'exibir_ate' => now()->addMonth()->toDateString(),
    ]);

    $turma = app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, consumoTurmaPayload([
        'name' => 'Turma Portal Expirada',
    ]));
    $course = $turma->course;
    $course->update(['name' => 'Curso Portal Expirado']);

    $licenca->forceFill(['exibir_ate' => now()->subDay()->toDateString()])->save();

    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/cursos')
        ->assertOk()
        ->assertDontSee('Curso Portal Expirado');

    $this->get('http://'.$host.'/cursos/'.$course->id)
        ->assertNotFound();
});

it('lista cursos externos publicados do diretor da UF mesmo sem licença', function () {
    $tenant = consumoTenant('araras-ext-sp');
    $diretorSp = consumoDirector('SP');
    $diretorMg = consumoDirector('MG');

    $itemSp = CatalogItem::create([
        'owner_user_id' => $diretorSp->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Curso externo SP visivel',
        'status' => CatalogItemStatus::Publicado,
    ]);
    CatalogLesson::create([
        'catalog_item_id' => $itemSp->id,
        'ordem' => 1,
        'titulo' => 'Aula catalogo SP',
        'video_url' => 'https://www.youtube.com/watch?v=abc',
    ]);

    CatalogItem::create([
        'owner_user_id' => $diretorMg->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Curso externo MG oculto',
        'status' => CatalogItemStatus::Publicado,
    ]);

    CatalogItem::create([
        'owner_user_id' => $diretorSp->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Rascunho SP oculto',
        'status' => CatalogItemStatus::Rascunho,
    ]);

    $admin = consumoAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Cursos externos disponíveis', false);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional')
        ->assertOk()
        ->assertSee('Curso externo SP visivel', false)
        ->assertSee('Ver catálogo', false)
        ->assertDontSee('Curso externo MG oculto', false)
        ->assertDontSee('Rascunho SP oculto', false);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/itens/'.$itemSp->id)
        ->assertOk()
        ->assertSee('Aula catalogo SP', false)
        ->assertSee('ainda não foi liberado', false);
});

it('nao abre catalogo de diretor de outra UF', function () {
    $tenant = consumoTenant('araras-ext-mg-block');
    $diretorMg = consumoDirector('MG');

    $itemMg = CatalogItem::create([
        'owner_user_id' => $diretorMg->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Curso MG bloqueado',
        'status' => CatalogItemStatus::Publicado,
    ]);

    $admin = consumoAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/itens/'.$itemMg->id)
        ->assertNotFound();
});
