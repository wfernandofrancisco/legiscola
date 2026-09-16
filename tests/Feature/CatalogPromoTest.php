<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\CatalogPromo;
use App\Models\CatalogPromoContact;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CatalogPromoService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

function promoTenant(string $slug, string $uf = 'SP'): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'nome_fantasia' => 'Câmara '.$slug,
        'slug' => $slug.'-'.uniqid(),
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => $uf,
    ]);
}

function promoDiretor(array $ufs = ['SP']): User
{
    $diretor = User::create([
        'tenant_id' => null,
        'name' => 'Diretor Promo',
        'email' => 'diretor-promo-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_DIRECTOR,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $diretor->assignRole(User::TYPE_TENANT_DIRECTOR);

    foreach ($ufs as $uf) {
        DirectorUf::create(['user_id' => $diretor->id, 'uf' => $uf]);
    }

    return $diretor;
}

function promoAdmin(Tenant $tenant): User
{
    $admin = User::create([
        'tenant_id' => $tenant->id,
        'name' => 'Admin Promo',
        'email' => 'admin-promo-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_ADMIN,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);
    $admin->assignRole('tenant_admin');

    return $admin;
}

function promoItem(User $diretor): CatalogItem
{
    $item = CatalogItem::create([
        'owner_user_id' => $diretor->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Curso com desconto',
        'resumo' => 'Resumo promo',
        'descricao' => 'Descrição completa',
        'workload_hours' => 8,
        'status' => CatalogItemStatus::Publicado,
        'preco_sugerido' => 500,
    ]);

    CatalogLesson::create([
        'catalog_item_id' => $item->id,
        'ordem' => 1,
        'titulo' => 'Aula 1 — Introdução',
        'video_url' => 'https://www.youtube.com/watch?v=abc',
    ]);

    return $item;
}

it('diretor publica aviso geral e admin da UF ativa vê no dashboard', function () {
    $tenant = promoTenant('promo-sp', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    $this->actingAs($diretor)
        ->post(route('diretor.promos.store'), [
            'catalog_item_id' => $item->id,
            'titulo' => 'Curso X com 20% de desconto',
            'mensagem' => 'Aproveite até o fim do mês',
            'desconto_percentual' => 20,
            'preco_de' => 500,
            'preco_por' => 400,
            'alcance' => 'geral',
            'ativo' => 1,
        ])
        ->assertRedirect(route('diretor.promos.index'));

    expect(CatalogPromo::query()->count())->toBe(1);

    \App\Support\TenantContext::set((int) $tenant->id);
    expect(app(CatalogPromoService::class)->forAdminDashboard($admin))->toHaveCount(1);
    expect(CatalogPromo::query()->currentlyActive()->forTenant((int) $tenant->id)->count())->toBe(1);
    \App\Support\TenantContext::clear();

    $host = $tenant->slug.'.'.config('app.domain');

    // Limpa flash do diretor para não dar falso positivo no assertSee.
    session()->forget(['success', 'error', 'info']);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Curso X com 20% de desconto', false)
        ->assertSee('Comunicado regional', false)
        ->assertSee('Ver curso e aulas', false)
        ->assertSee('Cursos externos disponíveis', false)
        ->assertDontSee('Próximo aviso', false);
});

it('diretor anexa capa no aviso e o admin vê no popup', function () {
    Storage::fake('public');

    $tenant = promoTenant('promo-capa', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);
    $capa = UploadedFile::fake()->image('aviso.jpg', 800, 400);

    $this->actingAs($diretor)
        ->post(route('diretor.promos.store'), [
            'catalog_item_id' => $item->id,
            'titulo' => 'Aviso com capa',
            'mensagem' => "Primeira linha\n\nSegunda linha do comunicado.",
            'alcance' => 'geral',
            'ativo' => 1,
            'capa' => $capa,
        ])
        ->assertRedirect(route('diretor.promos.index'));

    $promo = CatalogPromo::query()->first();
    expect($promo?->capa_path)->not->toBeNull();
    Storage::disk('public')->assertExists($promo->capa_path);

    $host = $tenant->slug.'.'.config('app.domain');
    session()->forget(['success', 'error', 'info']);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Aviso com capa', false)
        ->assertSee('Primeira linha', false)
        ->assertSee('Segunda linha do comunicado.', false)
        ->assertSee(Storage::disk('public')->url($promo->capa_path), false)
        ->assertSee('role="dialog"', false);
});

it('aviso específico não aparece para câmara não selecionada', function () {
    $spA = promoTenant('promo-a', 'SP');
    $spB = promoTenant('promo-b', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $adminB = promoAdmin($spB);

    $this->actingAs($diretor);

    app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Só para a câmara A',
        'alcance' => 'especifico',
        'tenant_ids' => [$spA->id],
        'ativo' => true,
    ]);

    $host = $spB->slug.'.'.config('app.domain');

    $this->actingAs($adminB)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertDontSee('Só para a câmara A', false);
});

it('fechar o aviso tira do dashboard e não volta no dia seguinte', function () {
    $tenant = promoTenant('promo-dismiss', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    $promo = app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Oferta relâmpago',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->post('http://'.$host.'/admin/avisos-regionais/'.$promo->id.'/fechar')
        ->assertRedirect();

    $this->travel(2)->days();

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertDontSee('Oferta relâmpago', false);
});

it('editar o aviso faz reaparecer para quem já tinha fechado', function () {
    $tenant = promoTenant('promo-reedit', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    $promo = app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Oferta antiga',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    app(CatalogPromoService::class)->dismiss($promo, $admin);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertDontSee('Oferta antiga', false);

    $this->travel(1)->seconds();

    app(CatalogPromoService::class)->update($promo, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Oferta renovada com mais desconto',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Oferta renovada com mais desconto', false);
});

it('admin abre o aviso e vê as aulas do curso', function () {
    $tenant = promoTenant('promo-show', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    $promo = app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Veja o conteúdo',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin/avisos-regionais/'.$promo->id)
        ->assertOk()
        ->assertSee('Aula 1 — Introdução', false)
        ->assertSee('Vídeo', false)
        ->assertSee('Falar com diretor regional', false);

    // Abrir já fecha o banner no dashboard.
    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertDontSee('Veja o conteúdo', false);
});

it('tenant inativo não recebe aviso geral', function () {
    $tenant = promoTenant('promo-inativo', 'SP');
    $tenant->forceFill(['status' => 'inativo'])->save();

    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Não deve aparecer',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    expect(app(CatalogPromoService::class)->forAdminDashboard($admin))->toBeEmpty();
});

it('vários avisos abrem em modal com navegação lateral', function () {
    $tenant = promoTenant('promo-carrossel', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Primeiro comunicado',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Segundo comunicado',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Primeiro comunicado', false)
        ->assertSee('Segundo comunicado', false)
        ->assertSee('Próximo aviso', false)
        ->assertSee('Aviso anterior', false)
        ->assertSee('role="dialog"', false);
});

it('câmara com cadastro pendente ainda vê aviso geral no admin', function () {
    $tenant = promoTenant('promo-pendente', 'SP');
    $tenant->forceFill(['cadastro_status' => Tenant::CADASTRO_PENDENTE])->save();

    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);

    app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Aviso para câmara pendente',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->get('http://'.$host.'/admin')
        ->assertOk()
        ->assertSee('Aviso para câmara pendente', false)
        ->assertSee('Ver curso e aulas', false);
});

it('admin envia contato do aviso e o diretor vê na lista', function () {
    $tenant = promoTenant('promo-contato', 'SP');
    $diretor = promoDiretor(['SP']);
    $item = promoItem($diretor);
    $admin = promoAdmin($tenant);
    $admin->forceFill(['phone' => '19988887777'])->save();

    $promo = app(CatalogPromoService::class)->create($diretor, [
        'catalog_item_id' => $item->id,
        'titulo' => 'Curso para contato',
        'alcance' => 'geral',
        'ativo' => true,
    ]);

    $host = $tenant->slug.'.'.config('app.domain');

    $this->actingAs($admin)
        ->from('http://'.$host.'/admin/avisos-regionais/'.$promo->id)
        ->post('http://'.$host.'/admin/avisos-regionais/'.$promo->id.'/contato', [
            'nome' => 'Ana da Câmara',
            'whatsapp' => '(19) 98888-7777',
            'interesse' => 'Quero abrir uma turma em outubro.',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $contato = CatalogPromoContact::query()->first();
    expect($contato)->not->toBeNull()
        ->and($contato->nome)->toBe('Ana da Câmara')
        ->and($contato->whatsapp)->toBe('19988887777')
        ->and($contato->interesse)->toBe('Quero abrir uma turma em outubro.')
        ->and((int) $contato->director_user_id)->toBe((int) $diretor->id);

    $this->actingAs($diretor)
        ->get(route('diretor.promos.index'))
        ->assertOk()
        ->assertSee('Contatos (1)', false);

    $this->actingAs($diretor)
        ->get(route('diretor.promos.contatos', $promo))
        ->assertOk()
        ->assertSee('Ana da Câmara', false)
        ->assertSee('Quero abrir uma turma em outubro.', false)
        ->assertSee('19988887777', false);

    $outro = promoDiretor(['SP']);
    $this->actingAs($outro)
        ->get(route('diretor.promos.contatos', $promo))
        ->assertForbidden();
});
