<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Jobs\SendEmpresaRelacaoAvisoJob;
use App\Models\EmpresaRelacao;
use App\Services\CnpjImportService;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cnpj:import {competencia} {--requested-by=} {--initialize-only} {--run-id=} {--manual} {--manual-dir=}', function (CnpjImportService $service) {
    $competencia = (string) $this->argument('competencia');
    $requestedByOption = $this->option('requested-by');
    $requestedBy = is_numeric($requestedByOption) ? (int) $requestedByOption : null;
    $runIdOption = $this->option('run-id');
    $runId = is_numeric($runIdOption) ? (int) $runIdOption : null;
    $manual = (bool) $this->option('manual');
    $manualDir = $this->option('manual-dir');

    $modeText = $manual ? 'manual' : 'download';
    $this->info("Iniciando importacao CNPJ: {$competencia} (modo: {$modeText})");
    if ($manual && $manualDir) {
        $this->line("Diretorio manual informado: {$manualDir}");
    }

    if ($runId) {
        $this->line("Usando run existente #{$runId} para processar pipeline completo...");
        $run = $service->executeEstabelecimentos($runId, $manual, is_string($manualDir) ? $manualDir : null);
    } else {
        $run = $service->initializeRun($competencia, $requestedBy);

        if ((bool) $this->option('initialize-only')) {
            $run = $service->markRunInitialized($run);
        } else {
            $run = $service->executeEstabelecimentos($run->id, $manual, is_string($manualDir) ? $manualDir : null);
        }
    }

    $this->newLine();
    $this->info("Run #{$run->id} criada com status: {$run->status}");
    $this->line("Competencia: {$run->competencia}");
    $this->line('Steps criados:');

    foreach ($run->steps as $step) {
        $this->line("- {$step->step}: {$step->status}");
    }
})->purpose('Inicializa pipeline de importacao CNPJ por competencia');

Artisan::command('cnpj:sync-dominios {competencia} {--manual-dir=}', function (CnpjImportService $service) {
    $competencia = (string) $this->argument('competencia');
    $manualDir = $this->option('manual-dir');
    $manualDir = is_string($manualDir) && trim($manualDir) !== '' ? $manualDir : null;

    $this->info("Sincronizando dominios (CNAE, Naturezas, Motivos) da competencia {$competencia}...");
    if ($manualDir) {
        $this->line("Diretorio manual: {$manualDir}");
    }

    $result = $service->syncDominiosOnly($competencia, $manualDir);

    $this->newLine();
    $this->info('Sincronizacao de dominios concluida.');
    $this->table(
        ['tabela', 'registros_sincronizados'],
        [
            ['cnaes', $result['cnaes'] ?? 0],
            ['naturezas_juridicas', $result['naturezas'] ?? 0],
            ['motivos', $result['motivos'] ?? 0],
        ]
    );
})->purpose('Sincroniza somente tabelas de dominio (CNAE, Naturezas, Motivos)');

Artisan::command('cnpj:debug-municipios {competencia} {--manual-dir=} {--top=20} {--codes=}', function () {
    $competencia = (string) $this->argument('competencia');
    $manualDirOption = $this->option('manual-dir');
    $manualDir = is_string($manualDirOption) && trim($manualDirOption) !== ''
        ? $manualDirOption
        : storage_path("app/private/imports/manual/{$competencia}");

    if (! is_dir($manualDir)) {
        $this->error("Diretorio nao encontrado: {$manualDir}");
        return;
    }

    $files = glob($manualDir.DIRECTORY_SEPARATOR.'*') ?: [];
    $estabeleFiles = array_values(array_filter($files, static function (string $path): bool {
        return is_file($path) && str_contains(strtoupper(basename($path)), 'ESTABELE');
    }));

    if ($estabeleFiles === []) {
        $this->error("Nenhum arquivo ESTABELE encontrado em {$manualDir}");
        return;
    }

    $counts = [];
    $rows = 0;

    foreach ($estabeleFiles as $file) {
        $handle = fopen($file, 'rb');
        if ($handle === false) {
            $this->warn("Nao foi possivel abrir {$file}");
            continue;
        }

        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (! is_array($row) || count($row) < 21) {
                continue;
            }

            $rows++;
            $municipio = preg_replace('/\D/', '', trim((string) ($row[20] ?? ''))) ?? '';
            if ($municipio === '') {
                $municipio = '(vazio)';
            }

            $counts[$municipio] = ($counts[$municipio] ?? 0) + 1;
        }

        fclose($handle);
    }

    arsort($counts);
    $top = max(1, (int) $this->option('top'));

    $this->info("Linhas lidas: {$rows}");
    $this->info('Top codigos de municipio no arquivo ESTABELE:');
    $this->table(
        ['codigo_municipio', 'total'],
        array_map(
            static fn (string $codigo, int $total) => [$codigo, $total],
            array_keys(array_slice($counts, 0, $top, true)),
            array_values(array_slice($counts, 0, $top, true)),
        )
    );

    $codesOption = $this->option('codes');
    if (is_string($codesOption) && trim($codesOption) !== '') {
        $codes = array_values(array_filter(array_map(
            static fn (string $code) => preg_replace('/\D/', '', $code) ?? '',
            explode(',', $codesOption)
        )));

        if ($codes !== []) {
            $this->info('Contagem para codigos informados:');
            $this->table(
                ['codigo_municipio', 'total'],
                array_map(
                    static fn (string $code) => [$code, $counts[$code] ?? 0],
                    $codes
                )
            );
        }
    }
})->purpose('Diagnostica codigos de municipio presentes no arquivo ESTABELE manual');

Artisan::command('cnpj:normalizar-enderecos
    {--tenant-id= : Processa apenas um tenant}
    {--limit=5000 : Maximo de registros por execucao}
    {--chunk=500 : Tamanho do bloco no processamento}
    {--sleep-ms=0 : Espera entre chamadas externas}
    {--overwrite : Reprocessa mesmo ja normalizados}
', function () {
    $tenantId = $this->option('tenant-id');
    $limit = max(1, (int) $this->option('limit'));
    $chunkSize = max(1, (int) $this->option('chunk'));
    $sleepMs = max(0, (int) $this->option('sleep-ms'));
    $overwrite = (bool) $this->option('overwrite');

    $this->info('Iniciando normalizacao de enderecos por CEP...');

    $baseQuery = DB::table('empresas')
        ->select(['id', 'tenant_id', 'cep', 'logradouro', 'bairro', 'uf'])
        ->whereNotNull('cep')
        ->whereRaw('CHAR_LENGTH(cep) = 8');

    if ($tenantId !== null && $tenantId !== '') {
        $baseQuery->where('tenant_id', (int) $tenantId);
    }

    if (! $overwrite) {
        $baseQuery->whereNull('endereco_normalizado_at');
    }

    $totalElegiveis = (clone $baseQuery)->count();
    $this->line("Registros elegiveis: {$totalElegiveis}");

    $remaining = $limit;
    $processed = 0;
    $updated = 0;
    $notFound = 0;
    $invalidCep = 0;
    $apiErrors = 0;

    /** @var array<string, array{ok:bool,logradouro:?string,bairro:?string,fonte:string}> $cepCache */
    $cepCache = [];

    while ($remaining > 0) {
        $batch = (clone $baseQuery)
            ->orderBy('id')
            ->limit(min($chunkSize, $remaining))
            ->get();

        if ($batch->isEmpty()) {
            break;
        }

        foreach ($batch as $empresa) {
            $processed++;
            $remaining--;

            $cep = preg_replace('/\D/', '', (string) $empresa->cep) ?? '';
            if (strlen($cep) !== 8) {
                DB::table('empresas')->where('id', $empresa->id)->update([
                    'endereco_normalizacao_status' => 'cep_invalido',
                    'endereco_normalizacao_fonte' => null,
                    'endereco_normalizado_at' => now(),
                    'updated_at' => now(),
                ]);
                $invalidCep++;
                continue;
            }

            if (! isset($cepCache[$cep])) {
                $cepCache[$cep] = (function () use ($cep, $sleepMs): array {
                    if ($sleepMs > 0) {
                        usleep($sleepMs * 1000);
                    }

                    try {
                        $resp = Http::timeout(20)->get("https://viacep.com.br/ws/{$cep}/json/");
                        if (! $resp->successful()) {
                            return ['ok' => false, 'logradouro' => null, 'bairro' => null, 'fonte' => 'viacep_http_error'];
                        }

                        $data = $resp->json();
                        if (! is_array($data) || ($data['erro'] ?? false) === true) {
                            return ['ok' => false, 'logradouro' => null, 'bairro' => null, 'fonte' => 'viacep_not_found'];
                        }

                        $logradouro = isset($data['logradouro']) ? trim((string) $data['logradouro']) : null;
                        $bairro = isset($data['bairro']) ? trim((string) $data['bairro']) : null;

                        return [
                            'ok' => true,
                            'logradouro' => $logradouro !== '' ? $logradouro : null,
                            'bairro' => $bairro !== '' ? $bairro : null,
                            'fonte' => 'viacep',
                        ];
                    } catch (\Throwable) {
                        return ['ok' => false, 'logradouro' => null, 'bairro' => null, 'fonte' => 'viacep_exception'];
                    }
                })();
            }

            $lookup = $cepCache[$cep];

            if (! $lookup['ok']) {
                DB::table('empresas')->where('id', $empresa->id)->update([
                    'endereco_normalizacao_status' => $lookup['fonte'] === 'viacep_not_found' ? 'nao_encontrado' : 'erro_api',
                    'endereco_normalizacao_fonte' => $lookup['fonte'],
                    'endereco_normalizado_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($lookup['fonte'] === 'viacep_not_found') {
                    $notFound++;
                } else {
                    $apiErrors++;
                }
                continue;
            }

            DB::table('empresas')->where('id', $empresa->id)->update([
                'logradouro_normalizado' => $lookup['logradouro'],
                'bairro_normalizado' => $lookup['bairro'],
                'endereco_normalizacao_status' => 'normalizado',
                'endereco_normalizacao_fonte' => $lookup['fonte'],
                'endereco_normalizado_at' => now(),
                'updated_at' => now(),
            ]);
            $updated++;
        }
    }

    $this->newLine();
    $this->info('Normalizacao concluida.');
    $this->table(
        ['metrica', 'valor'],
        [
            ['processados', $processed],
            ['normalizados', $updated],
            ['cep_invalido', $invalidCep],
            ['nao_encontrado', $notFound],
            ['erro_api', $apiErrors],
            ['cache_ceps_distintos', count($cepCache)],
        ]
    );
})->purpose('Normaliza logradouro e bairro em empresas via API de CEP');

Artisan::command('cnpj:geocodificar-enderecos
    {--tenant-id= : Processa apenas um tenant}
    {--limit=5000 : Maximo de registros por execucao}
    {--chunk=250 : Tamanho do bloco no processamento}
    {--sleep-ms=0 : Espera entre chamadas externas}
    {--provider=nominatim : Provedor de geocoding (nominatim|google)}
    {--overwrite : Reprocessa mesmo sem mudanca de endereco}
', function () {
    $provider = strtolower((string) $this->option('provider'));
    if (! in_array($provider, ['nominatim', 'google'], true)) {
        $this->error("Provider invalido: {$provider}. Use nominatim ou google.");
        return;
    }

    $googleApiKey = (string) config('services.google_maps.key', env('GOOGLE_MAPS_GEOCODING_KEY', ''));
    if ($provider === 'google' && trim($googleApiKey) === '') {
        $this->error('Defina GOOGLE_MAPS_GEOCODING_KEY no .env para usar provider=google.');
        return;
    }

    $tenantId = $this->option('tenant-id');
    $limit = max(1, (int) $this->option('limit'));
    $chunkSize = max(1, (int) $this->option('chunk'));
    $sleepMs = max(0, (int) $this->option('sleep-ms'));
    $overwrite = (bool) $this->option('overwrite');

    $this->info("Iniciando geocodificacao de enderecos (provider: {$provider})...");

    $baseQuery = DB::table('empresas')
        ->select([
            'id',
            'tenant_id',
            'logradouro_normalizado',
            'logradouro',
            'numero',
            'bairro_normalizado',
            'bairro',
            'cep',
            'uf',
            'latitude',
            'longitude',
            'geocoding_status',
            'geocoding_address_hash',
        ])
        ->whereNotNull('cep')
        ->whereRaw('CHAR_LENGTH(cep) = 8');

    if ($tenantId !== null && $tenantId !== '') {
        $baseQuery->where('tenant_id', (int) $tenantId);
    }

    $totalElegiveis = (clone $baseQuery)->count();
    $this->line("Registros elegiveis: {$totalElegiveis}");

    $remaining = $limit;
    $processed = 0;
    $updated = 0;
    $skippedUnchanged = 0;
    $invalidAddress = 0;
    $zeroResults = 0;
    $apiErrors = 0;

    /** @var array<string, array{ok:bool,lat:?float,lng:?float,status:string}> $hashCache */
    $hashCache = [];

    while ($remaining > 0) {
        $batch = (clone $baseQuery)
            ->orderBy('id')
            ->limit(min($chunkSize, $remaining))
            ->get();

        if ($batch->isEmpty()) {
            break;
        }

        foreach ($batch as $empresa) {
            $processed++;
            $remaining--;

            $logradouroBase = trim((string) ($empresa->logradouro_normalizado ?: $empresa->logradouro));
            $bairroBase = trim((string) ($empresa->bairro_normalizado ?: $empresa->bairro));
            $numero = trim((string) ($empresa->numero ?? ''));
            $cep = preg_replace('/\D/', '', (string) $empresa->cep) ?? '';
            $uf = trim((string) ($empresa->uf ?? ''));

            if ($logradouroBase === '' || $cep === '' || strlen($cep) !== 8) {
                DB::table('empresas')->where('id', $empresa->id)->update([
                    'geocoding_status' => 'invalid_address',
                    'geocoding_source' => $provider,
                    'geocoding_last_attempt_at' => now(),
                    'updated_at' => now(),
                ]);
                $invalidAddress++;
                continue;
            }

            $addressParts = array_values(array_filter([
                $logradouroBase,
                $numero,
                $bairroBase,
                $cep,
                $uf,
                'Brasil',
            ], static fn (string $part): bool => $part !== ''));

            $address = implode(', ', $addressParts);
            $addressHash = hash('sha256', mb_strtolower($address));

            $isUnchanged = $empresa->geocoding_address_hash === $addressHash
                && $empresa->latitude !== null
                && $empresa->longitude !== null
                && $empresa->geocoding_status === 'ok';

            if (! $overwrite && $isUnchanged) {
                $skippedUnchanged++;
                continue;
            }

            if (! isset($hashCache[$addressHash])) {
                if ($sleepMs > 0) {
                    usleep($sleepMs * 1000);
                }

                try {
                    if ($provider === 'google') {
                        $response = Http::timeout(20)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                            'address' => $address,
                            'key' => $googleApiKey,
                        ]);

                        if (! $response->successful()) {
                            $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => 'HTTP_ERROR'];
                        } else {
                            $data = $response->json();
                            $status = (string) ($data['status'] ?? '');

                            if ($status === 'OK' && isset($data['results'][0]['geometry']['location'])) {
                                $location = $data['results'][0]['geometry']['location'];
                                $hashCache[$addressHash] = [
                                    'ok' => true,
                                    'lat' => isset($location['lat']) ? (float) $location['lat'] : null,
                                    'lng' => isset($location['lng']) ? (float) $location['lng'] : null,
                                    'status' => 'OK',
                                ];
                            } elseif ($status === 'ZERO_RESULTS') {
                                $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => 'ZERO_RESULTS'];
                            } else {
                                $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => $status !== '' ? $status : 'UNKNOWN'];
                            }
                        }
                    } else {
                        $response = Http::timeout(20)
                            ->withHeaders([
                                'User-Agent' => 'desenvolve-city/1.0 (contato@desenvolve.city)',
                            ])
                            ->get('https://nominatim.openstreetmap.org/search', [
                                'q' => $address,
                                'format' => 'jsonv2',
                                'limit' => 1,
                                'addressdetails' => 0,
                            ]);

                        if (! $response->successful()) {
                            $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => 'HTTP_ERROR'];
                        } else {
                            $data = $response->json();
                            if (is_array($data) && isset($data[0]['lat'], $data[0]['lon'])) {
                                $hashCache[$addressHash] = [
                                    'ok' => true,
                                    'lat' => (float) $data[0]['lat'],
                                    'lng' => (float) $data[0]['lon'],
                                    'status' => 'OK',
                                ];
                            } else {
                                $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => 'ZERO_RESULTS'];
                            }
                        }
                    }
                } catch (\Throwable) {
                    $hashCache[$addressHash] = ['ok' => false, 'lat' => null, 'lng' => null, 'status' => 'EXCEPTION'];
                }
            }

            $geo = $hashCache[$addressHash];
            $now = now();

            if ($geo['ok'] && $geo['lat'] !== null && $geo['lng'] !== null) {
                DB::table('empresas')->where('id', $empresa->id)->update([
                    'latitude' => $geo['lat'],
                    'longitude' => $geo['lng'],
                    'geocoding_status' => 'ok',
                    'geocoding_source' => $provider,
                    'geocoded_at' => $now,
                    'geocoding_address_hash' => $addressHash,
                    'geocoding_last_attempt_at' => $now,
                    'updated_at' => $now,
                ]);
                $updated++;
                continue;
            }

            $mappedStatus = $geo['status'] === 'ZERO_RESULTS' ? 'zero_results' : 'api_error';
            DB::table('empresas')->where('id', $empresa->id)->update([
                'geocoding_status' => $mappedStatus,
                'geocoding_source' => $provider,
                'geocoding_address_hash' => $addressHash,
                'geocoding_last_attempt_at' => $now,
                'updated_at' => $now,
            ]);

            if ($mappedStatus === 'zero_results') {
                $zeroResults++;
            } else {
                $apiErrors++;
            }
        }
    }

    $this->newLine();
    $this->info('Geocodificacao concluida.');
    $this->table(
        ['metrica', 'valor'],
        [
            ['processados', $processed],
            ['atualizados_com_coord', $updated],
            ['pulados_sem_mudanca', $skippedUnchanged],
            ['endereco_invalido', $invalidAddress],
            ['zero_results', $zeroResults],
            ['erro_api', $apiErrors],
            ['enderecos_unicos_consultados', count($hashCache)],
        ]
    );
})->purpose('Geocodifica empresas por provider, evitando chamadas sem mudanca de endereco');

Artisan::command('empresa-relacoes:enviar-avisos', function () {
    $now = now();

    $relacoes = EmpresaRelacao::query()
        ->whereNotNull('data_aviso')
        ->whereNull('aviso_enviado_em')
        ->where('data_aviso', '<=', $now)
        ->get(['id']);

    foreach ($relacoes as $relacao) {
        SendEmpresaRelacaoAvisoJob::dispatch($relacao->id);
    }

    $this->info("Avisos enfileirados: {$relacoes->count()}");
})->purpose('Envia avisos de relacoes de empresas vencidos');
