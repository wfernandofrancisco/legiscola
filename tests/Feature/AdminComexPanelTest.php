<?php

use App\Models\CentralProcessRun;
use App\Models\ComexImportacaoLinha;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Support\Facades\Hash;

it('admin comex painel responde 200 com contexto de tenant', function () {
    $tenant = Tenant::create([
        'name' => 'T Comex Admin '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-comex-adm-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
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
        'type' => 'comex_import',
        'status' => CentralProcessRun::STATUS_COMPLETED,
        'requested_by' => null,
        'meta' => [],
    ]);
    ComexImportacaoLinha::withoutGlobalScopes()->create([
        'tenant_id' => $tenant->id,
        'central_process_run_id' => $run->id,
        'co_ano' => 2026,
        'co_mes' => 1,
        'sh4' => '1234',
        'co_pais' => '160',
        'sg_uf_mun' => 'SP',
        'co_mun' => '3550308',
        'kg_liquido' => 100,
        'vl_fob' => 5000,
    ]);

    TenantContext::set($tenant->id);
    $response = $this->actingAs($user)->get(route('admin.comex.index'));
    TenantContext::clear();

    $response->assertOk();

    ComexImportacaoLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $user->forceDelete();
    $tenant->forceDelete();
});
