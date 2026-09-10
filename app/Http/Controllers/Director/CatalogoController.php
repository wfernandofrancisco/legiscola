<?php

namespace App\Http\Controllers\Director;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemTipo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Director\StoreCatalogItemRequest;
use App\Models\CatalogItem;
use App\Services\CatalogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogoController extends Controller
{
    public function __construct(private CatalogService $catalog) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CatalogItem::class);

        $itens = CatalogItem::query()
            ->where('owner_user_id', $request->user()->id)
            ->withCount(['lessons', 'licenses'])
            ->when($request->filled('tipo'), fn ($q) => $q->where('tipo', $request->string('tipo')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('titulo', 'like', '%'.$request->string('q').'%'))
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('director.catalogo.index', [
            'itens' => $itens,
            'tipos' => CatalogItemTipo::options(),
            'statuses' => CatalogItemStatus::options(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CatalogItem::class);

        return view('director.catalogo.create');
    }

    public function store(StoreCatalogItemRequest $request): RedirectResponse
    {
        $this->authorize('create', CatalogItem::class);

        $item = $this->catalog->createItem($request->user(), $request->validated() + [
            'capa' => $request->file('capa'),
        ]);

        return redirect()
            ->route('diretor.catalogo.show', $item)
            ->with('success', 'Item criado. Agora monte as aulas.');
    }

    public function show(CatalogItem $catalogo): View
    {
        $this->authorize('view', $catalogo);

        $catalogo->load(['lessons', 'licenses.tenant']);

        return view('director.catalogo.show', ['item' => $catalogo]);
    }

    public function edit(CatalogItem $catalogo): View
    {
        $this->authorize('update', $catalogo);

        return view('director.catalogo.edit', ['item' => $catalogo]);
    }

    public function update(StoreCatalogItemRequest $request, CatalogItem $catalogo): RedirectResponse
    {
        $this->authorize('update', $catalogo);

        $this->catalog->updateItem($catalogo, $request->validated() + [
            'capa' => $request->file('capa'),
        ]);

        return redirect()
            ->route('diretor.catalogo.show', $catalogo)
            ->with('success', 'Item atualizado.');
    }

    public function destroy(CatalogItem $catalogo): RedirectResponse
    {
        $this->authorize('delete', $catalogo);

        if ($catalogo->licenses()->exists()) {
            return back()->with('error', 'Este item já foi liberado para alguma câmara e não pode ser removido. Arquive-o.');
        }

        $titulo = $catalogo->titulo;

        $this->catalog->deleteItem($catalogo);

        return redirect()
            ->route('diretor.catalogo.index')
            ->with('success', "\"{$titulo}\" removido do catálogo.");
    }
}
