<?php

use App\Enums\CnaeSinonimoStatus;
use App\Models\Cnae;
use App\Models\CnaeSinonimo;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->withoutMiddleware([
        \App\Http\Middleware\EnsureCentralAccess::class,
        \App\Http\Middleware\EnsureTenantAccess::class,
        \App\Http\Middleware\EnsureHasTenant::class,
        \App\Http\Middleware\SetTenantContext::class,
        \App\Http\Middleware\ApplyTenantContextFromAuth::class,
        \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    ]);
});

function makeTenant(array $overrides = []): Tenant
{
    return Tenant::create(array_merge([
        'name' => 'Tenant Test',
        'slug' => 'tenant-test-' . fake()->unique()->numberBetween(1, 9999),
        'domain' => fake()->unique()->domainName(),
        'status' => 'ativo',
    ], $overrides));
}

function makeTenantAdmin(?Tenant $tenant = null, array $overrides = []): User
{
    $tenant ??= makeTenant();

    return User::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Tenant Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeSuperAdmin(array $overrides = []): User
{
    return User::create(array_merge([
        'tenant_id' => null,
        'name' => 'Super Admin',
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_SUPER_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ], $overrides));
}

function makeCnae(array $overrides = []): Cnae
{
    return Cnae::create(array_merge([
        'descricao' => 'Comercio varejista',
        'situacao' => true,
        'tenant_id' => null,
    ], $overrides));
}

it('tenant_admin pode sugerir sinonimo e fica aguardando aprovacao', function () {
    $tenant = makeTenant();
    $tenantAdmin = makeTenantAdmin($tenant);
    $cnae = makeCnae();

    $response = $this->actingAs($tenantAdmin)->post(route('admin.cnae-sinonimos.store'), [
        'cnae_id' => $cnae->id,
        'sinonimo' => 'mercadinho',
    ]);

    $response->assertRedirect(route('admin.cnae-sinonimos.index'));

    $this->assertDatabaseHas('cnae_sinonimos', [
        'cnae_id' => $cnae->id,
        'sinonimo' => 'mercadinho',
        'status' => CnaeSinonimoStatus::AGUARDANDO->value,
        'created_by' => $tenantAdmin->id,
        'tenant_id' => $tenant->id,
    ]);
});

it('tenant_admin visualiza apenas as proprias sugestoes no index', function () {
    $tenant = makeTenant();
    $tenantAdmin = makeTenantAdmin($tenant, ['email' => 'owner@test.com']);
    $otherAdmin = makeTenantAdmin($tenant, ['email' => 'other@test.com']);
    $cnae = makeCnae();

    CnaeSinonimo::create([
        'cnae_id' => $cnae->id,
        'sinonimo' => 'sugestao-propria',
        'status' => CnaeSinonimoStatus::AGUARDANDO->value,
        'created_by' => $tenantAdmin->id,
        'tenant_id' => $tenant->id,
    ]);

    CnaeSinonimo::create([
        'cnae_id' => $cnae->id,
        'sinonimo' => 'sugestao-de-outro',
        'status' => CnaeSinonimoStatus::AGUARDANDO->value,
        'created_by' => $otherAdmin->id,
        'tenant_id' => $tenant->id,
    ]);

    $response = $this->actingAs($tenantAdmin)->get(route('admin.cnae-sinonimos.index'));

    $response->assertOk();
    $response->assertSee('sugestao-propria');
    $response->assertDontSee('sugestao-de-outro');
});

it('tenant_admin nao pode ver detalhe de sugestao de outro usuario', function () {
    $tenant = makeTenant();
    $tenantAdmin = makeTenantAdmin($tenant, ['email' => 'owner2@test.com']);
    $otherAdmin = makeTenantAdmin($tenant, ['email' => 'other2@test.com']);
    $cnae = makeCnae();

    $sinonimo = CnaeSinonimo::create([
        'cnae_id' => $cnae->id,
        'sinonimo' => 'somente-outro',
        'status' => CnaeSinonimoStatus::AGUARDANDO->value,
        'created_by' => $otherAdmin->id,
        'tenant_id' => $tenant->id,
    ]);

    $this->actingAs($tenantAdmin)
        ->get(route('admin.cnae-sinonimos.show', $sinonimo))
        ->assertForbidden();
});

it('super_admin executa crud de sinonimos no central', function () {
    $superAdmin = makeSuperAdmin();
    $cnae = makeCnae();

    $createResponse = $this->actingAs($superAdmin)->post(route('central.cnae-sinonimos.store'), [
        'cnae_id' => $cnae->id,
        'sinonimo_name' => 'novo-sinonimo-central',
    ]);

    $createResponse->assertRedirect(route('central.cnae-sinonimos.index'));

    $sinonimo = CnaeSinonimo::where('sinonimo', 'novo-sinonimo-central')->firstOrFail();

    expect($sinonimo->status)->toBe(CnaeSinonimoStatus::APROVADO);
    expect($sinonimo->created_by)->toBe($superAdmin->id);

    $this->actingAs($superAdmin)
        ->put(route('central.cnae-sinonimos.update', $sinonimo), [
            'cnae_id' => $cnae->id,
            'sinonimo_name' => 'sinonimo-atualizado-central',
        ])
        ->assertRedirect(route('central.cnae-sinonimos.index'));

    $this->assertDatabaseHas('cnae_sinonimos', [
        'id' => $sinonimo->id,
        'sinonimo' => 'sinonimo-atualizado-central',
        'status' => CnaeSinonimoStatus::APROVADO->value,
    ]);

    $this->actingAs($superAdmin)
        ->delete(route('central.cnae-sinonimos.destroy', $sinonimo))
        ->assertRedirect(route('central.cnae-sinonimos.index'));

    $this->assertSoftDeleted('cnae_sinonimos', ['id' => $sinonimo->id]);
});

it('tenant_admin sugere e super_admin aprova no central', function () {
    $tenant = makeTenant();
    $tenantAdmin = makeTenantAdmin($tenant);
    $superAdmin = makeSuperAdmin();
    $cnae = makeCnae();

    $this->actingAs($tenantAdmin)->post(route('admin.cnae-sinonimos.store'), [
        'cnae_id' => $cnae->id,
        'sinonimo' => 'sugestao-para-aprovacao',
    ])->assertRedirect(route('admin.cnae-sinonimos.index'));

    $sinonimo = CnaeSinonimo::where('sinonimo', 'sugestao-para-aprovacao')->firstOrFail();

    expect($sinonimo->status)->toBe(CnaeSinonimoStatus::AGUARDANDO);
    expect($sinonimo->tenant_id)->toBe($tenant->id);

    $this->actingAs($superAdmin)
        ->post(route('central.cnae-sinonimos.approve', $sinonimo))
        ->assertRedirect(route('central.cnae-sinonimos.index'));

    $sinonimo->refresh();

    expect($sinonimo->status)->toBe(CnaeSinonimoStatus::APROVADO);
    expect($sinonimo->updated_by)->toBe($superAdmin->id);
    expect($sinonimo->tenant_id)->toBeNull();
});
