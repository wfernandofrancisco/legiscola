<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Exceptions\LicenseNotAvailableException;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\CatalogLicense;
use App\Models\DirectorUf;
use App\Models\Event;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LicenseActivationService;
use App\Support\TenantContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

function palestraTenant(string $slug, string $uf = 'SP'): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => $uf,
        'cidade' => 'Araras',
    ]);
}

function palestraDirector(string $uf = 'SP'): User
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

function palestraAdmin(Tenant $tenant): User
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

function palestraLicense(
    User $director,
    Tenant $tenant,
    CatalogItemTipo $tipo = CatalogItemTipo::Palestra,
    array $overrides = []
): CatalogLicense {
    $item = CatalogItem::create([
        'owner_user_id' => $director->id,
        'tipo' => $tipo,
        'titulo' => 'LGPD no Legislativo',
        'resumo' => 'Palestra de 2 horas',
        'descricao' => 'Conteúdo produzido pela direção regional',
        'status' => CatalogItemStatus::Publicado,
    ]);

    return CatalogLicense::create(array_merge([
        'catalog_item_id' => $item->id,
        'tenant_id' => $tenant->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
    ], $overrides));
}

function palestraPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'LGPD no Legislativo — 1ª edição',
        'date_time' => '2027-05-20 19:00',
        'palestrante_nome' => 'Dra. Ana',
        'max_seats' => 120,
        'allow_online_registration' => '1',
        'com_certificado' => '1',
        'city' => 'Araras',
        'state' => 'SP',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    TenantContext::clear();
});

it('agenda a palestra como evento da câmara ligado ao catálogo', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload());

    expect($evento->tenant_id)->toBe($tenant->id)
        ->and($evento->catalog_item_id)->toBe($licenca->catalog_item_id)
        ->and($evento->catalog_license_id)->toBe($licenca->id)
        ->and($evento->isFromCatalog())->toBeTrue()
        ->and($evento->title)->toBe('LGPD no Legislativo — 1ª edição')
        ->and($evento->palestrante_nome)->toBe('Dra. Ana')
        ->and($evento->max_seats)->toBe(120)
        ->and($evento->date_time->format('Y-m-d H:i'))->toBe('2027-05-20 19:00');
});

it('usa título e descrição do catálogo quando a câmara não informa', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    $evento = app(LicenseActivationService::class)
        ->openEvento($licenca, $tenant->id, ['date_time' => '2027-05-20 19:00']);

    expect($evento->title)->toBe('LGPD no Legislativo')
        ->and($evento->description)->toBe('Conteúdo produzido pela direção regional');
});

it('não cria curso nem turma ao agendar uma palestra', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload());

    expect($licenca->fresh()->course)->toBeNull();
});

it('respeita o limite de edições da licença de palestra', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: ['max_turmas' => 2]);
    $service = app(LicenseActivationService::class);

    $service->openEvento($licenca, $tenant->id, palestraPayload());
    $service->openEvento($licenca->fresh(), $tenant->id, palestraPayload());

    expect(fn () => $service->openEvento($licenca->fresh(), $tenant->id, palestraPayload()))
        ->toThrow(LicenseNotAvailableException::class);

    expect(Event::withoutGlobalScopes()->count())->toBe(2);
});

it('bloqueia agendar palestra de licença com prazo vencido', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: ['exibir_ate' => now()->subDay()->toDateString()]);

    expect(fn () => app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload()))
        ->toThrow(LicenseNotAvailableException::class);
});

it('impede agendar palestra com licença de outra câmara', function () {
    $araras = palestraTenant('araras-sp');
    $leme = palestraTenant('leme-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $leme);

    expect(fn () => app(LicenseActivationService::class)->openEvento($licenca, $araras->id, palestraPayload()))
        ->toThrow(LicenseNotAvailableException::class);
});

it('recusa agendar evento a partir de um item do tipo curso', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, tipo: CatalogItemTipo::Curso);

    expect(fn () => app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload()))
        ->toThrow(LicenseNotAvailableException::class);
});

it('recusa abrir turma a partir de um item do tipo palestra', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    $payload = [
        'name' => 'Turma 1',
        'tipo_turma' => 'online',
        'data_inicio' => '2027-03-01',
        'intervalo_dias' => 7,
        'hora_inicio' => '19:00',
        'hora_fim' => '21:00',
    ];

    expect(fn () => app(LicenseActivationService::class)->openTurma($licenca, $tenant->id, $payload))
        ->toThrow(LicenseNotAvailableException::class);
});

it('agenda a palestra pela tela do admin', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id.'/eventos', palestraPayload())
        ->assertRedirect();

    $evento = Event::withoutGlobalScopes()->first();

    expect($evento)->not->toBeNull()
        ->and($evento->catalog_license_id)->toBe($licenca->id)
        ->and($evento->tenant_id)->toBe($tenant->id)
        ->and($evento->allow_online_registration)->toBeTrue()
        ->and($evento->com_certificado)->toBeTrue();
});

it('devolve 404 ao agendar palestra de licença de outra câmara', function () {
    $araras = palestraTenant('araras-sp');
    $leme = palestraTenant('leme-sp');
    $director = palestraDirector();
    $licencaLeme = palestraLicense($director, $leme);

    $admin = palestraAdmin($araras);
    $host = $araras->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/catalogo-regional/'.$licencaLeme->id.'/eventos', palestraPayload())
        ->assertNotFound();

    expect(Event::withoutGlobalScopes()->count())->toBe(0);
});

it('usa data e vagas da licença ao agendar se a câmara não informar', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'palestra_em' => '2027-09-10 20:00:00',
        'max_inscritos' => 90,
        'modalidade' => 'presencial',
    ]);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, []);

    expect($evento->date_time->format('Y-m-d H:i'))->toBe('2027-09-10 20:00')
        ->and($evento->max_seats)->toBe(90);
});

it('trava data, vagas e modalidade que o diretor já definiu', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'palestra_em' => '2027-09-10 20:00:00',
        'max_inscritos' => 90,
        'modalidade' => 'presencial',
    ]);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id)
        ->assertOk()
        ->assertSee('Definido pela direção regional')
        ->assertSee('Presencial')
        ->assertSee('date_time_display', false)
        ->assertSee('max_seats_display', false)
        ->assertSee('modalidade_display', false);

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id.'/eventos', palestraPayload([
            'date_time' => '2028-01-01 08:00',
            'max_seats' => 12,
        ]))
        ->assertRedirect();

    $evento = Event::withoutGlobalScopes()->first();

    expect($evento->date_time->format('Y-m-d H:i'))->toBe('2027-09-10 20:00')
        ->and($evento->max_seats)->toBe(90);
});

it('ignora data e vagas enviadas pela câmara quando a licença já tem esses dados', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'palestra_em' => '2027-09-10 20:00:00',
        'max_inscritos' => 90,
        'modalidade' => 'online',
    ]);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload([
        'date_time' => '2028-12-31 23:00',
        'max_seats' => 3,
    ]));

    expect($evento->date_time->format('Y-m-d H:i'))->toBe('2027-09-10 20:00')
        ->and($evento->max_seats)->toBe(90);
});

it('mostra a tela de agendar em vez de abrir turma para palestra', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/catalogo-regional/'.$licenca->id)
        ->assertOk()
        ->assertSee('Agendar esta palestra')
        ->assertDontSee('Abrir uma turma');
});

it('mostra palestra liberada em eventos e no sino da navbar', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    palestraLicense($director, $tenant);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/eventos')
        ->assertOk()
        ->assertSee('LGPD no Legislativo')
        ->assertSee('palestra liberada para agendar')
        ->assertSee('Agendar palestra')
        ->assertSee('Liberados pela direção')
        ->assertSee('Palestra liberada — agendar evento');
});

it('mostra curso liberado no sino da navbar', function () {
    $tenant = palestraTenant('araras-curso');
    $director = palestraDirector();
    palestraLicense($director, $tenant, CatalogItemTipo::Curso);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('LGPD no Legislativo')
        ->assertSee('Liberados pela direção')
        ->assertSee('Curso liberado — abrir turma');
});

it('tira a palestra do sino depois de agendar o evento', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: ['max_turmas' => 1]);

    app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload());

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/eventos')
        ->assertOk()
        ->assertDontSee('palestra liberada para agendar')
        ->assertDontSee('Palestra liberada — agendar evento')
        ->assertSee('LGPD no Legislativo — 1ª edição');
});

it('entrega o material da palestra do catálogo ao aluno e ao admin', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'exibir_ate' => now()->addMonth()->toDateString(),
    ]);

    CatalogLesson::create([
        'catalog_item_id' => $licenca->catalog_item_id,
        'ordem' => 1,
        'titulo' => 'Slides da palestra',
        'material_url' => 'https://exemplo.com/slides-lgpd.pdf',
    ]);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload());

    expect($evento->fresh()->catalogContent())->toHaveCount(1);

    $admin = palestraAdmin($tenant);
    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/escola/eventos/'.$evento->id.'/edit')
        ->assertOk()
        ->assertSee('Slides da palestra');
});

it('esconde o material da palestra depois que a licença vence', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'exibir_ate' => now()->addMonth()->toDateString(),
    ]);

    CatalogLesson::create([
        'catalog_item_id' => $licenca->catalog_item_id,
        'ordem' => 1,
        'titulo' => 'Slides da palestra',
        'material_url' => 'https://exemplo.com/slides-lgpd.pdf',
    ]);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload());

    $licenca->forceFill(['exibir_ate' => now()->subDay()->toDateString()])->save();

    expect($evento->fresh()->catalogContent())->toBeEmpty();
});

it('oculta no portal público o evento do catálogo após exibir_ate', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'exibir_ate' => now()->subDay()->toDateString(),
        'modalidade' => 'presencial',
    ]);

    // Prazo já venceu: openEvento bloqueia. Forçamos o vínculo como se a câmara
    // tivesse agendado antes do vencimento.
    $licenca->forceFill(['exibir_ate' => now()->addMonth()->toDateString()])->save();
    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload([
        'title' => 'Palestra expirada no portal',
    ]));
    $licenca->forceFill(['exibir_ate' => now()->subDay()->toDateString()])->save();

    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/eventos')
        ->assertOk()
        ->assertDontSee('Palestra expirada no portal');

    $this->get('http://'.$host.'/eventos/'.$evento->id)
        ->assertNotFound();
});

it('mantém no portal o evento do catálogo enquanto exibir_ate for futuro', function () {
    $tenant = palestraTenant('araras-sp');
    $director = palestraDirector();
    $licenca = palestraLicense($director, $tenant, overrides: [
        'exibir_ate' => now()->addMonth()->toDateString(),
        'modalidade' => 'presencial',
    ]);

    $evento = app(LicenseActivationService::class)->openEvento($licenca, $tenant->id, palestraPayload([
        'title' => 'Palestra ainda visível',
    ]));

    $host = $tenant->slug.'.'.config('app.domain');

    $this->get('http://'.$host.'/eventos')
        ->assertOk()
        ->assertSee('Palestra ainda visível');

    $this->get('http://'.$host.'/eventos/'.$evento->id)
        ->assertOk()
        ->assertSee('Palestra ainda visível');
});
