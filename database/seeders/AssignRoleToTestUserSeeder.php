<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignRoleToTestUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'teste@prefeituraleme.com')->first();
        if ($user) {
            $user->assignRole('tenant_user');
            $this->command->info('Role tenant_user atribuída ao user teste');
        } else {
            $this->command->error('User teste não encontrado');
        }
    }
}