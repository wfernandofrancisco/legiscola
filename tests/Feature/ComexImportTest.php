<?php

use App\Models\CentralProcessRun;
use App\Models\ComexExportacaoLinha;
use App\Models\ComexImportacaoLinha;
use App\Models\Tenant;
use App\Services\ComexCsvImportService;
use App\Support\ComexMunicipioCodigo;
use Illuminate\Support\Facades\Storage;

it('normaliza codigo municipio comex para 7 digitos', function () {
    expect(ComexMunicipioCodigo::normalize('3550308'))->toBe('3550308');
    expect(ComexMunicipioCodigo::normalize('03550308'))->toBe('3550308');
});

it('importa apenas linhas do ano e municipio mapeado', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Comex '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-comex-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_importacao_exportacao' => '3550308',
    ]);

    $dir = ComexCsvImportService::MANUAL_RELATIVE_ROOT.'/pest-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(ComexCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($dir, true);

    $header = "CO_ANO;CO_MES;SH4;CO_PAIS;SG_UF_MUN;CO_MUN;KG_LIQUIDO;VL_FOB\n";
    $row2025 = "2025;01;1234;160;SP;3550308;100;200\n";
    $row2026 = "2026;01;1234;160;SP;3550308;50;75\n";
    $rowOtherMun = "2026;02;1234;160;SP;3509502;1;2\n";
    Storage::disk('local')->put($dir.'/IMP_2026_MUN.csv', $header.$row2025.$row2026.$rowOtherMun);
    Storage::disk('local')->put($dir.'/EXP_2026_MUN.csv', $header.$row2026);

    $run = CentralProcessRun::query()->create([
        'type' => 'comex_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $map = ['3550308' => (int) $tenant->id];
    $summary = app(ComexCsvImportService::class)->importYearForManualDirectory($run->id, $dir, 2026, $map);

    expect((int) $summary['importacao_rows_inserted'])->toBe(1);
    expect((int) $summary['exportacao_rows_inserted'])->toBe(1);
    expect(ComexImportacaoLinha::query()->where('tenant_id', $tenant->id)->where('co_ano', 2026)->count())->toBe(1);
    expect(ComexExportacaoLinha::query()->where('tenant_id', $tenant->id)->where('co_ano', 2026)->count())->toBe(1);

    Storage::disk('local')->deleteDirectory($dir);
    ComexImportacaoLinha::query()->where('tenant_id', $tenant->id)->delete();
    ComexExportacaoLinha::query()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});

it('aceita nomes legados importacao.csv e exportacao.csv', function () {
    $tenant = Tenant::create([
        'name' => 'Tenant Comex legado '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-comex-leg-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_importacao_exportacao' => '3550308',
    ]);

    $dir = ComexCsvImportService::MANUAL_RELATIVE_ROOT.'/pest-leg-'.uniqid('', true);
    Storage::disk('local')->makeDirectory(ComexCsvImportService::MANUAL_RELATIVE_ROOT, true);
    Storage::disk('local')->makeDirectory($dir, true);

    $header = "CO_ANO;CO_MES;SH4;CO_PAIS;SG_UF_MUN;CO_MUN;KG_LIQUIDO;VL_FOB\n";
    Storage::disk('local')->put($dir.'/importacao.csv', $header."2026;01;1234;160;SP;3550308;1;2\n");
    Storage::disk('local')->put($dir.'/exportacao.csv', $header."2026;01;1234;160;SP;3550308;3;4\n");

    $run = CentralProcessRun::query()->create([
        'type' => 'comex_import',
        'status' => CentralProcessRun::STATUS_QUEUED,
        'requested_by' => null,
        'meta' => [],
    ]);

    $map = ['3550308' => (int) $tenant->id];
    $summary = app(ComexCsvImportService::class)->importYearForManualDirectory($run->id, $dir, 2026, $map);

    expect((int) $summary['importacao_rows_inserted'])->toBe(1);
    expect((int) $summary['exportacao_rows_inserted'])->toBe(1);

    Storage::disk('local')->deleteDirectory($dir);
    ComexImportacaoLinha::query()->where('tenant_id', $tenant->id)->delete();
    ComexExportacaoLinha::query()->where('tenant_id', $tenant->id)->delete();
    $run->delete();
    $tenant->forceDelete();
});
