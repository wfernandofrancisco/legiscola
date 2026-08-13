<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $this->centralRole = Role::firstOrCreate([
        'name' => 'central_super_admin',
        'guard_name' => 'web',
    ]);

    foreach ([
        'central.central.roles.index',
        'central.central.permissions.index',
    ] as $permissionName) {
        Permission::firstOrCreate([
            'name' => $permissionName,
            'guard_name' => 'web',
        ]);
    }

    $this->restrictedUser = User::create([
        'tenant_id' => null,
        'name' => 'Restricted Central',
        'email' => 'restricted-central@test.local',
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_SUPER_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $this->restrictedUser->assignRole($this->centralRole);
});

test('central sem permissao nao acessa roles index', function () {
    $response = $this->actingAs($this->restrictedUser)->get(route('central.roles.index'));

    $response->assertForbidden();
});

test('central sem permissao nao acessa permissions index', function () {
    $response = $this->actingAs($this->restrictedUser)->get(route('central.permissions.index'));

    $response->assertForbidden();
});

test('central com permissao acessa roles e permissions', function () {
    $this->restrictedUser->givePermissionTo([
        'central.central.roles.index',
        'central.central.permissions.index',
    ]);

    $rolesResponse = $this->actingAs($this->restrictedUser)->get(route('central.roles.index'));
    $permissionsResponse = $this->actingAs($this->restrictedUser)->get(route('central.permissions.index'));

    $rolesResponse->assertOk();
    $permissionsResponse->assertOk();
});
