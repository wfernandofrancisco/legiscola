<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\SobreEscolaServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreSobreEscolaRequest;
use App\Http\Requests\Escola\UpdateSobreEscolaRequest;
use App\Models\SobreEscola;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SobreEscolaController extends Controller
{
    public function __construct(private SobreEscolaServiceInterface $service) {}

    public function index(): View
    {
        $items = $this->service->paginate(15);
        $firstItem = SobreEscola::query()->latest()->first();
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Sobre a Escola'],
        ];

        return view('admin.sobre-escola.index', compact('items', 'firstItem', 'breadcrumbs'));
    }

    public function create(): View|RedirectResponse
    {
        if (SobreEscola::query()->exists()) {
            return redirect()->route('admin.sobre-escola.index')
                ->with('error', 'Já existe um registro de Sobre a Escola. Você pode apenas editar ou excluir o atual.');
        }

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Sobre a Escola', 'href' => route('admin.sobre-escola.index')],
            ['label' => 'Novo conteúdo'],
        ];

        return view('admin.sobre-escola.create', compact('breadcrumbs'));
    }

    public function store(StoreSobreEscolaRequest $request): RedirectResponse
    {
        if (SobreEscola::query()->exists()) {
            return redirect()->route('admin.sobre-escola.index')
                ->with('error', 'Já existe um registro de Sobre a Escola. Você pode apenas editar ou excluir o atual.');
        }

        $this->service->create($request->validated());
        return redirect()->route('admin.sobre-escola.index')->with('success', 'Conteúdo criado com sucesso.');
    }

    public function edit(SobreEscola $sobreEscola): View
    {
        $item = $sobreEscola->load(['eixos', 'pessoas']);
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Sobre a Escola', 'href' => route('admin.sobre-escola.index')],
            ['label' => 'Editar conteúdo'],
        ];

        return view('admin.sobre-escola.edit', compact('item', 'breadcrumbs'));
    }

    public function update(UpdateSobreEscolaRequest $request, SobreEscola $sobreEscola): RedirectResponse
    {
        $this->service->update($sobreEscola, $request->validated());
        return redirect()->route('admin.sobre-escola.index')->with('success', 'Conteúdo atualizado com sucesso.');
    }

    public function destroy(SobreEscola $sobreEscola): RedirectResponse
    {
        $this->service->delete($sobreEscola);
        return redirect()->route('admin.sobre-escola.index')->with('success', 'Conteúdo removido com sucesso.');
    }
}
