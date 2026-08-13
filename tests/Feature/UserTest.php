<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

describe('User Management', function () {

    beforeEach(function () {
        // Setup roles e permissions
        setupTestRoles();

        $this->tenant = Tenant::create([
            'name' => 'Test Tenant',
            'slug' => 'test-tenant',
            'domain' => 'test.local',
            'status' => 'ativo'
        ]);
    });

    test('pode criar um novo usuário', function () {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]);

        expect($user->id)->toBeGreaterThan(0);
        expect($user->email)->toBe('joao@example.com');
        expect(Hash::check('password123', $user->password))->toBeTrue();
    });

    test('pode atribuir role a um usuário', function () {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_ADMIN,
            'status' => User::STATUS_ATIVO,
        ]);

        $user->assignRole('tenant-admin');

        expect($user->hasRole('tenant-admin'))->toBeTrue();
    });

    test('pode verificar permissões', function () {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Pedro Costa',
            'email' => 'pedro@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]);

        $user->assignRole('tenant-user');

        expect($user->hasPermissionTo('view-companies'))->toBeTrue();
    });

    test('usuário super admin tem todas as permissões', function () {
        $admin = User::create([
            'name' => 'Administrador',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'user_type' => User::TYPE_SUPER_ADMIN,
            'status' => User::STATUS_ATIVO,
        ]);

        $admin->assignRole('super-admin');

        expect($admin->hasRole('super-admin'))->toBeTrue();
        expect($admin->hasPermissionTo('view-users'))->toBeTrue();
    });

    test('email é obrigatório e único', function () {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Usuario 1',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]);

        expect(fn() => User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Usuario 2',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]))->toThrow(\Illuminate\Database\QueryException::class);
    });

    test('usuário pertence a tenant', function () {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Ana Silva',
            'email' => 'ana@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]);

        expect($user->tenant_id)->toBe($this->tenant->id);
        expect($user->tenant()->first()->slug)->toBe('test-tenant');
    });

    test('usuário pode ser ativado e desativado', function () {
        $user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Carlos Silva',
            'email' => 'carlos@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::TYPE_TENANT_USER,
            'status' => User::STATUS_ATIVO,
        ]);

        $user->update(['status' => User::STATUS_INATIVO]);
        expect($user->status)->toBe('inativo');

        $user->update(['status' => User::STATUS_ATIVO]);
        expect($user->status)->toBe('ativo');
    });
});

function setupTestRoles()
{
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

    $permissions = [
        'view-users',
        'create-users',
        'edit-users',
        'delete-users',
        'view-companies',
        'create-companies',
        'edit-companies',
        'delete-companies',
        'view-budgets',
        'create-budgets',
        'edit-budgets',
        'delete-budgets',
        'view-reports',
        'export-reports',
        'view-logs',
        'view-settings',
        'edit-settings',
        'view-activity',
    ];

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
    }

    $perm = Permission::all();

    Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web'])->syncPermissions($perm);
    Role::firstOrCreate(['name' => 'tenant-admin', 'guard_name' => 'web'])->syncPermissions($perm);
    Role::firstOrCreate(['name' => 'tenant-manager', 'guard_name' => 'web'])->syncPermissions('view-companies');
    Role::firstOrCreate(['name' => 'tenant-user', 'guard_name' => 'web'])->syncPermissions('view-companies');
}
