<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Noticia;
use App\Models\Student;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $totalStudents = Student::query()->where('tenant_id', $tenantId)->count();
        $matriculatedStudentIds = Enrollment::query()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('course_class_id')
            ->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca'])
            ->distinct()
            ->count('student_id');

        $concludedStudentIds = Enrollment::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'concluido')
            ->distinct()
            ->count('student_id');

        $classStatusCounts = CourseClass::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $enrollmentStatusCounts = Enrollment::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $sexoLabels = [
            'masculino' => 'Masculino',
            'feminino' => 'Feminino',
            'outro' => 'Outro',
            'nao_informado' => 'Não informado',
        ];

        $sexoCountsRaw = Student::query()
            ->where('tenant_id', $tenantId)
            ->selectRaw("COALESCE(NULLIF(sexo, ''), 'nao_informado') as sexo_key, COUNT(*) as total")
            ->groupBy('sexo_key')
            ->pluck('total', 'sexo_key')
            ->map(fn ($value) => (int) $value)
            ->all();

        $sexoCounts = collect($sexoLabels)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'total' => $sexoCountsRaw[$key] ?? 0,
            ])
            ->values()
            ->all();

        $ageBuckets = [
            'ate_17' => ['label' => 'Até 17', 'total' => 0],
            '18_24' => ['label' => '18 a 24', 'total' => 0],
            '25_34' => ['label' => '25 a 34', 'total' => 0],
            '35_44' => ['label' => '35 a 44', 'total' => 0],
            '45_59' => ['label' => '45 a 59', 'total' => 0],
            '60_plus' => ['label' => '60+', 'total' => 0],
            'nao_informado' => ['label' => 'Sem data', 'total' => 0],
        ];

        $today = CarbonImmutable::today();
        Student::query()
            ->where('tenant_id', $tenantId)
            ->get(['birth_date'])
            ->each(function (Student $student) use (&$ageBuckets, $today): void {
                if (! $student->birth_date) {
                    $ageBuckets['nao_informado']['total']++;
                    return;
                }

                $age = $student->birth_date->diffInYears($today);
                $bucket = match (true) {
                    $age <= 17 => 'ate_17',
                    $age <= 24 => '18_24',
                    $age <= 34 => '25_34',
                    $age <= 44 => '35_44',
                    $age <= 59 => '45_59',
                    default => '60_plus',
                };

                $ageBuckets[$bucket]['total']++;
            });

        $openClasses = CourseClass::query()
            ->where('tenant_id', $tenantId)
            ->where('status', 'inscricao')
            ->with('course:id,name')
            ->withCount([
                'enrollments as matriculas_count' => fn ($query) => $query->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->orderBy('enrollment_end')
            ->limit(5)
            ->get();

        $stats = [
            'total_usuarios' => User::where('tenant_id', $tenantId)->count(),
            'total_noticias' => Noticia::where('tenant_id', $tenantId)->count(),
            'noticias_publicadas' => Noticia::where('tenant_id', $tenantId)->where('ativo', true)->count(),
            'turmas_total' => array_sum($classStatusCounts),
            'turmas_ativas' => $classStatusCounts['em_andamento'] ?? 0,
            'turmas_encerradas' => $classStatusCounts['concluido'] ?? 0,
            'turmas_inscricao' => $classStatusCounts['inscricao'] ?? 0,
            'alunos_cadastrados' => $totalStudents,
            'alunos_matriculados' => $matriculatedStudentIds,
            'alunos_concluintes' => $concludedStudentIds,
            'alunos_sem_turma' => max(0, $totalStudents - $matriculatedStudentIds),
            'matriculas_total' => array_sum($enrollmentStatusCounts),
        ];

        return view('admin.dashboard', compact(
            'stats',
            'classStatusCounts',
            'enrollmentStatusCounts',
            'sexoCounts',
            'ageBuckets',
            'openClasses'
        ));
    }
}
