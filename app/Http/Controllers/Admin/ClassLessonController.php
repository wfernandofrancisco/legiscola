<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\ClassLessonServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreClassLessonRequest;
use App\Http\Requests\Escola\UpdateClassLessonRequest;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Support\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ClassLessonController extends Controller
{
    public function __construct(private ClassLessonServiceInterface $service) {}

    public function index(Request $request): View
    {
        $classLessons = $this->service->paginateFiltered(
            15,
            $request->string('search')->toString(),
            $request->integer('course_class_id') ?: null
        );
        $courseClasses = CourseClass::query()->orderBy('name')->get();
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Aulas'],
        ];
        return view('admin.class-lessons.index', compact('classLessons', 'courseClasses', 'breadcrumbs'));
    }

    public function create(): View
    {
        $prefillCourseClass = null;
        if (request()->filled('course_class_id')) {
            $prefillCourseClass = CourseClass::query()->find((int) request('course_class_id'));
        }

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Aulas', 'href' => route('admin.aulas.index')],
            ['label' => 'Nova aula'],
        ];

        return view('admin.class-lessons.create', compact('breadcrumbs', 'prefillCourseClass'));
    }

    public function store(StoreClassLessonRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['material_file'])->all();
        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            $data['material_file_path'] = $file->store('class-lessons/'.TenantContext::getTenantId(), 'public');
            $data['material_file_name'] = $file->getClientOriginalName();
        }
        $this->service->create($data);
        return redirect()->route('admin.aulas.index')->with('success', 'Aula criada com sucesso.');
    }

    public function edit(ClassLesson $aula): View
    {
        $classLesson = $aula;
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Aulas', 'href' => route('admin.aulas.index')],
            ['label' => 'Editar aula'],
        ];
        return view('admin.class-lessons.edit', compact('classLesson', 'breadcrumbs'));
    }

    public function update(UpdateClassLessonRequest $request, ClassLesson $aula): RedirectResponse
    {
        $data = collect($request->validated())->except(['material_file', 'remove_material_file'])->all();

        if ($request->hasFile('material_file')) {
            if ($aula->material_file_path) {
                Storage::disk('public')->delete($aula->material_file_path);
            }
            $file = $request->file('material_file');
            $data['material_file_path'] = $file->store('class-lessons/'.TenantContext::getTenantId(), 'public');
            $data['material_file_name'] = $file->getClientOriginalName();
        } elseif ($request->boolean('remove_material_file')) {
            if ($aula->material_file_path) {
                Storage::disk('public')->delete($aula->material_file_path);
            }
            $data['material_file_path'] = null;
            $data['material_file_name'] = null;
        }

        $this->service->update($aula, $data);
        return redirect()->route('admin.aulas.index')->with('success', 'Aula atualizada com sucesso.');
    }

    public function destroy(ClassLesson $aula): RedirectResponse
    {
        $this->service->delete($aula);
        return redirect()->route('admin.aulas.index')->with('success', 'Aula removida com sucesso.');
    }

    public function searchCourseClasses(Request $request): JsonResponse
    {
        $term = trim((string) $request->string('q'));

        $results = CourseClass::query()
            ->with('course:id,name')
            ->when($term !== '', function ($query) use ($term): void {
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', "%{$term}%")
                        ->orWhereHas('course', fn ($courseQuery) => $courseQuery->where('name', 'like', "%{$term}%"));
                });
            })
            ->orderBy('name')
            ->limit(20)
            ->get()
            ->map(fn (CourseClass $courseClass) => [
                'id' => $courseClass->id,
                'name' => $courseClass->name,
                'course' => $courseClass->course?->name,
            ]);

        return response()->json($results);
    }
}
