<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestTenantUserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'prefeituraleme')->first();
        if ($tenant) {
            $user = User::firstOrCreate(
                ['email' => 'teste@prefeituraleme.com'],
                [
                    'tenant_id' => $tenant->id,
                    'name' => 'Teste User',
                    'password' => Hash::make('123456'),
                    'user_type' => 'tenant_user',
                    'status' => 'ativo',
                    // 'email_verified_at' => now(), // Comente para testar verification
                ]
            );
            $this->command->info('User de teste criado: teste@prefeituraleme.com | Senha: 123456');
        } else {
            $this->command->error('Tenant prefeituraleme não encontrado');
        }
    }
}