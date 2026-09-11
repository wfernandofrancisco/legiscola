<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Enums\CatalogLicenseStatus;
use App\Mail\CatalogLicenseReleasedMail;
use App\Models\CatalogItem;
use App\Models\CatalogLicense;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function licenseTenant(string $slug, string $uf): Tenant
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

/**
 * @param  list<string>  $ufs
 */
function licenseDirector(array $ufs): User
{
    $director = User::create([
        'tenant_id' => null,
        'name' => 'Diretor '.implode('', $ufs),
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

function licenseItem(User $director, CatalogItemStatus $status = CatalogItemStatus::Publicado): CatalogItem
{
    return CatalogItem::create([
        'owner_user_id' => $director->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Curso '.fake()->unique()->word(),
        'status' => $status,
    ]);
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

it('libera um item publicado para câmara da região', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
            'max_turmas' => 3,
            'exibir_ate' => now()->addYear()->toDateString(),
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    $licenca = CatalogLicense::first();

    expect($licenca->tenant_id)->toBe($sp->id)
        ->and($licenca->director_user_id)->toBe($director->id)
        ->and($licenca->status)->toBe(CatalogLicenseStatus::Ativa)
        ->and($licenca->max_turmas)->toBe(3)
        ->and($licenca->liberado_em)->not->toBeNull()
        ->and($licenca->isUsable())->toBeTrue();
});

it('recusa liberar para câmara fora da abrangência', function () {
    licenseTenant('araras-sp', 'SP');
    $mg = licenseTenant('uberaba-mg', 'MG');

    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $mg->id,
            'status' => 'ativa',
        ])
        ->assertSessionHasErrors('tenant_id');

    expect(CatalogLicense::count())->toBe(0);
});

it('recusa liberar item que não está publicado', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $rascunho = licenseItem($director, CatalogItemStatus::Rascunho);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $rascunho->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
        ])
        ->assertSessionHasErrors('catalog_item_id');

    expect(CatalogLicense::count())->toBe(0);
});

it('recusa liberar item de outro diretor', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    licenseTenant('uberaba-mg', 'MG');

    $diretorSp = licenseDirector(['SP']);
    $diretorMg = licenseDirector(['MG']);
    $itemDoMg = licenseItem($diretorMg);

    $this->actingAs($diretorSp)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $itemDoMg->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
        ])
        ->assertSessionHasErrors('catalog_item_id');

    expect(CatalogLicense::count())->toBe(0);
});

it('impede duas licenças do mesmo item para a mesma câmara', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $payload = [
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'status' => 'ativa',
    ];

    $this->actingAs($director)->post(route('diretor.licencas.store'), $payload);
    $this->actingAs($director)->post(route('diretor.licencas.store'), $payload)
        ->assertSessionHasErrors('tenant_id');

    expect(CatalogLicense::count())->toBe(1);
});

it('lista apenas as licenças do próprio diretor', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $mg = licenseTenant('uberaba-mg', 'MG');

    $diretorSp = licenseDirector(['SP']);
    $diretorMg = licenseDirector(['MG']);

    $itemSp = licenseItem($diretorSp);
    $itemSp->update(['titulo' => 'Licenca do SP']);
    $itemMg = licenseItem($diretorMg);
    $itemMg->update(['titulo' => 'Licenca do MG']);

    CatalogLicense::create([
        'catalog_item_id' => $itemSp->id, 'tenant_id' => $sp->id,
        'director_user_id' => $diretorSp->id, 'status' => 'ativa',
    ]);
    CatalogLicense::create([
        'catalog_item_id' => $itemMg->id, 'tenant_id' => $mg->id,
        'director_user_id' => $diretorMg->id, 'status' => 'ativa',
    ]);

    $this->actingAs($diretorSp)
        ->get(route('diretor.licencas.index'))
        ->assertOk()
        ->assertSee('Licenca do SP')
        ->assertDontSee('Licenca do MG');
});

it('impede editar licença de outro diretor', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    licenseTenant('uberaba-mg', 'MG');

    $diretorSp = licenseDirector(['SP']);
    $diretorOutro = licenseDirector(['SP']);

    $item = licenseItem($diretorOutro);
    $licenca = CatalogLicense::create([
        'catalog_item_id' => $item->id, 'tenant_id' => $sp->id,
        'director_user_id' => $diretorOutro->id, 'status' => 'ativa',
    ]);

    $this->actingAs($diretorSp)
        ->get(route('diretor.licencas.edit', $licenca))
        ->assertForbidden();
});

it('trata licença com prazo vencido como inutilizável', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $licenca = CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
        'exibir_ate' => now()->subDay()->toDateString(),
    ]);

    expect($licenca->isExpired())->toBeTrue()
        ->and($licenca->isUsable())->toBeFalse()
        ->and(CatalogLicense::query()->usable()->count())->toBe(0);
});

it('conta como utilizável a licença ativa sem prazo', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
    ]);

    expect(CatalogLicense::query()->usable()->forTenant($sp->id)->count())->toBe(1);
});

it('não conta licença suspensa como utilizável', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'suspensa',
    ]);

    expect(CatalogLicense::query()->usable()->count())->toBe(0);
});

it('grava agenda e limite de inscritos ao liberar palestra', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);
    $item->update(['tipo' => 'palestra', 'titulo' => 'Palestra LGPD']);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
            'modalidade' => 'presencial',
            'palestra_em' => '2027-06-15T19:00',
            'max_inscritos' => 180,
            'valor' => '3500',
            'pagamento_status' => 'pendente',
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    $licenca = CatalogLicense::first();

    expect($licenca->max_inscritos)->toBe(180)
        ->and($licenca->max_turmas)->toBe(1)
        ->and($licenca->modalidade->value)->toBe('presencial')
        ->and($licenca->palestra_em->format('Y-m-d H:i'))->toBe('2027-06-15 19:00');
});

it('exige data quando a palestra é presencial', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);
    $item->update(['tipo' => 'palestra']);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
            'modalidade' => 'presencial',
        ])
        ->assertSessionHasErrors('palestra_em');
});

it('grava o professor na licença e pré-preenche no evento da palestra', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);
    $item->update(['tipo' => 'palestra']);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
            'modalidade' => 'online',
            'palestra_em' => '2027-07-01T10:00',
            'professor_nome' => 'Dra. Marina Silva',
        ])
        ->assertRedirect();

    $licenca = CatalogLicense::first();

    expect($licenca->professor_nome)->toBe('Dra. Marina Silva');

    $evento = app(\App\Services\LicenseActivationService::class)
        ->openEvento($licenca, $sp->id, ['date_time' => '2027-07-01 10:00']);

    expect($evento->palestrante_nome)->toBe('Dra. Marina Silva');
});

it('mostra palestra planejada na agenda do diretor', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);
    $item->update(['tipo' => 'palestra', 'titulo' => 'Orçamento público']);

    CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
        'modalidade' => 'presencial',
        'palestra_em' => '2027-08-20 19:00:00',
        'max_inscritos' => 100,
    ]);

    $this->actingAs($director)
        ->get(route('diretor.agenda.index'))
        ->assertOk()
        ->assertSee('Agenda');

    $this->actingAs($director)
        ->get(route('diretor.agenda.events', [
            'start' => '2027-08-01',
            'end' => '2027-08-31',
        ]))
        ->assertOk()
        ->assertJsonFragment(['title' => 'Orçamento público']);
});

it('guarda o arquivo da nota fiscal em disco privado e deixa o diretor baixar', function () {
    Storage::fake('local');

    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $licenca = CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
    ]);

    $this->actingAs($director)
        ->put(route('diretor.licencas.update', $licenca), [
            'status' => 'ativa',
            'pagamento_status' => 'pago',
            'nota_fiscal_numero' => '2026/114',
            'nota_fiscal_arquivo' => UploadedFile::fake()->create('nf-2026-114.pdf', 120, 'application/pdf'),
        ])
        ->assertRedirect();

    $path = $licenca->fresh()->nota_fiscal_arquivo_path;

    expect($path)->not->toBeNull();
    Storage::disk('local')->assertExists($path);

    $this->actingAs($director)
        ->get(route('diretor.licencas.nota-fiscal', $licenca))
        ->assertOk()
        ->assertDownload();
});

it('impede outro diretor de baixar a nota fiscal da licença', function () {
    Storage::fake('local');

    $sp = licenseTenant('araras-sp', 'SP');
    $dono = licenseDirector(['SP']);
    $outro = licenseDirector(['SP']);
    $item = licenseItem($dono);

    $licenca = CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $dono->id,
        'status' => 'ativa',
        'nota_fiscal_arquivo_path' => 'licencas/notas-fiscais/1/nf.pdf',
    ]);

    $this->actingAs($outro)
        ->get(route('diretor.licencas.nota-fiscal', $licenca))
        ->assertForbidden();
});

it('avisa os administradores da câmara ao liberar uma licença ativa', function () {
    Mail::fake();

    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    $admin = User::create([
        'tenant_id' => $sp->id,
        'name' => 'Admin Araras',
        'email' => 'admin-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    // O mailable é ShouldQueue, então entra na fila em vez de sair na hora.
    Mail::assertQueued(
        CatalogLicenseReleasedMail::class,
        fn (CatalogLicenseReleasedMail $mail) => $mail->hasTo($admin->email)
    );
});

it('não avisa a câmara quando a licença nasce suspensa', function () {
    Mail::fake();

    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);

    User::create([
        'tenant_id' => $sp->id,
        'name' => 'Admin Araras',
        'email' => 'admin-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'suspensa',
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    Mail::assertNothingQueued();
});

it('exporta a agenda em ICS', function () {
    $sp = licenseTenant('araras-sp', 'SP');
    $director = licenseDirector(['SP']);
    $item = licenseItem($director);
    $item->update(['tipo' => 'palestra', 'titulo' => 'Ética no Legislativo']);

    CatalogLicense::create([
        'catalog_item_id' => $item->id,
        'tenant_id' => $sp->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
        'modalidade' => 'presencial',
        'palestra_em' => now()->addMonth()->setTime(19, 0)->toDateTimeString(),
        'max_inscritos' => 80,
    ]);

    $this->actingAs($director)
        ->get(route('diretor.agenda.export'))
        ->assertOk()
        ->assertHeader('content-type', 'text/calendar; charset=utf-8')
        ->assertSee('BEGIN:VCALENDAR')
        ->assertSee('Ética no Legislativo');
});

it('alerta conflito quando há dois presenciais no mesmo dia', function () {
    $araras = licenseTenant('araras-sp', 'SP');
    $leme = licenseTenant('leme-sp', 'SP');
    $director = licenseDirector(['SP']);

    $itemA = licenseItem($director);
    $itemA->update(['tipo' => 'palestra', 'titulo' => 'Palestra Araras']);
    $itemB = licenseItem($director);
    $itemB->update(['tipo' => 'palestra', 'titulo' => 'Palestra Leme']);

    $dia = now()->addMonths(2)->startOfDay()->setTime(10, 0);

    CatalogLicense::create([
        'catalog_item_id' => $itemA->id,
        'tenant_id' => $araras->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
        'modalidade' => 'presencial',
        'palestra_em' => $dia->toDateTimeString(),
    ]);

    CatalogLicense::create([
        'catalog_item_id' => $itemB->id,
        'tenant_id' => $leme->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
        'modalidade' => 'presencial',
        'palestra_em' => $dia->copy()->setTime(19, 0)->toDateTimeString(),
    ]);

    $this->actingAs($director)
        ->get(route('diretor.agenda.index'))
        ->assertOk()
        ->assertSee('Conflitos presenciais no mesmo dia')
        ->assertSee('Palestra Araras')
        ->assertSee('Palestra Leme');

    $this->actingAs($director)
        ->get(route('diretor.agenda.events', [
            'start' => $dia->copy()->startOfMonth()->toDateString(),
            'end' => $dia->copy()->endOfMonth()->toDateString(),
        ]))
        ->assertOk()
        ->assertJsonFragment(['conflito' => true]);
});
