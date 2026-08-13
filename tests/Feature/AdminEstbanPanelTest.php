<?php

use App\Models\CentralProcessRun;
use App\Models\EstbanLinha;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;

it('admin estban painel responde 200 com contexto de tenant', function () {
    $tenant = Tenant::create([
        'name' => 'T Estban Admin '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-estban-adm-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => '1100015',
    ]);
    $user = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_COMPLETED,
        'requested_by' => null,
        'meta' => [],
    ]);
    EstbanLinha::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'central_process_run_id' => $run->id,
        'data_base' => 202401,
        'co_municipio' => '1100015',
        'uf' => 'RO',
        'co_estado' => null,
        'cnpj' => '00000000',
        'nome_instituicao' => 'Banco Teste',
        'agencia' => '1',
        'verbetes' => ['162_POUPANCA' => 10, '165_CREDITO' => 20],
    ]);

    TenantContext::set($tenant->id);
    $response = $this->actingAs($user)->get(route('admin.estban.index'));
    $response->assertOk();
    expect($response->content())->toContain('chart-estban-linhas');

    foreach (['instituicoes', 'detalhe', 'ajuda'] as $tab) {
        $this->actingAs($user)->get(route('admin.estban.index', ['view' => $tab]))->assertOk();
    }
    TenantContext::clear();

    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $user->forceDelete();
    $tenant->forceDelete();
});
