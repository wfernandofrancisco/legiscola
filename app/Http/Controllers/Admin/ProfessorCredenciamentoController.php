<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\ProfessorCredenciamentoServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreProfessorCredenciamentoRequest;
use App\Http\Requests\Escola\UpdateProfessorCredenciamentoRequest;
use App\Models\ProfessorCredenciamento;
use App\Models\ProfessorCredenciamentoAnexo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfessorCredenciamentoController extends Controller
{
    public function __construct(private ProfessorCredenciamentoServiceInterface $service) {}

    public function index(Request $request): View
    {
        $credenciamentos = $this->service->paginateFiltered(15, $request->string('search')->toString() ?: null);
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Docentes / Credenciamento'],
        ];

        return view('admin.professores-credenciamentos.index', compact('credenciamentos', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Docentes / Credenciamento', 'href' => route('admin.professores-credenciamentos.index')],
            ['label' => 'Novo conteúdo'],
        ];

        return view('admin.professores-credenciamentos.create', compact('breadcrumbs'));
    }

    public function store(StoreProfessorCredenciamentoRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('admin.professores-credenciamentos.index')->with('success', 'Conteúdo de credenciamento criado com sucesso.');
    }

    public function edit(ProfessorCredenciamento $professoresCredenciamento): View
    {
        $credenciamento = $professoresCredenciamento->load('anexos');
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Docentes / Credenciamento', 'href' => route('admin.professores-credenciamentos.index')],
            ['label' => 'Editar conteúdo'],
        ];

        return view('admin.professores-credenciamentos.edit', compact('credenciamento', 'breadcrumbs'));
    }

    public function update(UpdateProfessorCredenciamentoRequest $request, ProfessorCredenciamento $professoresCredenciamento): RedirectResponse
    {
        $this->service->update($professoresCredenciamento, $request->validated());
        return redirect()->route('admin.professores-credenciamentos.index')->with('success', 'Conteúdo atualizado com sucesso.');
    }

    public function destroy(ProfessorCredenciamento $professoresCredenciamento): RedirectResponse
    {
        $this->service->delete($professoresCredenciamento);
        return redirect()->route('admin.professores-credenciamentos.index')->with('success', 'Conteúdo removido com sucesso.');
    }

    public function destroyAnexo(ProfessorCredenciamento $professoresCredenciamento, ProfessorCredenciamentoAnexo $anexo): RedirectResponse
    {
        abort_if((int) $anexo->professor_credenciamento_id !== (int) $professoresCredenciamento->id, 404);
        $this->service->deleteAnexo($anexo);
        return back()->with('success', 'Anexo removido com sucesso.');
    }
}
