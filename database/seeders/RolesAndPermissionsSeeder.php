<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -----------------------------------------------------------------------
        // Permissions
        // -----------------------------------------------------------------------

        $permissions = [
            // Usuários
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            // Empresas
            'companies.view',
            'companies.create',
            'companies.edit',
            'companies.delete',

            // Orçamentos
            'budgets.view',
            'budgets.create',
            'budgets.edit',
            'budgets.delete',
            'budgets.approve',

            // Relatórios
            'reports.view',
            'reports.export',

            // Configurações
            'settings.view',
            'settings.edit',

            // Logs
            'logs.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // -----------------------------------------------------------------------
        // Roles e suas permissões
        // -----------------------------------------------------------------------

        // Super Admin — acesso total ao sistema (dono do SaaS e equipe)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());
        $superAdmin->update(['type' => 'central']);

        // Central Super Admin — super admin específico do central
        $centralSuperAdmin = Role::firstOrCreate(['name' => 'central_super_admin', 'guard_name' => 'web']);
        $centralSuperAdmin->givePermissionTo(Permission::all());
        $centralSuperAdmin->update(['type' => 'central']);

        // Tenant Admin — admin completo da empresa cliente
        $tenantAdmin = Role::firstOrCreate(['name' => 'tenant_admin', 'guard_name' => 'web']);
        $tenantAdmin->givePermissionTo([
            'users.view',
            'users.create',
            'users.edit',
            'users.delete',
            'companies.view',
            'companies.edit',
            'budgets.view',
            'budgets.create',
            'budgets.edit',
            'budgets.delete',
            'budgets.approve',
            'reports.view',
            'reports.export',
            'settings.view',
            'settings.edit',
        ]);
        $tenantAdmin->update(['type' => 'tenant']);

        // Tenant Manager — gerente/líder dentro da empresa cliente
        $tenantManager = Role::firstOrCreate(['name' => 'tenant_manager', 'guard_name' => 'web']);
        $tenantManager->givePermissionTo([
            'users.view',
            'companies.view',
            'budgets.view',
            'budgets.create',
            'budgets.edit',
            'budgets.approve',
            'reports.view',
            'reports.export',
        ]);
        $tenantManager->update(['type' => 'tenant']);

        // Tenant User — usuário comum da empresa cliente
        $tenantUser = Role::firstOrCreate(['name' => 'tenant_user', 'guard_name' => 'web']);
        $tenantUser->givePermissionTo([
            'budgets.view',
            'budgets.create',
        ]);
        $tenantUser->update(['type' => 'tenant']);

        $tenantProfessor = Role::firstOrCreate(['name' => 'tenant_professor', 'guard_name' => 'web']);
        $tenantProfessor->givePermissionTo([]);
        $tenantProfessor->update(['type' => 'tenant']);

        $this->command->info('Roles e permissões criadas com sucesso!');
    }
}
