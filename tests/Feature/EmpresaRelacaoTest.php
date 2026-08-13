<?php

use App\Enums\EmpresaRelacaoCanal;
use App\Enums\EmpresaRelacaoOrigem;
use App\Enums\EmpresaRelacaoPrioridade;
use App\Enums\EmpresaRelacaoStatus;
use App\Enums\EmpresaRelacaoTipo;
use App\Http\Middleware\ApplyTenantContextFromAuth;
use App\Http\Middleware\EnsureCentralAccess;
use App\Http\Middleware\EnsureHasTenant;
use App\Http\Middleware\EnsureTenantAccess;
use App\Http\Middleware\SetTenantContext;
use App\Jobs\SendEmpresaRelacaoAvisoJob;
use App\Models\Empresa;
use App\Models\EmpresaRelacao;
use App\Models\EmpresaRelacaoArquivo;
use App\Models\EmpresaRelacaoComentario;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->withoutMiddleware([
        EnsureCentralAccess::class,
        EnsureTenantAccess::class,
        EnsureHasTenant::class,
        SetTenantContext::class,
        ApplyTenantContextFromAuth::class,
        EnsureEmailIsVerified::class,
    ]);

    Storage::fake('public');
    Bus::fake();
});

function mkRelTenant(array $overrides = []): Tenant
{
    return Tenant::create(array_merge([
        'name' => 'Tenant Rel '.fake()->unique()->numberBetween(1, 99999),
        'slug' => 'tenant-rel-'.fake()->unique()->numberBetween(1, 99999),
        'domain' => fake()->unique()->domainName(),
        'status' => 'ativo',
    ], $overrides));
}

function mkRelUser(?Tenant $tenant = null, array $overrides = []): User
{
    $tenant ??= mkRelTenant();

    return User::create(array_merge([
        'tenant_id' => $tenant->id,
        'name' => 'Tenant Admin '.fake()->unique()->numberBetween(1, 99999),
        'email' => fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ], $overrides));
}

function mkRelEmpresa(Tenant $tenant, array $overrides = []): Empresa
{
    $cnpjBasico = str_pad((string) fake()->unique()->numberBetween(10000000, 99999999), 8, '0', STR_PAD_LEFT);

    return Empresa::query()->forceCreate(array_merge([
        'tenant_id' => $tenant->id,
        'cnpj' => $cnpjBasico.'000100',
        'cnpj_basico' => $cnpjBasico,
        'cnpj_ordem' => '0001',
        'cnpj_dv' => '00',
        'razao_social' => 'Empresa Teste LTDA',
        'nome_fantasia' => 'Empresa Teste',
        'situacao_cadastral' => '02',
        'uf' => 'SP',
    ], $overrides));
}

function mkRelacao(Empresa $empresa, User $creator, array $overrides = []): EmpresaRelacao
{
    return EmpresaRelacao::create(array_merge([
        'empresa_id' => $empresa->id,
        'tenant_id' => $empresa->tenant_id,
        'titulo' => 'Relação base',
        'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
        'status' => EmpresaRelacaoStatus::ABERTO->value,
        'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
        'created_by' => $creator->id,
        'updated_by' => $creator->id,
    ], $overrides));
}

it('cria relacao com sucesso e persiste todos os campos', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $envolvido = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $payload = [
        'titulo' => 'Visita técnica',
        'descricao' => 'Vistoria do alvará',
        'tipo' => EmpresaRelacaoTipo::VISITA->value,
        'status' => EmpresaRelacaoStatus::EM_ANDAMENTO->value,
        'prioridade' => EmpresaRelacaoPrioridade::ALTA->value,
        'data_relacao' => now()->toDateString(),
        'proximo_contato_em' => now()->addWeek()->toDateString(),
        'canal' => EmpresaRelacaoCanal::PRESENCIAL->value,
        'origem' => EmpresaRelacaoOrigem::INTERNO->value,
        'responsavel_nome' => 'Fiscal Joao',
        'protocolo' => 'PROT-001',
        'privado' => '1',
        'envolvidos' => [$envolvido->id],
    ];

    $response = $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.store', $empresa), $payload);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('empresa_relacoes', [
        'empresa_id' => $empresa->id,
        'tenant_id' => $tenant->id,
        'titulo' => 'Visita técnica',
        'descricao' => 'Vistoria do alvará',
        'tipo' => EmpresaRelacaoTipo::VISITA->value,
        'status' => EmpresaRelacaoStatus::EM_ANDAMENTO->value,
        'prioridade' => EmpresaRelacaoPrioridade::ALTA->value,
        'canal' => EmpresaRelacaoCanal::PRESENCIAL->value,
        'origem' => EmpresaRelacaoOrigem::INTERNO->value,
        'responsavel_nome' => 'Fiscal Joao',
        'protocolo' => 'PROT-001',
        'privado' => 1,
        'created_by' => $user->id,
        'updated_by' => $user->id,
    ]);

    $relacao = EmpresaRelacao::where('protocolo', 'PROT-001')->firstOrFail();
    expect($relacao->envolvidos()->pluck('users.id')->all())->toEqual([$envolvido->id]);
});

it('cria relacao com upload de arquivos validos (jpg + pdf)', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $jpg = UploadedFile::fake()->image('foto.jpg', 800, 600)->size(300);
    $pdf = UploadedFile::fake()->create('documento.pdf', 500, 'application/pdf');

    $response = $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'Com anexos',
            'tipo' => EmpresaRelacaoTipo::DOCUMENTACAO->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
            'arquivos' => [$jpg, $pdf],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $relacao = EmpresaRelacao::where('titulo', 'Com anexos')->firstOrFail();

    $arquivos = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao->id)->get();
    expect($arquivos)->toHaveCount(2);

    foreach ($arquivos as $arquivo) {
        expect($arquivo->tenant_id)->toBe($tenant->id);
        expect($arquivo->uploaded_by)->toBe($user->id);
        expect($arquivo->tamanho_bytes)->toBeGreaterThan(0);
        Storage::disk('public')->assertExists($arquivo->arquivo_path);
        expect($arquivo->arquivo_path)->toContain("empresa-relacoes/tenant-{$tenant->id}/{$relacao->id}");
    }

    expect($arquivos->pluck('mime_type')->all())->toEqualCanonicalizing(['image/jpeg', 'application/pdf']);
});

it('falha ao criar relacao sem campos obrigatorios', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.store', $empresa), []);

    $response->assertRedirect(route('admin.empresas.relacoes.index', $empresa));
    $response->assertSessionHasErrors(['titulo', 'tipo', 'status', 'prioridade']);
    expect(EmpresaRelacao::count())->toBe(0);
});

it('falha ao criar relacao com enums invalidos', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'X',
            'tipo' => 'tipo-inexistente',
            'status' => 'status-invalido',
            'prioridade' => 'super-urgente',
            'canal' => 'pombo-correio',
            'origem' => 'lua',
        ]);

    $response->assertSessionHasErrors(['tipo', 'status', 'prioridade', 'canal', 'origem']);
});

it('falha ao criar relacao com arquivo de mime invalido', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $exec = UploadedFile::fake()->create('payload.exe', 100, 'application/octet-stream');

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'X',
            'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
            'arquivos' => [$exec],
        ]);

    $response->assertSessionHasErrors(['arquivos.0']);
    expect(EmpresaRelacao::count())->toBe(0);
});

it('falha ao criar relacao com arquivo acima de 10mb', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $big = UploadedFile::fake()->image('big.jpg')->size(11000); // 11 MB > 10240 KB

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'X',
            'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
            'arquivos' => [$big],
        ]);

    $response->assertSessionHasErrors(['arquivos.0']);
    expect(EmpresaRelacao::count())->toBe(0);
});

it('falha quando envolvido pertence a outro tenant', function () {
    $tenantA = mkRelTenant();
    $tenantB = mkRelTenant();
    $userA = mkRelUser($tenantA);
    $estranho = mkRelUser($tenantB);
    $empresa = mkRelEmpresa($tenantA);

    $response = $this->actingAs($userA)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'X',
            'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
            'envolvidos' => [$estranho->id],
        ]);

    $response->assertSessionHasErrors(['envolvidos.0']);
});

it('dispara aviso quando data_aviso e <= agora', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'Aviso vencido',
            'tipo' => EmpresaRelacaoTipo::PENDENCIA->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::URGENTE->value,
            'data_aviso' => now()->subDay()->toDateString(),
        ])->assertRedirect();

    $relacao = EmpresaRelacao::where('titulo', 'Aviso vencido')->firstOrFail();
    Bus::assertDispatched(SendEmpresaRelacaoAvisoJob::class, function ($job) use ($relacao) {
        $ref = new ReflectionClass($job);
        $prop = $ref->getProperty('relacaoId');
        $prop->setAccessible(true);

        return (int) $prop->getValue($job) === $relacao->id;
    });
});

it('NAO dispara aviso quando data_aviso e futura', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.store', $empresa), [
            'titulo' => 'Aviso futuro',
            'tipo' => EmpresaRelacaoTipo::RETORNO->value,
            'status' => EmpresaRelacaoStatus::ABERTO->value,
            'prioridade' => EmpresaRelacaoPrioridade::BAIXA->value,
            'data_aviso' => now()->addDays(5)->toDateString(),
        ])->assertRedirect();

    Bus::assertNotDispatched(SendEmpresaRelacaoAvisoJob::class);
});

it('adiciona comentario com sucesso', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.comentarios.store', [$empresa, $relacao]), [
            'mensagem' => 'Cliente compareceu na vistoria.',
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('empresa_relacao_comentarios', [
        'empresa_relacao_id' => $relacao->id,
        'tenant_id' => $tenant->id,
        'user_id' => $user->id,
        'mensagem' => 'Cliente compareceu na vistoria.',
    ]);
});

it('falha ao adicionar comentario sem mensagem', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.comentarios.store', [$empresa, $relacao]), [
            'mensagem' => '',
        ]);

    $response->assertSessionHasErrors(['mensagem']);
    expect(EmpresaRelacaoComentario::count())->toBe(0);
});

it('bloqueia comentario em relacao de outro tenant', function () {
    $tenantA = mkRelTenant();
    $tenantB = mkRelTenant();
    $userA = mkRelUser($tenantA);
    $userB = mkRelUser($tenantB);
    $empresaB = mkRelEmpresa($tenantB);
    $relacaoB = mkRelacao($empresaB, $userB);

    $response = $this->actingAs($userA)
        ->post(route('admin.empresas.relacoes.comentarios.store', [$empresaB, $relacaoB]), [
            'mensagem' => 'Tentativa indevida.',
        ]);

    $response->assertForbidden();
    expect(EmpresaRelacaoComentario::count())->toBe(0);
});

it('anexa arquivos em relacao existente com sucesso', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), [
            'arquivos' => [
                UploadedFile::fake()->image('a.png', 200, 200)->size(100),
                UploadedFile::fake()->create('b.pdf', 200, 'application/pdf'),
            ],
        ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $arquivos = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao->id)->get();
    expect($arquivos)->toHaveCount(2);

    foreach ($arquivos as $arquivo) {
        Storage::disk('public')->assertExists($arquivo->arquivo_path);
    }
});

it('falha ao anexar arquivos sem nenhum arquivo', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), []);

    $response->assertSessionHasErrors(['arquivos']);
    expect(EmpresaRelacaoArquivo::count())->toBe(0);
});

it('falha ao anexar arquivo com mime invalido', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.index', $empresa))
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), [
            'arquivos' => [UploadedFile::fake()->create('mal.exe', 50, 'application/octet-stream')],
        ]);

    $response->assertSessionHasErrors(['arquivos.0']);
    expect(EmpresaRelacaoArquivo::count())->toBe(0);
});

it('bloqueia anexar arquivos em relacao de outro tenant', function () {
    $tenantA = mkRelTenant();
    $tenantB = mkRelTenant();
    $userA = mkRelUser($tenantA);
    $userB = mkRelUser($tenantB);
    $empresaB = mkRelEmpresa($tenantB);
    $relacaoB = mkRelacao($empresaB, $userB);

    $response = $this->actingAs($userA)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresaB, $relacaoB]), [
            'arquivos' => [UploadedFile::fake()->image('foto.jpg')->size(50)],
        ]);

    $response->assertForbidden();
    expect(EmpresaRelacaoArquivo::count())->toBe(0);
});

it('apenas o criador da relacao pode excluir', function () {
    $tenant = mkRelTenant();
    $criador = mkRelUser($tenant);
    $outro = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $criador);

    $this->actingAs($outro)
        ->delete(route('admin.empresas.relacoes.destroy', [$empresa, $relacao]))
        ->assertForbidden();

    expect(EmpresaRelacao::find($relacao->id))->not->toBeNull();
});

it('exclui relacao e remove arquivos do storage', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), [
            'arquivos' => [UploadedFile::fake()->image('a.jpg')->size(50)],
        ])->assertRedirect();

    $arquivo = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao->id)->firstOrFail();
    Storage::disk('public')->assertExists($arquivo->arquivo_path);
    $pathSalvo = $arquivo->arquivo_path;

    $this->actingAs($user)
        ->delete(route('admin.empresas.relacoes.destroy', [$empresa, $relacao]))
        ->assertRedirect();

    expect(EmpresaRelacao::find($relacao->id))->toBeNull();
    expect(EmpresaRelacaoArquivo::find($arquivo->id))->toBeNull();
    Storage::disk('public')->assertMissing($pathSalvo);
});

it('exibe a tela de edicao para o criador', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user, ['titulo' => 'Editavel original']);

    $response = $this->actingAs($user)
        ->get(route('admin.empresas.relacoes.edit', [$empresa, $relacao]));

    $response->assertOk();
    $response->assertSee('Editavel original');
    $response->assertSee('Salvar alterações');
});

it('bloqueia tela de edicao quando usuario nao e o criador', function () {
    $tenant = mkRelTenant();
    $criador = mkRelUser($tenant);
    $outro = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $criador);

    $this->actingAs($outro)
        ->get(route('admin.empresas.relacoes.edit', [$empresa, $relacao]))
        ->assertForbidden();
});

it('retorna 404 quando relacao nao pertence a empresa na rota de edit', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresaA = mkRelEmpresa($tenant);
    $empresaB = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresaA, $user);

    $this->actingAs($user)
        ->get(route('admin.empresas.relacoes.edit', [$empresaB, $relacao]))
        ->assertNotFound();
});

it('atualiza relacao com sucesso e sincroniza envolvidos', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $envolvido1 = mkRelUser($tenant);
    $envolvido2 = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user, [
        'titulo' => 'Antes',
        'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
        'status' => EmpresaRelacaoStatus::ABERTO->value,
        'prioridade' => EmpresaRelacaoPrioridade::MEDIA->value,
    ]);
    $relacao->envolvidos()->syncWithPivotValues([$envolvido1->id], ['tenant_id' => $tenant->id]);

    $response = $this->actingAs($user)
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => 'Depois',
            'descricao' => 'Atualizada',
            'tipo' => EmpresaRelacaoTipo::FISCALIZACAO->value,
            'status' => EmpresaRelacaoStatus::CONCLUIDO->value,
            'prioridade' => EmpresaRelacaoPrioridade::URGENTE->value,
            'canal' => EmpresaRelacaoCanal::WHATSAPP->value,
            'origem' => EmpresaRelacaoOrigem::EMPRESA->value,
            'responsavel_nome' => 'Outro responsável',
            'protocolo' => 'PROT-EDIT',
            'data_relacao' => now()->toDateString(),
            'privado' => '1',
            'envolvidos' => [$envolvido2->id],
        ]);

    $response->assertRedirect(route('admin.empresas.relacoes.index', $empresa));
    $response->assertSessionHas('success');

    $relacao->refresh();
    expect($relacao->titulo)->toBe('Depois');
    expect($relacao->descricao)->toBe('Atualizada');
    expect($relacao->tipo)->toBe(EmpresaRelacaoTipo::FISCALIZACAO->value);
    expect($relacao->status)->toBe(EmpresaRelacaoStatus::CONCLUIDO->value);
    expect($relacao->prioridade)->toBe(EmpresaRelacaoPrioridade::URGENTE->value);
    expect($relacao->canal)->toBe(EmpresaRelacaoCanal::WHATSAPP->value);
    expect($relacao->origem)->toBe(EmpresaRelacaoOrigem::EMPRESA->value);
    expect($relacao->protocolo)->toBe('PROT-EDIT');
    expect($relacao->privado)->toBeTrue();
    expect($relacao->updated_by)->toBe($user->id);

    expect($relacao->envolvidos()->pluck('users.id')->all())->toEqual([$envolvido2->id]);
});

it('atualizacao desmarca privado quando nao enviado', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user, ['privado' => true]);

    $this->actingAs($user)
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => $relacao->titulo,
            'tipo' => $relacao->tipo,
            'status' => $relacao->status,
            'prioridade' => $relacao->prioridade,
        ])->assertRedirect();

    expect($relacao->refresh()->privado)->toBeFalse();
});

it('falha ao atualizar com campos obrigatorios vazios', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.edit', [$empresa, $relacao]))
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => '',
            'tipo' => '',
            'status' => '',
            'prioridade' => '',
        ]);

    $response->assertSessionHasErrors(['titulo', 'tipo', 'status', 'prioridade']);
});

it('falha ao atualizar com enums invalidos', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $response = $this->actingAs($user)
        ->from(route('admin.empresas.relacoes.edit', [$empresa, $relacao]))
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => 'X',
            'tipo' => 'inexistente',
            'status' => 'invalido',
            'prioridade' => 'top',
        ]);

    $response->assertSessionHasErrors(['tipo', 'status', 'prioridade']);
});

it('bloqueia atualizacao por usuario que nao criou a relacao', function () {
    $tenant = mkRelTenant();
    $criador = mkRelUser($tenant);
    $outro = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $criador, ['titulo' => 'Original']);

    $this->actingAs($outro)
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => 'Tentativa',
            'tipo' => $relacao->tipo,
            'status' => $relacao->status,
            'prioridade' => $relacao->prioridade,
        ])->assertForbidden();

    expect($relacao->refresh()->titulo)->toBe('Original');
});

it('bloqueia atualizacao com envolvido de outro tenant', function () {
    $tenantA = mkRelTenant();
    $tenantB = mkRelTenant();
    $userA = mkRelUser($tenantA);
    $estranho = mkRelUser($tenantB);
    $empresa = mkRelEmpresa($tenantA);
    $relacao = mkRelacao($empresa, $userA);

    $response = $this->actingAs($userA)
        ->from(route('admin.empresas.relacoes.edit', [$empresa, $relacao]))
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => 'X',
            'tipo' => $relacao->tipo,
            'status' => $relacao->status,
            'prioridade' => $relacao->prioridade,
            'envolvidos' => [$estranho->id],
        ]);

    $response->assertSessionHasErrors(['envolvidos.0']);
});

it('reenvia aviso ao alterar data_aviso para passado em relacao ja avisada', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user, [
        'data_aviso' => now()->subDays(2),
        'aviso_enviado_em' => now()->subDays(2),
    ]);

    Bus::assertNotDispatched(SendEmpresaRelacaoAvisoJob::class);

    $this->actingAs($user)
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => $relacao->titulo,
            'tipo' => $relacao->tipo,
            'status' => $relacao->status,
            'prioridade' => $relacao->prioridade,
            'data_aviso' => now()->subDay()->toDateString(),
        ])->assertRedirect();

    Bus::assertDispatched(SendEmpresaRelacaoAvisoJob::class);
    expect($relacao->refresh()->aviso_enviado_em)->toBeNull();
});

it('NAO redispara aviso quando data_aviso permanece a mesma', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $dataAviso = now()->subDay();
    $relacao = mkRelacao($empresa, $user, [
        'data_aviso' => $dataAviso,
        'aviso_enviado_em' => now()->subHour(),
    ]);

    $this->actingAs($user)
        ->put(route('admin.empresas.relacoes.update', [$empresa, $relacao]), [
            'titulo' => 'Outro título',
            'tipo' => $relacao->tipo,
            'status' => $relacao->status,
            'prioridade' => $relacao->prioridade,
            'data_aviso' => $dataAviso->copy()->toDateString(),
        ])->assertRedirect();

    Bus::assertNotDispatched(SendEmpresaRelacaoAvisoJob::class);
});

it('exclui anexo individual da relacao', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $user);

    $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), [
            'arquivos' => [UploadedFile::fake()->image('foto.jpg')->size(50)],
        ])->assertRedirect();

    $arquivo = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao->id)->firstOrFail();
    Storage::disk('public')->assertExists($arquivo->arquivo_path);
    $pathSalvo = $arquivo->arquivo_path;

    $response = $this->actingAs($user)
        ->delete(route('admin.empresas.relacoes.arquivos.destroy', [$empresa, $relacao, $arquivo]));

    $response->assertRedirect();
    $response->assertSessionHas('success');

    expect(EmpresaRelacaoArquivo::find($arquivo->id))->toBeNull();
    Storage::disk('public')->assertMissing($pathSalvo);
});

it('bloqueia exclusao de anexo por outro usuario', function () {
    $tenant = mkRelTenant();
    $uploader = mkRelUser($tenant);
    $outro = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao = mkRelacao($empresa, $uploader);

    $this->actingAs($uploader)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao]), [
            'arquivos' => [UploadedFile::fake()->image('a.jpg')->size(50)],
        ])->assertRedirect();

    $arquivo = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao->id)->firstOrFail();

    $this->actingAs($outro)
        ->delete(route('admin.empresas.relacoes.arquivos.destroy', [$empresa, $relacao, $arquivo]))
        ->assertForbidden();

    expect(EmpresaRelacaoArquivo::find($arquivo->id))->not->toBeNull();
    Storage::disk('public')->assertExists($arquivo->arquivo_path);
});

it('retorna 404 ao excluir anexo de relacao diferente', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);
    $relacao1 = mkRelacao($empresa, $user);
    $relacao2 = mkRelacao($empresa, $user);

    $this->actingAs($user)
        ->post(route('admin.empresas.relacoes.arquivos.store', [$empresa, $relacao1]), [
            'arquivos' => [UploadedFile::fake()->image('a.jpg')->size(50)],
        ])->assertRedirect();

    $arquivo = EmpresaRelacaoArquivo::where('empresa_relacao_id', $relacao1->id)->firstOrFail();

    $this->actingAs($user)
        ->delete(route('admin.empresas.relacoes.arquivos.destroy', [$empresa, $relacao2, $arquivo]))
        ->assertNotFound();

    expect(EmpresaRelacaoArquivo::find($arquivo->id))->not->toBeNull();
});

it('index renderiza com filtros aplicados', function () {
    $tenant = mkRelTenant();
    $user = mkRelUser($tenant);
    $empresa = mkRelEmpresa($tenant);

    mkRelacao($empresa, $user, [
        'titulo' => 'Visita Centro',
        'tipo' => EmpresaRelacaoTipo::VISITA->value,
        'status' => EmpresaRelacaoStatus::EM_ANDAMENTO->value,
    ]);
    mkRelacao($empresa, $user, [
        'titulo' => 'Atendimento Bairro',
        'tipo' => EmpresaRelacaoTipo::ATENDIMENTO->value,
        'status' => EmpresaRelacaoStatus::CONCLUIDO->value,
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.empresas.relacoes.index', [
            $empresa,
            'relacao_tipo' => EmpresaRelacaoTipo::VISITA->value,
        ]));

    $response->assertOk();
    $response->assertSee('Visita Centro');
    $response->assertDontSee('Atendimento Bairro');
});
