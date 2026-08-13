<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DebugAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@desenvolve.city')->first();
        if ($user) {
            $this->command->info('User encontrado:');
            $this->command->info('ID: ' . $user->id);
            $this->command->info('Name: ' . $user->name);
            $this->command->info('Email: ' . $user->email);
            $this->command->info('Tenant ID: ' . ($user->tenant_id ?? 'null'));
            $this->command->info('User Type: ' . $user->user_type);
            $this->command->info('Status: ' . $user->status);
            $this->command->info('Email Verified: ' . ($user->email_verified_at ? 'Sim' : 'Não'));
            $this->command->info('Roles: ' . $user->roles->pluck('name')->join(', '));
            $this->command->info('Has central_super_admin: ' . ($user->hasRole('central_super_admin') ? 'Sim' : 'Não'));
        } else {
            $this->command->error('User não encontrado');
        }
    }
}