<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Support\ProfessorContext;
use Illuminate\View\View;

class TurmaController extends Controller
{
    public function index(): View
    {
        ProfessorContext::requireDocentePainel();
        $ids = ProfessorContext::assignedCourseClassIds();

        $turmas = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->with(['course:id,name', 'teachers'])
            ->withCount([
                'enrollments as matriculas_count' => fn ($q) => $q->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Minhas turmas'],
        ];

        return view('professor.turmas.index', compact('turmas', 'breadcrumbs'));
    }

    public function show(CourseClass $turma): View
    {
        $this->authorize('interactAsAssignedProfessor', $turma);

        $turma->load([
            'course',
            'teachers',
            'lessons' => fn ($q) => $q->orderBy('date')->orderBy('start_time'),
        ]);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
            ['label' => 'Minhas turmas', 'href' => route('professor.turmas.index')],
            ['label' => $turma->name],
        ];

        return view('professor.turmas.show', compact('turma', 'breadcrumbs'));
    }
}
