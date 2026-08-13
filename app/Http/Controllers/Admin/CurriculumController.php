<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CurriculumServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCurriculumRequest;
use App\Http\Requests\Escola\UpdateCurriculumRequest;
use App\Models\Course;
use App\Models\Curriculum;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function __construct(private CurriculumServiceInterface $service) {}

    public function index(Request $request): View
    {
        $curricula = Curriculum::query()
            ->with('course')
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', (int) $request->integer('course_id')))
            ->orderBy('position')
            ->paginate(15)
            ->withQueryString();

        $courses = Course::query()->orderBy('name')->get(['id', 'name']);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Grade curricular'],
        ];

        return view('admin.curricula.index', compact('curricula', 'courses', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Grade curricular', 'href' => route('admin.grades-curriculares.index')],
            ['label' => 'Nova disciplina'],
        ];
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.curricula.create', compact('breadcrumbs', 'courses'));
    }

    public function edit(Curriculum $curriculum): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Grade curricular', 'href' => route('admin.grades-curriculares.index')],
            ['label' => 'Editar disciplina'],
        ];
        $courses = Course::query()->orderBy('name')->get(['id', 'name']);

        return view('admin.curricula.edit', compact('curriculum', 'breadcrumbs', 'courses'));
    }

    public function store(StoreCurriculumRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return back()->with('success', 'Grade curricular criada.');
    }

    public function update(UpdateCurriculumRequest $request, Curriculum $curriculum): RedirectResponse
    {
        $this->service->update($curriculum, $request->validated());
        return back()->with('success', 'Grade curricular atualizada.');
    }

    public function destroy(Curriculum $curriculum): RedirectResponse
    {
        $this->service->delete($curriculum);
        return back()->with('success', 'Grade curricular removida.');
    }
}
