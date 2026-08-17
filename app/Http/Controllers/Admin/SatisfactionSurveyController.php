<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSatisfactionSurveyRequest;
use App\Http\Requests\Admin\UpdateSatisfactionSurveyRequest;
use App\Models\SatisfactionSurvey;
use App\Services\SatisfactionSurveyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SatisfactionSurveyController extends Controller
{
    public function __construct(private SatisfactionSurveyService $service) {}

    public function index(Request $request): View
    {
        $surveys = $this->service->paginateFiltered(
            15,
            $request->string('search')->toString() ?: null,
            $request->string('status')->toString() !== '' ? $request->string('status')->toString() : null
        );

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Pesquisas de satisfação'],
        ];

        return view('admin.satisfaction-surveys.index', compact('surveys', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Pesquisas de satisfação', 'href' => route('admin.pesquisas-satisfacao.index')],
            ['label' => 'Nova pesquisa'],
        ];

        return view('admin.satisfaction-surveys.create', compact('breadcrumbs'));
    }

    public function store(StoreSatisfactionSurveyRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return redirect()->route('admin.pesquisas-satisfacao.index')->with('success', 'Pesquisa criada com sucesso.');
    }

    public function show(SatisfactionSurvey $survey): View
    {
        $survey->load(['questions.options']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Pesquisas de satisfação', 'href' => route('admin.pesquisas-satisfacao.index')],
            ['label' => 'Visualizar'],
        ];

        return view('admin.satisfaction-surveys.show', compact('survey', 'breadcrumbs'));
    }

    public function edit(SatisfactionSurvey $survey): View
    {
        $survey->load(['questions.options']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Pesquisas de satisfação', 'href' => route('admin.pesquisas-satisfacao.index')],
            ['label' => 'Editar'],
        ];

        return view('admin.satisfaction-surveys.edit', compact('survey', 'breadcrumbs'));
    }

    public function update(UpdateSatisfactionSurveyRequest $request, SatisfactionSurvey $survey): RedirectResponse
    {
        $this->service->update($survey, $request->validated());

        return redirect()->route('admin.pesquisas-satisfacao.index')->with('success', 'Pesquisa atualizada com sucesso.');
    }

    public function destroy(SatisfactionSurvey $survey): RedirectResponse
    {
        $this->service->delete($survey);

        return redirect()->route('admin.pesquisas-satisfacao.index')->with('success', 'Pesquisa removida com sucesso.');
    }
}
