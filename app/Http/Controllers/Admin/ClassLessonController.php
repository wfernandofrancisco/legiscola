<?php

namespace App\Http\Controllers\Admin;

use App\Contracts\Services\ClassLessonServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreClassLessonRequest;
use App\Http\Requests\Escola\UpdateClassLessonRequest;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(StoreClassLessonRequest $request): RedirectResponse|JsonResponse
    {
        $data = $request->validated();
        $this->service->create($data);
        $courseClassId = (int) ($data['course_class_id'] ?? 0);
        $url = $courseClassId > 0
            ? route('admin.turmas.show', ['turma' => $courseClassId, 'tab' => 'aulas'])
            : route('admin.aulas.index');

        return $this->respondAfterSave($request, $url, 'Aula criada com sucesso.');
    }

    public function edit(ClassLesson $aula): View
    {
        $classLesson = $aula->loadMissing('catalogLesson', 'courseLesson', 'courseClass');
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Aulas', 'href' => route('admin.aulas.index')],
            ['label' => 'Editar aula'],
        ];
        return view('admin.class-lessons.edit', compact('classLesson', 'breadcrumbs'));
    }

    public function update(UpdateClassLessonRequest $request, ClassLesson $aula): RedirectResponse|JsonResponse
    {
        $this->service->update($aula, $request->validated());
        $url = (int) $aula->course_class_id > 0
            ? route('admin.turmas.show', ['turma' => $aula->course_class_id, 'tab' => 'aulas'])
            : route('admin.aulas.index');

        return $this->respondAfterSave($request, $url, 'Aula atualizada com sucesso.');
    }

    public function destroy(ClassLesson $aula): RedirectResponse
    {
        $courseClassId = (int) $aula->course_class_id;
        $this->service->delete($aula);
        if ($courseClassId > 0) {
            return redirect()
                ->route('admin.turmas.show', ['turma' => $courseClassId, 'tab' => 'aulas'])
                ->with('success', 'Aula removida com sucesso.');
        }

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

    private function respondAfterSave(Request $request, string $url, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson() || $request->ajax()) {
            session()->flash('success', $message);

            return response()->json([
                'redirect' => $url,
                'message' => $message,
            ]);
        }

        return redirect()->to($url)->with('success', $message);
    }
}
