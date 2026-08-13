<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\NoticiaServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrUpdateNoticiaRequest;
use App\Models\Noticia;
use App\Models\NoticiaFoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoticiaController extends Controller
{
    public function __construct(
        protected NoticiaServiceInterface $service,
    ) {
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Noticia::class);

        $noticias = $this->service->paginateForAdmin(15, $request->only(['search', 'ativo']));

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Noticias'],
        ];

        return view('admin.noticias.index', compact('noticias', 'breadcrumbs'));
    }

    public function create()
    {
        $this->authorize('create', Noticia::class);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Noticias', 'href' => route('admin.noticias.index')],
            ['label' => 'Nova noticia'],
        ];

        return view('admin.noticias.create', compact('breadcrumbs'));
    }

    public function store(StoreOrUpdateNoticiaRequest $request)
    {
        $this->authorize('create', Noticia::class);

        $data = $request->safe()->except(['fotos', 'legendas', 'delete_fotos', 'slug']);
        $fotos = $request->file('fotos', []);

        if ($request->hasFile('foto_capa')) {
            $data['foto_capa'] = $this->service->storeCapa($request->file('foto_capa'));
        }

        $this->service->create($data, $fotos);

        return redirect()
            ->route('admin.noticias.index')
            ->with('success', 'Noticia criada com sucesso.');
    }

    public function show(Noticia $noticia)
    {
        $this->authorize('view', $noticia);
        $noticia->load(['user', 'fotos']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Noticias', 'href' => route('admin.noticias.index')],
            ['label' => $noticia->titulo],
        ];

        return view('admin.noticias.show', compact('noticia', 'breadcrumbs'));
    }

    public function edit(Noticia $noticia)
    {
        $this->authorize('update', $noticia);
        $noticia->load('fotos');

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Noticias', 'href' => route('admin.noticias.index')],
            ['label' => 'Editar noticia'],
        ];

        return view('admin.noticias.edit', compact('noticia', 'breadcrumbs'));
    }

    public function update(StoreOrUpdateNoticiaRequest $request, Noticia $noticia)
    {
        $this->authorize('update', $noticia);

        $data = $request->safe()->except(['fotos', 'legendas', 'delete_fotos', 'slug']);
        $fotos = $request->file('fotos', []);

        if ($request->hasFile('foto_capa')) {
            if ($noticia->foto_capa) {
                Storage::disk('public')->delete($noticia->foto_capa);
            }
            $data['foto_capa'] = $this->service->storeCapa($request->file('foto_capa'));
        }

        $this->service->update($noticia, $data, $fotos);

        $deleteFotos = collect($request->input('delete_fotos', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($deleteFotos->isNotEmpty()) {
            $noticia->load('fotos');

            foreach ($noticia->fotos as $foto) {
                if ($deleteFotos->contains((int) $foto->id)) {
                    $this->service->deleteFoto($noticia, $foto);
                }
            }
        }

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('success', 'Noticia atualizada com sucesso.');
    }

    public function destroy(Noticia $noticia)
    {
        $this->authorize('delete', $noticia);
        $this->service->delete($noticia);

        return redirect()
            ->route('admin.noticias.index')
            ->with('success', 'Noticia removida com sucesso.');
    }

    public function destroyFoto(Noticia $noticia, NoticiaFoto $foto)
    {
        $this->authorize('update', $noticia);

        if ((int) $foto->noticia_id !== (int) $noticia->id) {
            abort(404);
        }

        $this->service->deleteFoto($noticia, $foto);

        return redirect()
            ->route('admin.noticias.edit', $noticia)
            ->with('success', 'Foto removida com sucesso.');
    }
}
