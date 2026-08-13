<?php

use App\Enums\CnaeSinonimoStatus;
use App\Enums\EmpresaCatalogItemTipo;
use App\Models\Cnae;
use App\Models\CnaeSinonimo;
use App\Models\Empresa;
use App\Models\EmpresaCatalogItem;
use App\Models\EmpresaOverride;
use App\Models\OrcamentoSolicitacao;
use App\Models\Tenant;
use App\Models\User;
use App\Support\TenantContext;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

function tenantPortalHost(Tenant $tenant): string
{
    return $tenant->slug.'.'.config('app.domain');
}

it('portal mapa retorna 200 com host do tenant e aplica filtro cnae', function () {
    $tenant = Tenant::create([
        'name' => 'T Portal '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-portal-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'latitude' => -22.18535,
        'longitude' => -47.38805,
    ]);

    $codigoCnae = (string) fake()->unique()->numberBetween(1000000, 9999999);

    Cnae::query()->forceCreate([
        'codigo' => $codigoCnae,
        'descricao' => 'CNAE portal',
        'situacao' => true,
        'tenant_id' => null,
    ]);

    $cnpjBasico = str_pad((string) fake()->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);

    Empresa::withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'cnpj' => $cnpjBasico.'000100',
        'cnpj_basico' => $cnpjBasico,
        'cnpj_ordem' => '0001',
        'cnpj_dv' => '00',
        'razao_social' => 'Empresa Portal LTDA',
        'cnae_fiscal_principal' => $codigoCnae,
        'bairro' => 'Centro',
        'situacao_cadastral' => '02',
        'data_situacao_cadastral' => '2024-06-01',
        'latitude' => -22.19,
        'longitude' => -47.39,
    ]);

    $host = tenantPortalHost($tenant);

    $this->get('http://'.$host.'/portal/mapa')
        ->assertOk();

    $resp = $this->get('http://'.$host.'/portal/mapa?aplicar=1&cnaes%5B%5D='.$codigoCnae);
    $resp->assertOk();
    expect($resp->content())->toContain('mapa-portal');

    Empresa::withoutGlobalScopes()->where('tenant_id', $tenant->id)->forceDelete();
    Cnae::query()->where('codigo', $codigoCnae)->delete();
    $tenant->forceDelete();
});

it('portal catalogo filtra por sinonimo de cnae aprovado', function () {
    $tenant = Tenant::create([
        'name' => 'T Cat '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-cat-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'latitude' => -22.18535,
        'longitude' => -47.38805,
    ]);

    $codigoCnae = (string) fake()->unique()->numberBetween(1000000, 9999999);

    $cnae = Cnae::query()->forceCreate([
        'codigo' => $codigoCnae,
        'descricao' => 'Atividade catálogo teste',
        'situacao' => true,
        'tenant_id' => null,
    ]);

    $author = User::factory()->create([
        'user_type' => 'super_admin',
        'tenant_id' => null,
    ]);

    $sinonimoStr = 'catalogo-sin-'.fake()->unique()->numberBetween(10000, 99999);

    CnaeSinonimo::forceCreate([
        'cnae_id' => $cnae->id,
        'sinonimo' => $sinonimoStr,
        'status' => CnaeSinonimoStatus::APROVADO->value,
        'created_by' => $author->id,
        'updated_by' => null,
        'tenant_id' => null,
    ]);

    $cnpjBasico = str_pad((string) fake()->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);

    $empresaCatalogo = Empresa::withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'cnpj' => $cnpjBasico.'000100',
        'cnpj_basico' => $cnpjBasico,
        'cnpj_ordem' => '0001',
        'cnpj_dv' => '00',
        'razao_social' => 'Empresa Catálogo Sinonimo LTDA',
        'cnae_fiscal_principal' => $codigoCnae,
        'bairro' => 'Centro',
        'situacao_cadastral' => '02',
        'data_situacao_cadastral' => '2024-06-01',
        'latitude' => -22.19,
        'longitude' => -47.39,
    ]);

    EmpresaCatalogItem::query()->withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'empresa_id' => $empresaCatalogo->id,
        'tipo' => EmpresaCatalogItemTipo::Servico->value,
        'nome' => 'Serviço teste catálogo',
        'descricao' => null,
        'ativo' => true,
        'aceita_solicitacao_orcamento' => true,
    ]);

    $host = tenantPortalHost($tenant);

    $this->get('http://'.$host.'/portal/catalogo?sinonimo='.urlencode($sinonimoStr))
        ->assertOk()
        ->assertSee('Empresa Catálogo Sinonimo LTDA', false);

    CnaeSinonimo::withTrashed()->where('sinonimo', $sinonimoStr)->forceDelete();
    EmpresaCatalogItem::withoutGlobalScopes()->where('tenant_id', $tenant->id)->forceDelete();
    Empresa::withoutGlobalScopes()->where('tenant_id', $tenant->id)->forceDelete();
    Cnae::query()->where('id', $cnae->id)->delete();
    $author->forceDelete();
    $tenant->forceDelete();
});

it('morador pode registrar e criar solicitacao de orcamento', function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::firstOrCreate(['name' => 'tenant_user', 'guard_name' => 'web']);

    $tenant = Tenant::create([
        'name' => 'T Mor '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 't-mor-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
    ]);

    $host = tenantPortalHost($tenant);

    $cnpjBasico = str_pad((string) fake()->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $cnpj = $cnpjBasico.'000100';

    $empresa = Empresa::withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'cnpj' => $cnpj,
        'cnpj_basico' => $cnpjBasico,
        'cnpj_ordem' => '0001',
        'cnpj_dv' => '00',
        'razao_social' => 'Loja Orc LTDA',
        'nome_fantasia' => 'Loja Orc',
        'cnae_fiscal_principal' => '1091101',
        'situacao_cadastral' => '02',
        'data_situacao_cadastral' => '2024-06-01',
        'latitude' => -22.19,
        'longitude' => -47.39,
    ]);

    EmpresaOverride::withoutGlobalScopes()->forceCreate([
        'tenant_id' => $tenant->id,
        'empresa_id' => $empresa->id,
        'deseja_receber_orcamentos' => true,
    ]);

    $email = fake()->unique()->safeEmail();
    $this->post('http://'.$host.'/tenant/register/morador', [
        'name' => 'Morador Teste',
        'email' => $email,
        'password' => 'Password1!',
        'password_confirmation' => 'Password1!',
        'cpf' => '52998224725',
        'phone' => '19999999999',
        'endereco_completo' => 'Rua A, 1, Centro',
    ])->assertRedirect();

    $user = User::query()->where('email', $email)->first();
    expect($user)->not->toBeNull();

    TenantContext::set($tenant->id);

    $this->actingAs($user)->post('/app/orcamentos/solicitacoes/empresa/'.$empresa->id, [
        'titulo' => 'Orçamento teste',
        'mensagem' => 'Preciso de 10 unidades.',
    ])->assertRedirect(route('app.orcamento-solicitacoes.index'));

    expect(OrcamentoSolicitacao::query()->where('empresa_id', $empresa->id)->count())->toBe(1);

    OrcamentoSolicitacao::withoutGlobalScopes()->where('empresa_id', $empresa->id)->forceDelete();
    EmpresaOverride::withoutGlobalScopes()->where('empresa_id', $empresa->id)->forceDelete();
    Empresa::withoutGlobalScopes()->where('id', $empresa->id)->forceDelete();
    $user->forceDelete();
    TenantContext::clear();
    $tenant->forceDelete();
});
