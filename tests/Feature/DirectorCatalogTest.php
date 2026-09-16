<?php

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Enums\CatalogVideoProvider;
use App\Models\CatalogItem;
use App\Models\CatalogLesson;
use App\Models\DirectorUf;
use App\Models\Tenant;
use App\Models\User;
use App\Support\VideoEmbed;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

function catalogTenant(string $slug, string $uf): Tenant
{
    return Tenant::create([
        'name' => 'Câmara '.$slug,
        'slug' => $slug,
        'domain' => fake()->unique()->domainName(),
        'status' => Tenant::STATUS_ATIVO,
        'cadastro_status' => Tenant::CADASTRO_ATIVO,
        'estado' => $uf,
    ]);
}

/**
 * @param  list<string>  $ufs
 */
function catalogDirector(array $ufs): User
{
    $director = User::create([
        'tenant_id' => null,
        'name' => 'Diretor '.implode('', $ufs),
        'email' => 'diretor-'.fake()->unique()->safeEmail(),
        'password' => Hash::make('password'),
        'user_type' => User::TYPE_TENANT_DIRECTOR,
        'status' => User::STATUS_ATIVO,
        'email_verified_at' => now(),
    ]);

    $director->assignRole(User::TYPE_TENANT_DIRECTOR);

    foreach ($ufs as $uf) {
        DirectorUf::create(['user_id' => $director->id, 'uf' => $uf]);
    }

    return $director;
}

function catalogItemFor(User $director, array $overrides = []): CatalogItem
{
    return CatalogItem::create(array_merge([
        'owner_user_id' => $director->id,
        'tipo' => CatalogItemTipo::Curso,
        'titulo' => 'Processo Legislativo na Prática',
        'resumo' => 'Curso base do catálogo',
        'status' => CatalogItemStatus::Publicado,
        'workload_hours' => 20,
    ], $overrides));
}

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
    catalogTenant('araras-sp', 'SP');
    catalogTenant('uberaba-mg', 'MG');
});

it('cria um item no catálogo do diretor', function () {
    $director = catalogDirector(['SP']);

    $this->actingAs($director)
        ->post(route('diretor.catalogo.store'), [
            'tipo' => 'curso',
            'titulo' => 'Redação Legislativa',
            'resumo' => 'Técnicas de redação para o legislativo',
            'status' => 'publicado',
            'workload_hours' => 12,
        ])
        ->assertRedirect();

    $item = CatalogItem::first();

    expect($item->titulo)->toBe('Redação Legislativa')
        ->and($item->owner_user_id)->toBe($director->id)
        ->and($item->tipo)->toBe(CatalogItemTipo::Curso)
        ->and($item->status)->toBe(CatalogItemStatus::Publicado);
});

it('mostra no catálogo apenas os itens do próprio diretor', function () {
    $diretorSp = catalogDirector(['SP']);
    $diretorMg = catalogDirector(['MG']);

    catalogItemFor($diretorSp, ['titulo' => 'Curso do SP']);
    catalogItemFor($diretorMg, ['titulo' => 'Curso do MG']);

    $this->actingAs($diretorSp)
        ->get(route('diretor.catalogo.index'))
        ->assertOk()
        ->assertSee('Curso do SP')
        ->assertDontSee('Curso do MG');
});

it('impede um diretor de abrir item de catálogo de outro', function () {
    $diretorSp = catalogDirector(['SP']);
    $diretorMg = catalogDirector(['MG']);

    $itemDoMg = catalogItemFor($diretorMg);

    $this->actingAs($diretorSp)
        ->get(route('diretor.catalogo.show', $itemDoMg))
        ->assertForbidden();
});

it('impede um diretor de editar item de catálogo de outro', function () {
    $diretorSp = catalogDirector(['SP']);
    $diretorMg = catalogDirector(['MG']);

    $itemDoMg = catalogItemFor($diretorMg);

    $this->actingAs($diretorSp)
        ->put(route('diretor.catalogo.update', $itemDoMg), [
            'tipo' => 'curso',
            'titulo' => 'Sequestrado',
            'status' => 'publicado',
        ])
        ->assertForbidden();

    expect($itemDoMg->fresh()->titulo)->not->toBe('Sequestrado');
});

it('detecta o provedor do vídeo pelo link da aula', function () {
    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    $this->actingAs($director)
        ->post(route('diretor.catalogo.aulas.store', $item), [
            'titulo' => 'Aula 1 — Abertura',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
        ])
        ->assertRedirect();

    $lesson = CatalogLesson::first();

    expect($lesson->video_provider)->toBe(CatalogVideoProvider::Youtube)
        ->and($lesson->videoEmbedUrl())->toBe('https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ')
        ->and($lesson->ordem)->toBe(1);
});

it('aceita upload de vídeo MP4 na aula', function () {
    Storage::fake('public');

    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);
    $arquivo = UploadedFile::fake()->create('aula-1.mp4', 2048, 'video/mp4');

    $this->actingAs($director)
        ->post(route('diretor.catalogo.aulas.store', $item), [
            'titulo' => 'Aula com arquivo',
            'video_file' => $arquivo,
        ])
        ->assertRedirect();

    $lesson = CatalogLesson::first();

    expect($lesson->video_provider)->toBe(CatalogVideoProvider::Upload)
        ->and($lesson->isUploadedVideo())->toBeTrue()
        ->and($lesson->video_path)->not->toBeNull()
        ->and($lesson->videoEmbedUrl())->toBeNull()
        ->and($lesson->effectiveVideoUrl())->toContain('/storage/');

    Storage::disk('public')->assertExists($lesson->video_path);
});

it('mostra a barra de progresso na tela de aulas do catálogo', function () {
    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    $this->actingAs($director)
        ->get(route('diretor.catalogo.show', $item))
        ->assertOk()
        ->assertSee('js-ajax-upload-form', false)
        ->assertSee('Enviando arquivo', false);
});

it('devolve json de redirect no upload ajax da aula', function () {
    Storage::fake('public');

    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    $this->actingAs($director)
        ->post(route('diretor.catalogo.aulas.store', $item), [
            'titulo' => 'Aula com progresso',
            'video_file' => UploadedFile::fake()->create('aula-progresso.mp4', 1024, 'video/mp4'),
        ], [
            'Accept' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest',
        ])
        ->assertOk()
        ->assertJsonPath('redirect', route('diretor.catalogo.show', $item))
        ->assertJsonPath('message', 'Aula adicionada.');

    expect(CatalogLesson::query()->where('titulo', 'Aula com progresso')->exists())->toBeTrue();
});

it('arquivo enviado tem prioridade sobre o link na reprodução', function () {
    Storage::fake('public');

    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    $this->actingAs($director)
        ->post(route('diretor.catalogo.aulas.store', $item), [
            'titulo' => 'Híbrida',
            'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_file' => UploadedFile::fake()->create('prioridade.mp4', 1024, 'video/mp4'),
        ])
        ->assertRedirect();

    $lesson = CatalogLesson::first();

    expect($lesson->video_provider)->toBe(CatalogVideoProvider::Upload)
        ->and($lesson->video_url)->toContain('youtube')
        ->and($lesson->isUploadedVideo())->toBeTrue()
        ->and($lesson->videoEmbedUrl())->toBeNull();
});

it('reconhece link do Vimeo', function () {
    expect(VideoEmbed::detectProvider('https://vimeo.com/76979871'))->toBe(CatalogVideoProvider::Vimeo)
        ->and(VideoEmbed::embedUrl('https://vimeo.com/76979871'))->toBe('https://player.vimeo.com/video/76979871');
});

it('trata link desconhecido como externo e não embedável', function () {
    expect(VideoEmbed::detectProvider('https://exemplo.com/aula.mp4'))->toBe(CatalogVideoProvider::Externo)
        ->and(VideoEmbed::isEmbeddable('https://exemplo.com/aula.mp4'))->toBeFalse();
});

it('numera as aulas na ordem em que são adicionadas', function () {
    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    foreach (['Abertura', 'Desenvolvimento', 'Encerramento'] as $titulo) {
        $this->actingAs($director)
            ->post(route('diretor.catalogo.aulas.store', $item), ['titulo' => $titulo]);
    }

    expect($item->lessons()->pluck('ordem')->all())->toBe([1, 2, 3])
        ->and($item->lessons()->pluck('titulo')->all())->toBe(['Abertura', 'Desenvolvimento', 'Encerramento']);
});

it('impede adicionar aula em item de outro diretor', function () {
    $diretorSp = catalogDirector(['SP']);
    $diretorMg = catalogDirector(['MG']);

    $itemDoMg = catalogItemFor($diretorMg);

    $this->actingAs($diretorSp)
        ->post(route('diretor.catalogo.aulas.store', $itemDoMg), ['titulo' => 'Invasora'])
        ->assertForbidden();

    expect(CatalogLesson::count())->toBe(0);
});

it('reordena as aulas do item', function () {
    $director = catalogDirector(['SP']);
    $item = catalogItemFor($director);

    $a = CatalogLesson::create(['catalog_item_id' => $item->id, 'titulo' => 'A', 'ordem' => 1]);
    $b = CatalogLesson::create(['catalog_item_id' => $item->id, 'titulo' => 'B', 'ordem' => 2]);
    $c = CatalogLesson::create(['catalog_item_id' => $item->id, 'titulo' => 'C', 'ordem' => 3]);

    $this->actingAs($director)
        ->post(route('diretor.catalogo.aulas.reorder', $item), ['ordem' => [$c->id, $a->id, $b->id]]);

    expect($item->lessons()->pluck('titulo')->all())->toBe(['C', 'A', 'B']);
});
