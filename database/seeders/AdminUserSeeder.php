<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        // Get or create default tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Tenant Padrão',
                'domain' => 'legiscola.local',
                'description' => 'Tenant padrão do sistema',
                'status' => Tenant::STATUS_ATIVO,
            ]
        );

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@legiscola.local'],
            [
                'tenant_id' => null, // Super admin não pertence a tenant
                'name'      => 'Administrador',
                'password'  => Hash::make('admin123'),
                'user_type' => User::TYPE_SUPER_ADMIN,
                'status'    => User::STATUS_ATIVO,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('central_super_admin');

        $this->command->info("Admin criado: {$admin->email} | Senha: Admin@123");
        $this->command->warn('IMPORTANTE: Altere a senha do admin após o primeiro acesso!');
    }
}
