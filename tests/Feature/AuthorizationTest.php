<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    foreach (['central_super_admin', 'tenant-admin', 'tenant-user'] as $name) {
        Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $this->tenant = Tenant::create([
        'name' => 'Cliente Teste',
        'slug' => 'cliente-teste',
        'razao_social' => 'Cliente Teste LTDA',
        'cnpj' => '12345678000199',
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);

    $this->superAdmin = User::create([
        'tenant_id' => null,
        'name' => 'Super',
        'email' => 'super@test.local',
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_SUPER_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $this->superAdmin->assignRole('central_super_admin');

    $this->tenantUser = User::create([
        'tenant_id' => $this->tenant->id,
        'name' => 'Tenant User',
        'email' => 'tenant@test.local',
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_USER,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $this->tenantUser->assignRole('tenant-user');
});

test('super-admin acessa rota central', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('central.dashboard'));

    $response->assertOk();
});

test('usuário de tenant não acessa central dashboard', function () {
    $response = $this->actingAs($this->tenantUser)->get(route('central.dashboard'));

    $response->assertRedirect('/');
});

test('super-admin recebe redirect ao acessar painel tenant', function () {
    $response = $this->actingAs($this->superAdmin)->get(route('app.dashboard'));

    $response->assertRedirect(route('central.dashboard'));
});

test('tenant-user mantém role após login simulado', function () {
    expect($this->tenantUser->hasRole('tenant-user'))->toBeTrue();
    expect($this->tenantUser->hasRole('super-admin'))->toBeFalse();
});
