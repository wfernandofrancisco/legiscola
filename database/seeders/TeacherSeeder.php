<?php

namespace Database\Seeders;

use App\Models\Teacher;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class TeacherSeeder extends Seeder
{
    private const SEED_EMAIL_DOMAIN = 'professor_seed.invalid';

    public function run(): void
    {
        $tenant = Tenant::query()->orderBy('id')->first();
        if ($tenant === null) {
            $this->command?->warn('TeacherSeeder: nenhum tenant. Execute TenantSeeder antes.');

            return;
        }

        $existing = Teacher::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('email', 'like', 'prof_seed_%@'.self::SEED_EMAIL_DOMAIN)
            ->count();

        if ($existing >= 10) {
            $this->command?->info('TeacherSeeder: já existem 10+ professores de seed para este tenant.');

            return;
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::firstOrCreate(
            ['name' => 'tenant_professor', 'guard_name' => 'web'],
            ['type' => 'tenant']
        );

        $specialities = [
            'Direito Constitucional',
            'Processo Legislativo',
            'Orçamento Público',
            'Administração Pública',
            'Educação Cidadã',
            'Comunicação Institucional',
            'Legislação Municipal',
            'Ética e Transparência',
            'Gestão de Projetos',
            'Metodologia Ativa',
        ];

        for ($i = 1; $i <= 10; $i++) {
            $email = sprintf('prof_seed_%d_%d@'.self::SEED_EMAIL_DOMAIN, $i, $tenant->id);

            $user = User::query()->firstOrCreate(
                ['email' => $email],
                [
                    'tenant_id' => $tenant->id,
                    'name' => sprintf('Professor(a) Seed %d', $i),
                    'password' => Hash::make('password'),
                    'user_type' => User::TYPE_TENANT_RESPONSIBLE,
                    'status' => User::STATUS_ATIVO,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('tenant_professor')) {
                $user->assignRole('tenant_professor');
            }

            Teacher::withoutGlobalScopes()->updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'user_id' => $user->id,
                ],
                [
                    'full_name' => sprintf('Professor(a) Seed %d', $i),
                    'email' => $email,
                    'phone' => sprintf('(19) 9%04d-%04d', ($i * 17) % 10000, ($i * 31) % 10000),
                    'photo_path' => null,
                    'status' => 'ativo',
                    'bio' => 'Perfil criado automaticamente pelo TeacherSeeder.',
                    'specialities' => $specialities[$i - 1] ?? 'Multidisciplinar',
                ]
            );
        }

        $this->command?->info('TeacherSeeder: 10 professores (usuários docentes) criados ou já existentes.');
    }
}
