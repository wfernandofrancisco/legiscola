<?php

use App\Models\Cnae;
use App\Models\Empresa;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;

it('mapa de empresas exige cnae e lista marcadores quando aplicar', function () {
    $tenant = Tenant::create([
        'name' => 'T Mapa '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-mapa-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'latitude' => -22.18535,
        'longitude' => -47.38805,
    ]);

    $codigoCnae = (string) fake()->unique()->numberBetween(1000000, 9999999);

    Cnae::query()->forceCreate([
        'codigo' => $codigoCnae,
        'descricao' => 'CNAE teste mapa',
        'situacao' => true,
        'tenant_id' => null,
    ]);

    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Mapa',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $cnpjBasico = str_pad((string) fake()->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);

    Empresa::withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'cnpj' => $cnpjBasico.'000100',
        'cnpj_basico' => $cnpjBasico,
        'cnpj_ordem' => '0001',
        'cnpj_dv' => '00',
        'razao_social' => 'Empresa Mapa LTDA',
        'cnae_fiscal_principal' => $codigoCnae,
        'bairro' => 'Centro',
        'situacao_cadastral' => '02',
        'data_situacao_cadastral' => '2024-06-01',
        'latitude' => -22.19,
        'longitude' => -47.39,
    ]);

    TenantContext::set($tenant->id);

    $this->actingAs($user)->get(route('admin.empresas.mapa.index', ['aplicar' => 1]))
        ->assertSessionHasErrors('cnaes');

    $resp = $this->actingAs($user)->get(route('admin.empresas.mapa.index', [
        'aplicar' => 1,
        'cnaes' => [$codigoCnae],
    ]));
    $resp->assertOk();
    expect($resp->content())->toContain('mapa-empresas');
    expect($resp->content())->toContain('Filtradas');

    TenantContext::clear();

    Empresa::withoutGlobalScopes()->where('tenant_id', $tenant->id)->forceDelete();
    Cnae::query()->where('codigo', $codigoCnae)->delete();
    $user->forceDelete();
    $tenant->forceDelete();
});
