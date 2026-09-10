<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Models\CatalogItem;
use App\Models\CatalogLicense;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Services\DirectorFinanceService;
use App\Support\DirectorContext;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

function financeTenant(string $slug, string $uf): Tenant
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
function financeDirector(array $ufs): User
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

function financeItem(User $director, string $titulo): CatalogItem
{
    return CatalogItem::create([
        'owner_user_id' => $director->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => $titulo,
        'status' => CatalogItemStatus::Publicado,
    ]);
}

function financeLicense(User $director, Tenant $tenant, array $attrs = []): CatalogLicense
{
    return CatalogLicense::create(array_merge([
        'catalog_item_id' => financeItem($director, 'Curso '.fake()->unique()->word())->id,
        'tenant_id' => $tenant->id,
        'director_user_id' => $director->id,
        'status' => 'ativa',
    ], $attrs));
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    DirectorContext::forget();
});

it('grava o bloco financeiro ao liberar uma licença', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $director = financeDirector(['SP']);
    $item = financeItem($director, 'Curso de orçamento');

    $this->actingAs($director)
        ->post(route('diretor.licencas.store'), [
            'catalog_item_id' => $item->id,
            'tenant_id' => $sp->id,
            'status' => 'ativa',
            'valor' => '2500.50',
            'pagamento_status' => 'pendente',
            'vencimento_em' => '2026-10-10',
            'forma_pagamento' => 'Empenho',
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    $licenca = CatalogLicense::first();

    expect((float) $licenca->valor)->toBe(2500.50)
        ->and($licenca->forma_pagamento)->toBe('Empenho')
        ->and($licenca->vencimento_em->format('Y-m-d'))->toBe('2026-10-10');
});

it('preenche a data do pagamento ao marcar como pago sem informar a data', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $director = financeDirector(['SP']);
    $licenca = financeLicense($director, $sp, ['valor' => 1000, 'pagamento_status' => 'pendente']);

    $this->actingAs($director)
        ->put(route('diretor.licencas.update', $licenca), [
            'status' => 'ativa',
            'valor' => '1000',
            'pagamento_status' => 'pago',
        ])
        ->assertRedirect(route('diretor.licencas.index'));

    expect($licenca->fresh()->pago_em->toDateString())->toBe(now()->toDateString());
});

it('soma contratado, recebido, em aberto e vencido', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $director = financeDirector(['SP']);
    $this->actingAs($director);

    financeLicense($director, $sp, ['valor' => 1000, 'pagamento_status' => 'pago', 'vencimento_em' => now()->toDateString()]);
    financeLicense($director, $sp, ['valor' => 500, 'pagamento_status' => 'pendente', 'vencimento_em' => now()->addMonth()->toDateString()]);
    financeLicense($director, $sp, ['valor' => 300, 'pagamento_status' => 'pendente', 'vencimento_em' => now()->subMonth()->toDateString()]);

    $service = app(DirectorFinanceService::class);
    $resumo = $service->summary($service->licenses($director, (int) now()->year));

    expect($resumo['contratado'])->toBe(1800.0)
        ->and($resumo['recebido'])->toBe(1000.0)
        ->and($resumo['em_aberto'])->toBe(800.0)
        ->and($resumo['vencido'])->toBe(300.0);
});

it('ignora licença sem valor no painel financeiro', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $director = financeDirector(['SP']);
    $this->actingAs($director);

    financeLicense($director, $sp, ['valor' => 900]);
    financeLicense($director, $sp);

    $service = app(DirectorFinanceService::class);

    expect($service->licenses($director, (int) now()->year))->toHaveCount(1);
});

it('não mistura recebíveis de outro diretor', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $outroSp = financeTenant('leme-sp', 'SP');

    $director = financeDirector(['SP']);
    $outro = financeDirector(['SP']);

    financeLicense($director, $sp, ['valor' => 1000]);
    financeLicense($outro, $outroSp, ['valor' => 7777]);

    $this->actingAs($director);
    $service = app(DirectorFinanceService::class);
    $resumo = $service->summary($service->licenses($director, (int) now()->year));

    expect($resumo['contratado'])->toBe(1000.0);
});

it('não mostra recebível de câmara fora da abrangência atual', function () {
    $mg = financeTenant('uberaba-mg', 'MG');
    $director = financeDirector(['SP', 'MG']);
    financeLicense($director, $mg, ['valor' => 4000]);

    // Diretor perde MG: a licença antiga sai das contas.
    DirectorUf::query()->where('user_id', $director->id)->where('uf', 'MG')->delete();
    DirectorContext::forget();

    $this->actingAs($director);
    $service = app(DirectorFinanceService::class);

    expect($service->licenses($director->fresh(), (int) now()->year))->toHaveCount(0);
});

it('agrupa recebíveis por UF, câmara e mês', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $mg = financeTenant('uberaba-mg', 'MG');
    $director = financeDirector(['SP', 'MG']);
    $this->actingAs($director);

    financeLicense($director, $sp, ['valor' => 1000, 'vencimento_em' => '2026-03-10']);
    financeLicense($director, $sp, ['valor' => 200, 'vencimento_em' => '2026-03-25']);
    financeLicense($director, $mg, ['valor' => 500, 'vencimento_em' => '2026-04-05']);

    $service = app(DirectorFinanceService::class);
    $licencas = $service->licenses($director, 2026);

    expect($service->perUf($licencas)->pluck('contratado', 'uf')->all())
        ->toBe(['MG' => 500.0, 'SP' => 1200.0])
        ->and($service->perTenant($licencas))->toHaveCount(2)
        ->and($service->perMonth($licencas)->pluck('contratado', 'rotulo')->all())
        ->toBe(['Março de 2026' => 1200.0, 'Abril de 2026' => 500.0]);
});

it('abre o painel financeiro para o diretor e nega para tenant_admin', function () {
    $sp = financeTenant('araras-sp', 'SP');
    $director = financeDirector(['SP']);
    financeLicense($director, $sp, ['valor' => 1234.56, 'pagamento_status' => 'pendente']);

    $this->actingAs($director)
        ->get(route('diretor.financeiro.index'))
        ->assertOk()
        ->assertSee('1.234,56');

    $admin = User::create([
        'tenant_id' => $sp->id,
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('diretor.financeiro.index'))
        ->assertRedirect();
});
