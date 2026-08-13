<?php

use App\Http\Middleware\EnsureCentralAccess;
use App\Http\Middleware\EnsureTenantAccess;
use App\Models\CagedImportBatch;
use App\Models\CagedMovimentacao;
use App\Models\Tenant;
use App\Services\CagedMicrodataImportService;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCentralAccess::class,
        EnsureTenantAccess::class,
        EnsureEmailIsVerified::class,
    ]);

});

function mkCagedTenant(array $overrides = []): Tenant
{
    return Tenant::create(array_merge([
        'name' => 'Tenant Caged '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-caged-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'codigo_municipio_caged' => '3509502',
    ], $overrides));
}

function fakeCagedLine28(string $municipioIbge, string $sexo = '1'): string
{
    $line = '202401;3;35;MUN;G;4742300;-1;521110;101;7;50;44,00;3;SEXO;0;1;31;0;0;0;2500,50;1;0;1;202401;0;5;2500,50';

    return str_replace(['MUN', 'SEXO'], [$municipioIbge, $sexo], $line)."\n";
}

function fakeCagedLine30(string $municipioIbge): string
{
    $line = '202401;2;23;MUN;L;6810203;-1;411010;103;7;22;21,00;3;1;0;1;40;0;0;1;0,00;2;1;1;202401;202401;1;0;5;0,00';

    return str_replace('MUN', $municipioIbge, $line)."\n";
}

it('importa apenas linhas do municipio do tenant', function () {
    $tenant = mkCagedTenant();
    $content = fakeCagedLine28('3509502').fakeCagedLine28('4303100');

    $relativePath = 'caged/test-'.uniqid('', true).'.txt';
    Storage::disk('local')->put($relativePath, $content);

    $batch = CagedImportBatch::query()->create([
        'tenant_id' => $tenant->id,
        'original_filename' => 'CAGEDMOV202401.txt',
        'storage_path' => $relativePath,
        'arquivo_tipo' => 'mov',
        'competencia_declarada' => '202401',
        'status' => 'processing',
        'linhas_lidas' => 0,
        'linhas_gravadas' => 0,
    ]);

    app(CagedMicrodataImportService::class)->processBatchFile($batch->fresh(), $tenant);

    expect(CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(1);
    $mov = CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    expect($mov->municipio_codigo_ibge)->toBe('3509502');
    expect($mov->cnpj_raiz)->toBeNull();
    expect((float) $mov->salario_movimentacao)->toBe(2500.5);
    expect($batch->fresh()->status)->toBe('completed');
    expect($batch->fresh()->linhas_gravadas)->toBe(1);

    Storage::disk('local')->delete($relativePath);
    $batch->delete();
});

it('servico normaliza codigo ibge do tenant com mascara', function () {
    $tenant = mkCagedTenant(['codigo_municipio_caged' => '3509502']);
    $svc = app(CagedMicrodataImportService::class);
    expect($svc->normalizeIbgeMunicipio($tenant->codigo_municipio_caged))->toBe('3509502');
});

it('aceita codigo de municipio com 6 digitos como no arquivo Caged', function () {
    $tenant = mkCagedTenant(['codigo_municipio_caged' => '352670']);
    $svc = app(CagedMicrodataImportService::class);
    expect($svc->normalizeIbgeMunicipio($tenant->codigo_municipio_caged))->toBe('352670');

    $content = fakeCagedLine28('352670').fakeCagedLine28('3509502');
    $relativePath = 'caged/test-6dig-'.uniqid('', true).'.txt';
    Storage::disk('local')->put($relativePath, $content);

    $batch = CagedImportBatch::query()->create([
        'tenant_id' => $tenant->id,
        'original_filename' => 'CAGEDMOV202401.txt',
        'storage_path' => $relativePath,
        'arquivo_tipo' => 'mov',
        'competencia_declarada' => '202401',
        'status' => 'processing',
        'linhas_lidas' => 0,
        'linhas_gravadas' => 0,
    ]);

    app(CagedMicrodataImportService::class)->processBatchFile($batch->fresh(), $tenant);

    expect(CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(1);
    expect(CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->value('municipio_codigo_ibge'))->toBe('352670');

    Storage::disk('local')->delete($relativePath);
    $batch->delete();
});

it('importa arquivo com 30 colunas e mantém chaves canônicas no json', function () {
    $tenant = mkCagedTenant(['codigo_municipio_caged' => '352670']);
    $content = fakeCagedLine30('352670');
    $relativePath = 'caged/test-30-'.uniqid('', true).'.txt';
    Storage::disk('local')->put($relativePath, $content);

    $batch = CagedImportBatch::query()->create([
        'tenant_id' => $tenant->id,
        'original_filename' => 'CAGEDMOV202401.txt',
        'storage_path' => $relativePath,
        'arquivo_tipo' => 'mov',
        'competencia_declarada' => '202401',
        'status' => 'processing',
        'linhas_lidas' => 0,
        'linhas_gravadas' => 0,
    ]);

    app(CagedMicrodataImportService::class)->processBatchFile($batch->fresh(), $tenant);

    $mov = CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->first();
    expect($mov)->not->toBeNull();
    $d = $mov->dados;
    expect($d['sexo'] ?? null)->toBe('1');
    expect($d['racacor'] ?? null)->toBe('3');
    expect($d['competencia_exc'] ?? null)->toBe('202401');
    expect($d['indicadordeforadoprazo'] ?? null)->toBe('0');

    Storage::disk('local')->delete($relativePath);
    $batch->delete();
    CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->delete();
});

it('descarta a primeira linha quando for cabeçalho de colunas', function () {
    $tenant = mkCagedTenant();
    $header = "competênciamov;região;uf;município;seção;subclasse;saldomovimentação;cbo2002ocupação\n";
    $content = $header.fakeCagedLine28('3509502');
    $relativePath = 'caged/test-hdr-'.uniqid('', true).'.txt';
    Storage::disk('local')->put($relativePath, $content);

    $batch = CagedImportBatch::query()->create([
        'tenant_id' => $tenant->id,
        'original_filename' => 'CAGEDMOV202401.txt',
        'storage_path' => $relativePath,
        'arquivo_tipo' => 'mov',
        'competencia_declarada' => '202401',
        'status' => 'processing',
        'linhas_lidas' => 0,
        'linhas_gravadas' => 0,
    ]);

    app(CagedMicrodataImportService::class)->processBatchFile($batch->fresh(), $tenant);

    expect(CagedMovimentacao::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count())->toBe(1);
    expect($batch->fresh()->linhas_lidas)->toBe(1);

    Storage::disk('local')->delete($relativePath);
    $batch->delete();
});
