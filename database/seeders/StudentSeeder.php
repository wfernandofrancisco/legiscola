<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use App\Support\NominatimGeocoder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    private const LEME_UF = 'SP';

    private const LEME_CIDADE = 'Leme';

    /** Centro aproximado de Leme/SP (fallback se Nominatim falhar). */
    private const LEME_FALLBACK_LAT = -22.185556;

    private const LEME_FALLBACK_LON = -47.390278;

    /** Cache de CEP → coordenadas (evita ~500 chamadas a cada seed). */
    private const GEOCODE_CACHE_PATH = 'seeders/leme_ceps_geocode.json';

    /** Nomes comuns no Brasil (seed determinístico). */
    private const NOMES_MASCULINOS = [
        'João', 'Pedro', 'Lucas', 'Gabriel', 'Matheus', 'Rafael', 'Bruno', 'Felipe', 'Gustavo', 'Daniel',
        'Carlos', 'André', 'Marcos', 'Paulo', 'Ricardo', 'Rodrigo', 'Thiago', 'Fernando', 'Eduardo', 'Vinícius',
        'Leonardo', 'Diego', 'Henrique', 'Alexandre', 'Antônio', 'Francisco', 'Roberto', 'Marcelo', 'Renato', 'César',
        'Igor', 'Caio', 'Murilo', 'Arthur', 'Enzo', 'Miguel', 'Davi', 'Bernardo', 'Heitor', 'Lorenzo',
    ];

    private const NOMES_FEMININOS = [
        'Maria', 'Ana', 'Juliana', 'Fernanda', 'Patrícia', 'Camila', 'Amanda', 'Larissa', 'Beatriz', 'Mariana',
        'Bruna', 'Letícia', 'Gabriela', 'Rafaela', 'Priscila', 'Vanessa', 'Carla', 'Daniela', 'Adriana', 'Renata',
        'Aline', 'Tatiane', 'Simone', 'Cristina', 'Luciana', 'Sandra', 'Cláudia', 'Mônica', 'Eliane', 'Rosana',
        'Isabela', 'Luiza', 'Sophia', 'Helena', 'Alice', 'Laura', 'Valentina', 'Manuela', 'Lívia', 'Yasmin',
    ];

    private const SOBRENOMES = [
        'Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes',
        'Ribeiro', 'Carvalho', 'Almeida', 'Martins', 'Rocha', 'Costa', 'Araújo', 'Melo', 'Barbosa', 'Cardoso',
        'Correia', 'Dias', 'Teixeira', 'Monteiro', 'Mendes', 'Nascimento', 'Freitas', 'Cavalcanti', 'Campos', 'Duarte',
        'Moreira', 'Nunes', 'Machado', 'Castro', 'Vieira', 'Fernandes', 'Andrade', 'Peixoto', 'Tavares', 'Ramos',
        'Borges', 'Pinto', 'Coelho', 'Moura', 'Xavier', 'Reis', 'Azevedo', 'Brito', 'Cunha', 'Lopes',
    ];

    /** Logradouros reais ou típicos de Leme/SP (CEP 13610-xxx). */
    private const RUAS_LEME = [
        'Rua Barão de Campinas',
        'Rua Tiradentes',
        'Rua XV de Novembro',
        'Avenida Padre Tobias',
        'Avenida Comendador Hermenegildo Lunardi',
        'Rua Francisco Franco',
        'Rua Duque de Caxias',
        'Rua Prudente de Morais',
        'Rua São Paulo',
        'Rua Rio Branco',
        'Rua Campos Salles',
        'Rua Doutor Lauro Corrêa da Silva',
        'Avenida Doutor Luciano Esteves',
        'Rua Doutor Armando Salles Oliveira',
        'Rua Major Matheus',
        'Rua Coronel Virgílio de Melo Franco',
        'Rua Doutor Coutinho',
        'Rua Benjamin Constant',
        'Rua José Bonifácio',
        'Rua Marechal Deodoro',
        'Rua General Osório',
        'Rua Sete de Setembro',
        'Rua Quinze de Novembro',
        'Rua Dom Pedro II',
        'Rua Dom Bosco',
    ];

    public function run(): void
    {
        $tenant = Tenant::query()->orderBy('id')->first();
        if (! $tenant) {
            $this->command?->warn('StudentSeeder: nenhum tenant encontrado. Execute TenantSeeder antes.');

            return;
        }

        if (Student::withoutGlobalScopes()->where('enrollment_number', 'like', 'LM-SEED-%')->exists()) {
            $this->command?->warn('StudentSeeder: já existem alunos LM-SEED-*. Remova-os ou use migrate:fresh para gerar novamente.');

            return;
        }

        $orphansRemoved = $this->deleteOrphanSeedUsersForTenant((int) $tenant->id);
        if ($orphansRemoved > 0) {
            $this->command?->info(sprintf('StudentSeeder: removidos %d usuário(s) órfão(s) de seed anterior (e-mail aluno_seed_* sem aluno).', $orphansRemoved));
        }

        $bairros = ['Centro', 'Jardim América', 'Vila Nova', 'Parque das Nações', 'Santa Terezinha'];

        $geocodeCache = $this->loadGeocodeCache();
        $cacheMissing = 0;
        for ($j = 1; $j <= 500; $j++) {
            $d = '13610'.str_pad((string) $j, 3, '0', STR_PAD_LEFT);
            if (! isset($geocodeCache[$d])) {
                $cacheMissing++;
            }
        }
        if ($cacheMissing > 0) {
            $this->command?->info(sprintf(
                'StudentSeeder: geocodificando %d CEP(s) via Nominatim (~%.0f s na 1ª vez; depois usa cache em storage/app/%s).',
                $cacheMissing,
                $cacheMissing * (max(0, (int) config('services.nominatim.min_request_interval_ms', 1100)) / 1000),
                self::GEOCODE_CACHE_PATH
            ));
        }

        $geocodeCacheDirty = false;
        $nominatimFailures = 0;

        for ($i = 1; $i <= 500; $i++) {
            $email = sprintf('aluno_seed_%d_%d@seed.invalid', $i, $tenant->id);

            $cpf = $this->uniqueRandomCpf();
            if (Student::withoutGlobalScopes()->where('cpf', $cpf)->exists()) {
                $cpf = $this->uniqueRandomCpf();
            }

            $sexo = ['masculino', 'feminino', 'outro', 'nao_informado'][$i % 4];
            $nomeExibicao = $this->nomeBrasileiroParaIndice($i, $sexo);

            $user = User::query()->create([
                'tenant_id' => $tenant->id,
                'name' => $nomeExibicao,
                'email' => $email,
                'password' => Hash::make('password'),
                'user_type' => User::TYPE_TENANT_USER,
                'status' => User::STATUS_ATIVO,
            ]);
            $user->assignRole('tenant_user');

            $cep = sprintf('13610-%03d', $i);

            $logradouro = self::RUAS_LEME[($i - 1) % count(self::RUAS_LEME)];
            $numero = (string) (($i % 1800) + 1);
            $bairro = $bairros[$i % count($bairros)];

            $coords = $this->resolveCoordsForCep($cep, $i, $geocodeCache, $geocodeCacheDirty, $nominatimFailures);
            if ($geocodeCacheDirty && $i % 25 === 0) {
                $this->saveGeocodeCache($geocodeCache);
                $geocodeCacheDirty = false;
            }

            Student::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'user_id' => $user->id,
                'email' => $email,
                'enrollment_number' => sprintf('LM-SEED-%05d', $i),
                'birth_date' => now()->subYears(20 + ($i % 40))->subDays($i % 300),
                'sexo' => $sexo,
                'cpf' => $cpf,
                'telefone' => null,
                'celular' => sprintf('(19) 9%04d-%04d', $i % 10000, ($i * 7) % 10000),
                'cep' => $cep,
                'logradouro' => $logradouro,
                'numero' => $numero,
                'bairro' => $bairro,
                'cidade' => self::LEME_CIDADE,
                'uf' => self::LEME_UF,
                'latitude' => $coords['latitude'],
                'longitude' => $coords['longitude'],
                'profissao' => null,
                'escolaridade' => null,
                'photo_path' => null,
                'status' => 'ativo',
            ]);
        }

        if ($geocodeCacheDirty) {
            $this->saveGeocodeCache($geocodeCache);
        }

        if ($nominatimFailures > 0) {
            $this->command?->warn(sprintf(
                'StudentSeeder: Nominatim não retornou ponto para %d CEP(s); foi usado fallback ao redor do centro de Leme. Verifique rede e NOMINATIM_USER_AGENT / e-mail em .env.',
                $nominatimFailures
            ));
        }

        $this->command?->info('500 alunos (seed) criados com CEPs 13610-001 a 13610-500 e coordenadas (Nominatim ou fallback).');
    }

    /**
     * @param  array<string, array{latitude: float, longitude: float}>  $cache
     */
    private function resolveCoordsForCep(string $cep, int $seedIndex, array &$cache, bool &$cacheDirty, int &$nominatimFailures): array
    {
        $digits = preg_replace('/\D/', '', $cep) ?? '';
        if ($digits === '') {
            $nominatimFailures++;

            return $this->fallbackCoords($seedIndex);
        }

        if (isset($cache[$digits])) {
            return $cache[$digits];
        }

        $coords = NominatimGeocoder::geocode([
            'cep' => $cep,
            'cidade' => self::LEME_CIDADE,
            'uf' => self::LEME_UF,
        ]);

        if ($coords === null) {
            $nominatimFailures++;

            return $this->fallbackCoords($seedIndex);
        }

        $cache[$digits] = $coords;
        $cacheDirty = true;

        return $coords;
    }

    /**
     * @return array{latitude: float, longitude: float}
     */
    private function fallbackCoords(int $seedIndex): array
    {
        $lat = self::LEME_FALLBACK_LAT + (($seedIndex % 37) - 18) * 0.00025;
        $lon = self::LEME_FALLBACK_LON + (((int) ($seedIndex / 37) % 37) - 18) * 0.00025;

        return ['latitude' => $lat, 'longitude' => $lon];
    }

    /**
     * @return array<string, array{latitude: float, longitude: float}>
     */
    private function loadGeocodeCache(): array
    {
        $path = storage_path('app/'.self::GEOCODE_CACHE_PATH);
        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);
        if (! is_array($decoded)) {
            return [];
        }

        $out = [];
        foreach ($decoded as $cepDigits => $pair) {
            if (! is_string($cepDigits) || ! is_array($pair)) {
                continue;
            }
            if (! isset($pair['latitude'], $pair['longitude'])) {
                continue;
            }
            $out[$cepDigits] = [
                'latitude' => (float) $pair['latitude'],
                'longitude' => (float) $pair['longitude'],
            ];
        }

        return $out;
    }

    /**
     * @param  array<string, array{latitude: float, longitude: float}>  $cache
     */
    private function saveGeocodeCache(array $cache): void
    {
        $dir = storage_path('app/seeders');
        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        ksort($cache, SORT_STRING);
        File::put(
            storage_path('app/'.self::GEOCODE_CACHE_PATH),
            json_encode($cache, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n"
        );
    }

    /**
     * Nome composto típico no Brasil (determinístico por índice + sexo).
     */
    private function nomeBrasileiroParaIndice(int $i, string $sexo): string
    {
        $masc = self::NOMES_MASCULINOS;
        $fem = self::NOMES_FEMININOS;
        $sob = self::SOBRENOMES;

        $pool = match ($sexo) {
            'feminino' => $fem,
            'masculino' => $masc,
            default => ($i % 2 === 0) ? $fem : $masc,
        };

        $primeiro = $pool[($i * 31 + 7) % count($pool)];
        $s1 = $sob[($i * 11) % count($sob)];
        $s2 = $sob[(intdiv($i, 5) * 13 + 3) % count($sob)];
        if ($s1 === $s2) {
            $s2 = $sob[($i + 19) % count($sob)];
        }

        return $i % 6 === 0
            ? sprintf('%s %s %s', $primeiro, $s1, $s2)
            : sprintf('%s %s', $primeiro, $s1);
    }

    /**
     * Libera e-mails únicos após seed interrompido (user criado, Student não).
     * Inclui soft-deleted: unique em `users.email` continua bloqueando reinsert.
     */
    private function deleteOrphanSeedUsersForTenant(int $tenantId): int
    {
        $like = sprintf('aluno_seed_%%_%d@seed.invalid', $tenantId);

        $users = User::withTrashed()
            ->where('tenant_id', $tenantId)
            ->where('email', 'like', $like)
            ->whereDoesntHave('student', function ($q): void {
                $q->withoutGlobalScopes();
            })
            ->get();

        $removed = 0;
        foreach ($users as $user) {
            $user->syncRoles([]);
            $user->forceDelete();
            $removed++;
        }

        return $removed;
    }

    private function uniqueRandomCpf(): string
    {
        for ($attempt = 0; $attempt < 50; $attempt++) {
            $cpf = $this->randomValidCpfDigits();
            if (! Student::withoutGlobalScopes()->where('cpf', $cpf)->exists()) {
                return $cpf;
            }
        }

        return $this->randomValidCpfDigits();
    }

    private function randomValidCpfDigits(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[$i] = random_int(0, 9);
        }
        while (count(array_unique($n)) === 1) {
            $n[8] = random_int(0, 9);
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($c = 0; $c < $t; $c++) {
                $sum += $n[$c] * (($t + 1) - $c);
            }
            $digit = ((10 * $sum) % 11) % 10;
            $n[$t] = $digit;
        }

        return sprintf(
            '%d%d%d.%d%d%d.%d%d%d-%d%d',
            $n[0],
            $n[1],
            $n[2],
            $n[3],
            $n[4],
            $n[5],
            $n[6],
            $n[7],
            $n[8],
            $n[9],
            $n[10]
        );
    }
}
