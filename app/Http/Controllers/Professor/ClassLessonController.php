<?php

namespace App\Http\Controllers\Professor;

use App\Contracts\Services\ClassLessonServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreClassLessonRequest;
use App\Http\Requests\Escola\UpdateClassLessonRequest;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Support\ProfessorContext;
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
        ProfessorContext::requireDocentePainel();
        $ids = ProfessorContext::assignedCourseClassIds();

        $classLessons = $this->service->paginateFiltered(
            15,
            $request->string('search')->toString(),
            $request->integer('course_class_id') ?: null,
            $ids
        );

        $courseClasses = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->orderBy('name')
            ->get();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Aulas'],
        ];

        return view('professor.aulas.index', compact('classLessons', 'courseClasses', 'breadcrumbs'));
    }

    public function create(Request $request): View
    {
        ProfessorContext::requireDocentePainel();
        $ids = ProfessorContext::assignedCourseClassIds();
        $courseClasses = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'tipo_turma']);

        $prefillCourseClassId = $request->integer('course_class_id') ?: null;
        if ($prefillCourseClassId !== null && ! in_array($prefillCourseClassId, $ids, true)) {
            $prefillCourseClassId = null;
        }
        $prefillCourseClassName = '';
        if ($prefillCourseClassId) {
            $prefillCourseClassName = (string) (CourseClass::query()->find($prefillCourseClassId)?->name ?? '');
        }

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Aulas', 'href' => route('professor.aulas.index')],
            ['label' => 'Nova aula'],
        ];

        return view('professor.aulas.create', compact(
            'breadcrumbs',
            'courseClasses',
            'prefillCourseClassId',
            'prefillCourseClassName'
        ));
    }

    public function store(StoreClassLessonRequest $request): RedirectResponse
    {
        $data = collect($request->validated())->except(['material_file'])->all();
        $this->assertCourseClassAllowed((int) $data['course_class_id']);

        if ($request->hasFile('material_file')) {
            $file = $request->file('material_file');
            $data['material_file_path'] = $file->store('class-lessons/'.TenantContext::getTenantId(), 'public');
            $data['material_file_name'] = $file->getClientOriginalName();
        }
        $this->service->create($data);

        return redirect()->route('professor.aulas.index')->with('success', 'Aula criada com sucesso.');
    }

    public function edit(ClassLesson $aula): View
    {
        $aula->load('courseClass');
        $this->authorize('interactAsAssignedProfessor', $aula->courseClass);

        $ids = ProfessorContext::assignedCourseClassIds();
        $courseClasses = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->orderBy('name')
            ->get(['id', 'name', 'tipo_turma']);

        $classLesson = $aula;
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Aulas', 'href' => route('professor.aulas.index')],
            ['label' => 'Editar aula'],
        ];

        return view('professor.aulas.edit', compact('classLesson', 'breadcrumbs', 'courseClasses'));
    }

    public function update(UpdateClassLessonRequest $request, ClassLesson $aula): RedirectResponse
    {
        $aula->load('courseClass');
        $this->authorize('interactAsAssignedProfessor', $aula->courseClass);

        $data = collect($request->validated())->except(['material_file', 'remove_material_file'])->all();
        $this->assertCourseClassAllowed((int) $data['course_class_id']);

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

        return redirect()->route('professor.aulas.index')->with('success', 'Aula atualizada com sucesso.');
    }

    public function destroy(ClassLesson $aula): RedirectResponse
    {
        $aula->load('courseClass');
        $this->authorize('interactAsAssignedProfessor', $aula->courseClass);
        $this->service->delete($aula);

        return redirect()->route('professor.aulas.index')->with('success', 'Aula removida com sucesso.');
    }

    public function searchCourseClasses(Request $request): JsonResponse
    {
        $ids = ProfessorContext::assignedCourseClassIds();
        $term = trim((string) $request->string('q'));

        $results = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
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

    private function assertCourseClassAllowed(int $courseClassId): void
    {
        abort_unless(in_array($courseClassId, ProfessorContext::assignedCourseClassIds(), true), 403);
        $cc = CourseClass::query()->findOrFail($courseClassId);
        $this->authorize('interactAsAssignedProfessor', $cc);
    }
}
