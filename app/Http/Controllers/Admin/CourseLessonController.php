<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Escola\StoreCourseLessonRequest;
use App\Models\Course;
use App\Models\CourseLesson;
use App\Services\CourseLessonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CourseLessonController extends Controller
{
    public function __construct(private CourseLessonService $lessons) {}

    public function store(StoreCourseLessonRequest $request, Course $course): RedirectResponse
    {
        abort_if($course->isFromCatalog(), 403, 'As aulas deste curso vêm do catálogo regional.');

        $this->lessons->create($course, $request->validated() + [
            'video_file' => $request->file('video_file'),
            'material_file' => $request->file('material_file'),
        ]);

        return redirect()
            ->route('admin.cursos.edit', $course)
            ->with('success', 'Aula adicionada ao curso. Datas e horários entram na grade da turma.');
    }

    public function update(StoreCourseLessonRequest $request, Course $course, CourseLesson $courseLesson): RedirectResponse
    {
        $this->assertBelongsTo($course, $courseLesson);

        $this->lessons->update($courseLesson, $request->validated() + [
            'video_file' => $request->file('video_file'),
            'material_file' => $request->file('material_file'),
        ]);

        return redirect()
            ->route('admin.cursos.edit', $course)
            ->with('success', 'Aula do curso atualizada. Turmas que usam esta aula herdam vídeo e material.');
    }

    public function destroy(Course $course, CourseLesson $courseLesson): RedirectResponse
    {
        $this->assertBelongsTo($course, $courseLesson);
        $this->lessons->delete($courseLesson);

        return redirect()
            ->route('admin.cursos.edit', $course)
            ->with('success', 'Aula removida do curso. A grade das turmas já abertas não foi apagada.');
    }

    /**
     * Lista as aulas de conteúdo para montar a grade ao criar a turma.
     */
    public function gradePayload(Course $course): JsonResponse
    {
        if ($course->isFromCatalog()) {
            $course->loadMissing('catalogItem.lessons');
            $lessons = $course->catalogItem?->lessons ?? collect();

            return response()->json([
                'source' => 'catalog',
                'from_catalog' => true,
                'lessons' => $lessons->map(fn ($lesson) => [
                    'id' => $lesson->id,
                    'title' => $lesson->titulo,
                    'catalog_lesson_id' => $lesson->id,
                    'course_lesson_id' => null,
                    'has_video' => $lesson->hasVideo(),
                    'has_material' => $lesson->hasMaterial(),
                ])->values(),
            ]);
        }

        $course->loadMissing('lessons');

        return response()->json([
            'source' => 'course',
            'from_catalog' => false,
            'lessons' => $course->lessons->map(fn (CourseLesson $lesson) => [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'course_lesson_id' => $lesson->id,
                'catalog_lesson_id' => null,
                'has_video' => $lesson->hasVideo(),
                'has_material' => $lesson->hasMaterial(),
            ])->values(),
        ]);
    }

    public function reorder(Request $request, Course $course): RedirectResponse
    {
        abort_if($course->isFromCatalog(), 403);

        $data = $request->validate([
            'ordem' => ['required', 'array'],
            'ordem.*' => ['integer'],
        ]);

        $this->lessons->reorder($course, $data['ordem']);

        return back()->with('success', 'Ordem das aulas atualizada.');
    }

    private function assertBelongsTo(Course $course, CourseLesson $lesson): void
    {
        abort_unless((int) $lesson->course_id === (int) $course->id, 404);
    }
}
