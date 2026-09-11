<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\CatalogPromo;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CatalogPromoService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

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
        ->assertViewHas('avisosRegionais', fn ($avisos) => $avisos->count() === 1)
        ->assertSee('Curso X com 20% de desconto', false)
        ->assertSee('Ver curso e aulas', false);
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
        ->assertSee('Vídeo', false);

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
