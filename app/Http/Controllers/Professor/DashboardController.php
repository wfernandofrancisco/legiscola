<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Support\ProfessorContext;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        ProfessorContext::requireDocentePainel();
        $teacher = ProfessorContext::teacher();
        $ids = ProfessorContext::assignedCourseClassIds();

        $turmas = CourseClass::query()
            ->whereIn('id', $ids ?: [0])
            ->with(['course:id,name'])
            ->orderByDesc('updated_at')
            ->limit(12)
            ->get();

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('professor.dashboard')],
        ];

        return view('professor.dashboard', compact('teacher', 'turmas', 'breadcrumbs'));
    }
}
