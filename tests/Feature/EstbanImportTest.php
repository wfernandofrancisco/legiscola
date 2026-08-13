<?php

use App\Contracts\Repositories\TenantLookupRepositoryInterface;
use App\Models\CentralProcessRun;
use App\Models\EstbanLinha;
use App\Models\Tenant;
use App\Services\EstbanCsvImportService;
use Illuminate\Support\Facades\Storage;

it('importa todos csv do ano na pasta estaban/{ano}', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => '1100015',
    ]);

    $yearDir = EstbanCsvImportService::MANUAL_RELATIVE_ROOT.'/2024';
    Storage::disk('local')->makeDirectory(EstbanCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($yearDir, true);

    $csv1 = "DATA_BASE;CNPJ;NOME_INSTITUICAO;AGENCIA;CO_MUNICIPIO;UF;162_POUPANCA\n".
        "202401;00000000;Banco A;1;1100015;RO;10\n";
    $csv2 = "DATA_BASE,CNPJ,NOME_INSTITUICAO,AGENCIA,CO_MUNICIPIO,UF,165_X\n".
        "202402,00000000,Banco B,2,1100015,RO,20\n";

    Storage::disk('local')->put($yearDir.'/jan.csv', $csv1);
    Storage::disk('local')->put($yearDir.'/fev.csv', $csv2);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $lookup = [
        'ibge7' => ['1100015' => (int) $tenant->id],
        'codmun' => [],
    ];
    $summary = app(EstbanCsvImportService::class)->importYearFromManualDirectory(
        $run->id,
        null,
        2024,
        $lookup,
    );

    expect((int) $summary['csv_files'])->toBe(2);
    expect((int) $summary['inserted'])->toBe(2);

    expect(EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('data_base', 202401)->count())->toBe(1);
    expect(EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('data_base', 202402)->count())->toBe(1);

    Storage::disk('local')->delete($yearDir.'/jan.csv');
    Storage::disk('local')->delete($yearDir.'/fev.csv');
    Storage::disk('local')->deleteDirectory($yearDir);
    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});

it('aceita preambulo antes do cabecalho data_base', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban 2 '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban2-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => '3550308',
    ]);

    $yearDir = EstbanCsvImportService::MANUAL_RELATIVE_ROOT.'/pest2-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(EstbanCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($yearDir, true);

    $csv = "ESTBAN - teste\nSubtitulo\n".
        "DATA_BASE,CNPJ,NOME_INSTITUICAO,AGENCIA,CO_MUNICIPIO,UF,VALOR_X\n".
        "202401,1,Banco,9,3550308,SP,42\n";

    Storage::disk('local')->put($yearDir.'/p.csv', $csv);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $summary = app(EstbanCsvImportService::class)->importYearFromManualDirectory(
        $run->id,
        $yearDir,
        2024,
        [
            'ibge7' => ['3550308' => (int) $tenant->id],
            'codmun' => [],
        ],
    );

    expect((int) $summary['inserted'])->toBe(1);

    Storage::disk('local')->deleteDirectory($yearDir);
    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});

it('aceita layout bcb 4010 com hash em data_base cnpj_estab e codmun_ibge', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban BCB '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban-bcb-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => '1100015',
    ]);

    $yearDir = EstbanCsvImportService::MANUAL_RELATIVE_ROOT.'/bcb-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(EstbanCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($yearDir, true);

    $csv = "#ESTBAN Documento 4010 por municipio\nData de geracao: 2025-02-11\n".
        "#DATA_BASE;UF;CODMUN;CODMUN_IBGE;INST;NOME_INST;AGENCIA;NOME_AGENCIA;CNPJ_ESTAB;MUNICIPIO;VERBETE_160\n".
        "202501;AC;99999;1100015;1;BANCO X;1;Ag;12345678000199;Rio Branco;100\n";

    Storage::disk('local')->put($yearDir.'/202501_ESTBAN.CSV', $csv);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $summary = app(EstbanCsvImportService::class)->importYearFromManualDirectory(
        $run->id,
        $yearDir,
        2025,
        [
            'ibge7' => ['1100015' => (int) $tenant->id],
            'codmun' => [],
        ],
    );

    expect((int) $summary['inserted'])->toBe(1);

    $linha = EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('data_base', 202501)->first();
    expect($linha)->not->toBeNull();
    expect($linha->co_municipio)->toBe('1100015');
    expect($linha->cnpj)->toContain('12345678000199');
    expect($linha->nome_instituicao)->toContain('BANCO X');

    Storage::disk('local')->deleteDirectory($yearDir);
    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});

it('usa codigo_municipio_estban quando ibge nao esta preenchido', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban cod '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban-cod-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => null,
        'codigo_municipio_estban' => '1100015',
    ]);

    $map = app(TenantLookupRepositoryInterface::class)->activeEstbanMunicipioToTenantIdMap();
    expect($map['1100015'] ?? null)->toBe((int) $tenant->id);

    $lookup = app(TenantLookupRepositoryInterface::class)->activeEstbanImportMunicipioLookup();
    expect($lookup['codmun']['1100015'] ?? null)->toBe((int) $tenant->id);
    expect($lookup['ibge7']['1100015'] ?? null)->toBe((int) $tenant->id);

    $tenant->forceDelete();
});

it('cruza pelo ibge mesmo com codigo_municipio_estban curto que nao bate no csv', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban mix '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban-mix-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => '3550308',
        'codigo_municipio_estban' => '4123',
    ]);

    $yearDir = EstbanCsvImportService::MANUAL_RELATIVE_ROOT.'/mix-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(EstbanCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($yearDir, true);

    $csv = "DATA_BASE;CO_MUNICIPIO;UF;VERBETE_1\n".
        "202501;3550308;SP;7\n";

    Storage::disk('local')->put($yearDir.'/one.csv', $csv);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $lookup = app(TenantLookupRepositoryInterface::class)->activeEstbanImportMunicipioLookup();
    $summary = app(EstbanCsvImportService::class)->importYearFromManualDirectory(
        $run->id,
        $yearDir,
        2025,
        $lookup,
    );

    expect((int) $summary['inserted'])->toBe(1);

    Storage::disk('local')->deleteDirectory($yearDir);
    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});

it('cruza codigo municipio quando excel exporta com sufixo decimal .0', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Estban float '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-estban-float-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_ibge_municipio' => null,
        'codigo_municipio_estban' => '3426704',
    ]);

    $yearDir = EstbanCsvImportService::MANUAL_RELATIVE_ROOT.'/float-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(EstbanCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($yearDir, true);

    $csv = "DATA_BASE;CODMUN_IBGE;UF;VERBETE_1\n".
        "202501;3426704.0;SP;9\n";

    Storage::disk('local')->put($yearDir.'/f.csv', $csv);

    $run = CentralProcessRun::query()->create([
        'type' => 'estban_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $lookup = app(TenantLookupRepositoryInterface::class)->activeEstbanImportMunicipioLookup();
    $summary = app(EstbanCsvImportService::class)->importYearFromManualDirectory(
        $run->id,
        $yearDir,
        2025,
        $lookup,
    );

    expect((int) $summary['inserted'])->toBe(1);

    Storage::disk('local')->deleteDirectory($yearDir);
    EstbanLinha::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});
