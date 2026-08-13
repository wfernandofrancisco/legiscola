<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================================================================
        // PERMISSIONS - Central (Super Admin)
        // =====================================================================
        $centralPermissions = [
            'central.central.clientes.index' => 'Visualizar lista de clientes (tenants)',
            'central.central.clientes.create' => 'Criar novo cliente (tenant)',
            'central.central.clientes.show' => 'Visualizar detalhes de cliente (tenant)',
            'central.central.clientes.edit' => 'Editar informações de cliente (tenant)',
            'central.central.clientes.delete' => 'Deletar cliente (tenant)',

            'central.central.roles.index' => 'Visualizar lista de roles',
            'central.central.roles.create' => 'Criar novo role',
            'central.central.roles.show' => 'Visualizar detalhes de role',
            'central.central.roles.edit' => 'Editar role',
            'central.central.roles.delete' => 'Deletar role',

            'central.central.permissions.index' => 'Visualizar lista de permissions',
            'central.central.permissions.create' => 'Criar nova permission',
            'central.central.permissions.show' => 'Visualizar detalhes de permission',
            'central.central.permissions.edit' => 'Editar permission',
            'central.central.permissions.delete' => 'Deletar permission',
        ];

        // Create all permissions
        foreach ($centralPermissions as $name => $description) {
            Permission::firstOrCreate(['name' => $name], ['description' => $description]);
        }

        // =====================================================================
        // ROLES
        // =====================================================================

        // 1. Central Super Admin - Acesso total ao Central
        $centralSuperAdminRole = Role::firstOrCreate(['name' => 'central_super_admin'], ['description' => 'Super Administrador do Central']);
        $centralSuperAdminRole->syncPermissions(array_keys($centralPermissions));

        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
