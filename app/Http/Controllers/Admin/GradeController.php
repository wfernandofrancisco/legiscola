<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\GradeServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreGradeRequest;
use App\Http\Requests\Escola\UpdateGradeRequest;
use App\Models\Grade;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function __construct(private GradeServiceInterface $service) {}

    public function index(): View
    {
        return view('app.professor.grades.index', ['grades' => $this->service->paginate()]);
    }

    public function store(StoreGradeRequest $request): RedirectResponse
    {
        $this->authorize('create', Grade::class);
        $this->service->create($request->validated());
        return back()->with('success', 'Nota lançada com sucesso.');
    }

    public function update(UpdateGradeRequest $request, Grade $grade): RedirectResponse
    {
        $this->authorize('update', $grade);
        $this->service->update($grade, $request->validated());
        return back()->with('success', 'Nota atualizada com sucesso.');
    }
}
