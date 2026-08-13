<?php

namespace Database\Seeders;

use App\Models\Tenant;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Tenant::firstOrCreate(
            ['slug' => 'default'],
            [
                'name' => 'Tenant Padrão',
                'domain' => 'develop-city.local',
                'description' => 'Tenant padrão do sistema',
                'status' => Tenant::STATUS_ATIVO,
            ]
        );
    }
}
