<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class FixAdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'admin@desenvolve.city')->first();
        if ($user) {
            $user->update(['tenant_id' => null]);
            $user->syncRoles(['central_super_admin']);
            $this->command->info('User admin atualizado com role central_super_admin e tenant_id null');
        } else {
            $this->command->error('User admin não encontrado');
        }
    }
}