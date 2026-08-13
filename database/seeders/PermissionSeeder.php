<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Desabilitar cache enquanto cria permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ─────────────────────────────────────────────────
            // Central (Super Admin) - CRUD de Clientes (Tenants)
            // ─────────────────────────────────────────────────
            ['name' => 'central.central.clientes.index', 'description' => 'Visualizar lista de clientes (tenants)'],
            ['name' => 'central.central.clientes.create', 'description' => 'Criar novo cliente (tenant)'],
            ['name' => 'central.central.clientes.show', 'description' => 'Visualizar detalhes de cliente (tenant)'],
            ['name' => 'central.central.clientes.edit', 'description' => 'Editar informações de cliente (tenant)'],
            ['name' => 'central.central.clientes.delete', 'description' => 'Deletar cliente (tenant)'],

            // ─────────────────────────────────────────────────
            // Central (Super Admin) - CRUD de Roles
            // ─────────────────────────────────────────────────
            ['name' => 'central.central.roles.index', 'description' => 'Visualizar lista de roles'],
            ['name' => 'central.central.roles.create', 'description' => 'Criar novo role'],
            ['name' => 'central.central.roles.show', 'description' => 'Visualizar detalhes de role'],
            ['name' => 'central.central.roles.edit', 'description' => 'Editar role'],
            ['name' => 'central.central.roles.delete', 'description' => 'Deletar role'],

            // ─────────────────────────────────────────────────
            // Central (Super Admin) - CRUD de Permissions
            // ─────────────────────────────────────────────────
            ['name' => 'central.central.permissions.index', 'description' => 'Visualizar lista de permissions'],
            ['name' => 'central.central.permissions.create', 'description' => 'Criar nova permission'],
            ['name' => 'central.central.permissions.show', 'description' => 'Visualizar detalhes de permission'],
            ['name' => 'central.central.permissions.edit', 'description' => 'Editar permission'],
            ['name' => 'central.central.permissions.delete', 'description' => 'Deletar permission'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['description' => $permission['description']]
            );
        }

        // Reabilitar cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
