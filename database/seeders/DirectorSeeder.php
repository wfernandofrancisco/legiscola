<?php

namespace Database\Seeders;

use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Diretor regional de exemplo para desenvolvimento.
 *
 * As UFs vêm dos tenants existentes, então o diretor já nasce enxergando clientes reais.
 */
class DirectorSeeder extends Seeder
{
    public function run(): void
    {
        if (! Role::query()->where('name', User::TYPE_TENANT_DIRECTOR)->where('guard_name', 'web')->exists()) {
            $this->call(RolesAndPermissionsSeeder::class);
        }

        $ufs = Tenant::query()
            ->whereNotNull('estado')
            ->where('estado', '!=', '')
            ->distinct()
            ->pluck('estado')
            ->all();

        if ($ufs === []) {
            $ufs = ['SP'];
            $this->command?->warn('Nenhum tenant com UF definida — o diretor foi criado apenas com SP.');
        }

        $director = User::withoutGlobalScopes()->firstOrNew(['email' => 'diretor@legiscola.test']);

        $director->fill([
            'tenant_id' => null,
            'name' => 'Diretor Regional (dev)',
            'password' => Hash::make('password'),
            'user_type' => User::TYPE_TENANT_DIRECTOR,
            'status' => User::STATUS_ATIVO,
            'email_verified_at' => now(),
        ])->save();

        $director->syncRoles([User::TYPE_TENANT_DIRECTOR]);

        $director->directorUfs()->delete();

        foreach ($ufs as $uf) {
            DirectorUf::create(['user_id' => $director->id, 'uf' => $uf]);
        }

        $this->command?->info("Diretor: {$director->email} / password — UFs: ".implode(', ', $ufs));
    }
}
