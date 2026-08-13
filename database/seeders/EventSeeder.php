<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    private const SEED_TITLE_PREFIX = '[Seed] ';

    public function run(): void
    {
        $tenant = Tenant::query()->orderBy('id')->first();
        if ($tenant === null) {
            $this->command?->warn('EventSeeder: nenhum tenant. Execute TenantSeeder antes.');

            return;
        }

        $titles = [
            'Seminário de Cidadania',
            'Oficina de Orçamento Participativo',
            'Encontro de Vereadores Iniciantes',
            'Palestra: Transparência Pública',
            'Workshop de Redação de Projetos de Lei',
            'Mesa: Direitos Humanos na Câmara',
            'Curso Intensivo de Processo Legislativo',
            'Webinar: LGPD na Administração',
            'Capacitação em Atendimento ao Cidadão',
            'Simpósio de Educação Legislativa',
            'Rodada: Sustentabilidade Urbana',
            'Debate: Participação Popular',
            'Oficina de Comunicação Digital',
            'Encontro Regional de Escolas do Legislativo',
            'Conferência de Boas Práticas Municipais',
        ];

        $cities = ['Leme', 'Campinas', 'Araras', 'Rio Claro', 'Piracicaba', 'Limeira', 'Americana'];

        $created = 0;
        foreach ($titles as $idx => $title) {
            $fullTitle = self::SEED_TITLE_PREFIX.$title;
            if (Event::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('title', $fullTitle)->exists()) {
                continue;
            }

            $n = $idx + 1;
            $startsAt = now()->addMonths($n)->setTime(9, 0);

            Event::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'title' => $fullTitle,
                'description' => 'Evento de demonstração gerado pelo EventSeeder.',
                'allow_online_registration' => $n % 3 !== 0,
                'com_certificado' => $n % 2 === 0,
                'registration_starts_at' => now()->subWeek(),
                'registration_ends_at' => $startsAt->copy()->subDay(),
                'max_seats' => 40 + ($n * 5),
                'date_time' => $startsAt,
                'zipcode' => sprintf('13610-%03d', ($n * 7) % 300),
                'address' => 'Praça Central',
                'number' => (string) (100 + $n),
                'complement' => 'Auditório',
                'district' => 'Centro',
                'city' => $cities[$idx % count($cities)],
                'state' => 'SP',
                'photo_path' => null,
            ]);
            $created++;
        }

        if ($created === 0) {
            $this->command?->info('EventSeeder: eventos de seed já existiam (nenhum novo).');
        } else {
            $this->command?->info("EventSeeder: {$created} evento(s) de demonstração criado(s).");
        }
    }
}
