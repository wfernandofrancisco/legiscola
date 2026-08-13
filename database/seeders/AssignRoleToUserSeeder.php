<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AssignRoleToUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::find(1);
        if ($user) {
            $user->assignRole('central_super_admin');
            $this->command->info('Role central_super_admin atribuído ao user ID 1');
        } else {
            $this->command->error('User ID 1 não encontrado');
        }
    }
}