<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\CourseServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCourseRequest;
use App\Http\Requests\Escola\UpdateCourseRequest;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function __construct(private CourseServiceInterface $service) {}

    public function index(Request $request): View
    {
        $courses = Course::query()
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = trim((string) $request->string('search'));
                $query->where(function ($q) use ($search): void {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', (string) $request->input('status')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Cursos'],
        ];

        return view('admin.courses.index', compact('courses', 'breadcrumbs'));
    }

    public function create(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Cursos', 'href' => route('admin.cursos.index')],
            ['label' => 'Novo curso'],
        ];

        return view('admin.courses.create', compact('breadcrumbs'));
    }

    public function edit(Course $course): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Cursos', 'href' => route('admin.cursos.index')],
            ['label' => 'Editar curso'],
        ];

        return view('admin.courses.edit', compact('course', 'breadcrumbs'));
    }

    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return back()->with('success', 'Curso criado com sucesso.');
    }

    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $this->service->update($course, $request->validated());
        return back()->with('success', 'Curso atualizado com sucesso.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->service->delete($course);
        return back()->with('success', 'Curso removido com sucesso.');
    }

    public function search(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        $results = Course::query()
            ->select(['id', 'name'])
            ->when($term !== '', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(20)
            ->get();

        return response()->json($results);
    }
}
