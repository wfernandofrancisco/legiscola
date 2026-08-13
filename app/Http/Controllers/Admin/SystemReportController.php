<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Escolaridade;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\TenantAdminSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class SystemReportController extends Controller
{
    private const ATTENDANCE_LIKE_STATUSES = ['inscrito', 'cursando', 'concluido', 'baixa_presenca'];

    public function index(Request $request): View
    {
        $request->validate([
            'data_inicio' => ['nullable', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
        ]);

        [$start, $end] = $this->dateRangeOrDefault($request);
        $data = $this->buildReport($start, $end);

        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Relatórios'],
        ];

        return view('admin.reports.index', array_merge(compact('breadcrumbs', 'start', 'end'), $data));
    }

    public function pdf(Request $request): Response
    {
        $request->validate([
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['required', 'date', 'after_or_equal:data_inicio'],
        ]);

        $start = Carbon::parse($request->string('data_inicio'))->startOfDay();
        $end = Carbon::parse($request->string('data_fim'))->endOfDay();

        $data = $this->buildReport($start, $end);
        $tenant = auth()->user()->tenant()->first();
        $settings = TenantAdminSetting::query()->where('tenant_id', auth()->user()->tenant_id)->first();

        $logoPath = null;
        if (! empty($settings?->logo_prefeitura_path)) {
            $candidate = storage_path('app/public/'.$settings->logo_prefeitura_path);
            if (is_file($candidate)) {
                $logoPath = $candidate;
            }
        }

        $pdf = Pdf::loadView('admin.reports.system-report-pdf', array_merge($data, [
            'tenant' => $tenant,
            'logoPath' => $logoPath,
            'printedBy' => auth()->user()->name,
            'printedAt' => now(),
            'periodStart' => $start,
            'periodEnd' => $end,
        ]))->setPaper('a4', 'portrait');

        $filename = 'relatorio-sistema-'.$start->format('Y-m-d').'-'.$end->format('Y-m-d').'.pdf';

        return $pdf->stream($filename);
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function dateRangeOrDefault(Request $request): array
    {
        $start = $request->filled('data_inicio')
            ? Carbon::parse($request->string('data_inicio'))->startOfDay()
            : now()->startOfMonth()->startOfDay();

        $end = $request->filled('data_fim')
            ? Carbon::parse($request->string('data_fim'))->endOfDay()
            : now()->endOfDay();

        if ($end->lt($start)) {
            $end = $start->copy()->endOfDay();
        }

        return [$start, $end];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReport(Carbon $start, Carbon $end): array
    {
        $today = CarbonImmutable::today();

        $totalStudents = Student::query()->count();

        $studentsInPeriod = Student::query()
            ->whereBetween('created_at', [$start, $end])
            ->get(['birth_date', 'sexo', 'escolaridade', 'bairro']);

        $studentsInPeriodCount = $studentsInPeriod->count();

        $sexoLabels = [
            'masculino' => 'Masculino',
            'feminino' => 'Feminino',
            'outro' => 'Outro',
            'nao_informado' => 'Não informado',
        ];

        $sexoCountsRaw = [];
        foreach ($studentsInPeriod as $student) {
            $key = $student->sexo;
            if ($key === null || $key === '') {
                $key = 'nao_informado';
            }
            $sexoCountsRaw[$key] = ($sexoCountsRaw[$key] ?? 0) + 1;
        }

        $sexoCounts = collect($sexoLabels)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'total' => (int) ($sexoCountsRaw[$key] ?? 0),
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

        foreach ($studentsInPeriod as $student) {
            if (! $student->birth_date) {
                $ageBuckets['nao_informado']['total']++;

                continue;
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
        }

        $escolaridadeCounts = [];
        foreach ($studentsInPeriod as $student) {
            $e = $student->escolaridade;
            if ($e instanceof Escolaridade) {
                $key = $e->value;
                $label = $e->label();
            } elseif (is_string($e) && ($try = Escolaridade::tryFrom($e)) !== null) {
                $key = $try->value;
                $label = $try->label();
            } else {
                $key = 'nao_informado';
                $label = 'Não informado';
            }
            if (! isset($escolaridadeCounts[$key])) {
                $escolaridadeCounts[$key] = ['label' => $label, 'total' => 0];
            }
            $escolaridadeCounts[$key]['total']++;
        }
        uasort($escolaridadeCounts, fn ($a, $b) => $b['total'] <=> $a['total']);

        $bairroCounts = [];
        foreach ($studentsInPeriod as $student) {
            $b = trim((string) $student->bairro);
            $key = $b === '' ? 'nao_informado' : mb_strtolower($b);
            $label = $b === '' ? 'Não informado' : $b;
            if (! isset($bairroCounts[$key])) {
                $bairroCounts[$key] = ['label' => $label, 'total' => 0];
            }
            $bairroCounts[$key]['total']++;
        }
        uasort($bairroCounts, fn ($a, $b) => $b['total'] <=> $a['total']);

        $classStatusCounts = CourseClass::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $classStatusInPeriod = CourseClass::query()
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $enrollmentStatusAll = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->selectRaw('enrollments.status, COUNT(*) as total')
            ->groupBy('enrollments.status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $newEnrollmentsByStatus = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->whereBetween('enrollments.created_at', [$start, $end])
            ->selectRaw('enrollments.status, COUNT(*) as total')
            ->groupBy('enrollments.status')
            ->pluck('total', 'status')
            ->map(fn ($value) => (int) $value)
            ->all();

        $withdrawalsInPeriod = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->where('enrollments.status', 'desistido')
            ->whereBetween('enrollments.updated_at', [$start, $end])
            ->count();

        $withdrawalsByCourse = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->where('enrollments.status', 'desistido')
            ->whereBetween('enrollments.updated_at', [$start, $end])
            ->join('course_classes', 'enrollments.course_class_id', '=', 'course_classes.id')
            ->join('courses', 'course_classes.course_id', '=', 'courses.id')
            ->selectRaw('courses.id as course_id, courses.name as course_name, COUNT(*) as total')
            ->groupBy('courses.id', 'courses.name')
            ->orderByDesc('total')
            ->get();

        $completionByCourse = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->join('course_classes', 'enrollments.course_class_id', '=', 'course_classes.id')
            ->where('course_classes.status', 'concluido')
            ->selectRaw(
                'courses.id as course_id, courses.name as course_name, '.
                'SUM(CASE WHEN enrollments.status = \'concluido\' THEN 1 ELSE 0 END) as concluded, '.
                'COUNT(*) as total'
            )
            ->join('courses', 'course_classes.course_id', '=', 'courses.id')
            ->groupBy('courses.id', 'courses.name')
            ->havingRaw('COUNT(*) > 0')
            ->get()
            ->map(function ($row) {
                $total = (int) $row->total;
                $concluded = (int) $row->concluded;
                $rate = $total > 0 ? round(100 * $concluded / $total, 1) : 0.0;

                return [
                    'course_id' => (int) $row->course_id,
                    'course_name' => (string) $row->course_name,
                    'concluded' => $concluded,
                    'total' => $total,
                    'rate' => $rate,
                ];
            })
            ->sortByDesc('rate')
            ->values();

        $bestCompletion = $completionByCourse->first();

        $matriculatedDistinct = Enrollment::query()
            ->whereNotNull('enrollments.course_class_id')
            ->whereIn('enrollments.status', self::ATTENDANCE_LIKE_STATUSES)
            ->distinct()
            ->count('enrollments.student_id');

        $totalCourses = Course::query()->count();
        $totalTeachers = Teacher::query()->count();
        $certificatesIssuedInPeriod = Certificate::query()
            ->whereNotNull('issued_at')
            ->whereBetween('issued_at', [$start, $end])
            ->whereNotIn('status', ['revoked', 'revogado', 'inativo', 'cancelado'])
            ->count();

        $classStatusLabels = [
            'cadastrado' => 'Cadastrado',
            'inscricao' => 'Inscrição',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
        ];

        $enrollmentStatusLabels = [
            'inscrito' => 'Inscrito',
            'cursando' => 'Cursando',
            'desistido' => 'Desistido',
            'concluido' => 'Concluído',
            'baixa_presenca' => 'Baixa presença',
        ];

        $chartsPayload = $this->buildChartsPayload(
            $start,
            $end,
            $classStatusLabels,
            $enrollmentStatusLabels,
            $classStatusCounts,
            $sexoCounts,
            $ageBuckets,
            $escolaridadeCounts,
            $bairroCounts,
            $enrollmentStatusAll,
            $newEnrollmentsByStatus,
            $withdrawalsByCourse,
            $completionByCourse,
        );

        return [
            'totalStudents' => $totalStudents,
            'matriculatedDistinct' => $matriculatedDistinct,
            'studentsInPeriodCount' => $studentsInPeriodCount,
            'sexoCounts' => $sexoCounts,
            'ageBuckets' => $ageBuckets,
            'escolaridadeCounts' => $escolaridadeCounts,
            'bairroCounts' => $bairroCounts,
            'classStatusCounts' => $classStatusCounts,
            'classStatusInPeriod' => $classStatusInPeriod,
            'classStatusLabels' => $classStatusLabels,
            'enrollmentStatusAll' => $enrollmentStatusAll,
            'newEnrollmentsByStatus' => $newEnrollmentsByStatus,
            'enrollmentStatusLabels' => $enrollmentStatusLabels,
            'withdrawalsInPeriod' => $withdrawalsInPeriod,
            'withdrawalsByCourse' => $withdrawalsByCourse,
            'completionByCourse' => $completionByCourse,
            'bestCompletion' => $bestCompletion,
            'totalCourses' => $totalCourses,
            'totalTeachers' => $totalTeachers,
            'certificatesIssuedInPeriod' => $certificatesIssuedInPeriod,
            'chartsPayload' => $chartsPayload,
        ];
    }

    /**
     * @param  array<string, string>  $classStatusLabels
     * @param  array<string, string>  $enrollmentStatusLabels
     * @param  array<string, int>  $classStatusCounts
     * @param  list<array{key: string, label: string, total: int}>  $sexoCounts
     * @param  array<string, array{label: string, total: int}>  $ageBuckets
     * @param  array<string, array{label: string, total: int}>  $escolaridadeCounts
     * @param  array<string, array{label: string, total: int}>  $bairroCounts
     * @param  array<string, int>  $enrollmentStatusAll
     * @param  array<string, int>  $newEnrollmentsByStatus
     * @param  Collection<int, object>  $withdrawalsByCourse
     * @param  Collection<int, array<string, mixed>>  $completionByCourse
     * @return array<string, mixed>
     */
    private function buildChartsPayload(
        Carbon $start,
        Carbon $end,
        array $classStatusLabels,
        array $enrollmentStatusLabels,
        array $classStatusCounts,
        array $sexoCounts,
        array $ageBuckets,
        array $escolaridadeCounts,
        array $bairroCounts,
        array $enrollmentStatusAll,
        array $newEnrollmentsByStatus,
        $withdrawalsByCourse,
        $completionByCourse,
    ): array {
        $enrollmentDayMap = $this->dayCountMap(
            Enrollment::query()
                ->whereNotNull('enrollments.course_class_id')
                ->whereBetween('enrollments.created_at', [$start, $end])
                ->selectRaw('DATE(enrollments.created_at) as day, COUNT(*) as c')
                ->groupBy('day')
                ->get()
        );

        $studentDayMap = $this->dayCountMap(
            Student::query()
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('DATE(created_at) as day, COUNT(*) as c')
                ->groupBy('day')
                ->get()
        );

        $lineLabels = [];
        $enrollmentDaily = [];
        $studentDaily = [];
        for (
            $d = $start->copy()->startOfDay(), $endDay = $end->copy()->startOfDay();
            $d->lte($endDay);
            $d->addDay()
        ) {
            $key = $d->format('Y-m-d');
            $lineLabels[] = $d->format('d/m');
            $enrollmentDaily[] = (int) ($enrollmentDayMap[$key] ?? 0);
            $studentDaily[] = (int) ($studentDayMap[$key] ?? 0);
        }

        $classPieLabels = [];
        $classPieData = [];
        foreach ($classStatusLabels as $key => $label) {
            $n = (int) ($classStatusCounts[$key] ?? 0);
            if ($n > 0) {
                $classPieLabels[] = $label;
                $classPieData[] = $n;
            }
        }

        $sexoPieLabels = [];
        $sexoPieData = [];
        foreach ($sexoCounts as $row) {
            if ($row['total'] > 0) {
                $sexoPieLabels[] = $row['label'];
                $sexoPieData[] = $row['total'];
            }
        }

        $ageBarLabels = [];
        $ageBarData = [];
        foreach ($ageBuckets as $row) {
            if ($row['label'] === 'Sem data' && $row['total'] === 0) {
                continue;
            }
            $ageBarLabels[] = $row['label'];
            $ageBarData[] = $row['total'];
        }

        $enrollBaseLabels = [];
        $enrollBaseData = [];
        foreach ($enrollmentStatusLabels as $key => $label) {
            $n = (int) ($enrollmentStatusAll[$key] ?? 0);
            $enrollBaseLabels[] = $label;
            $enrollBaseData[] = $n;
        }

        $enrollNewLabels = [];
        $enrollNewData = [];
        foreach ($enrollmentStatusLabels as $key => $label) {
            $n = (int) ($newEnrollmentsByStatus[$key] ?? 0);
            $enrollNewLabels[] = $label;
            $enrollNewData[] = $n;
        }

        $withdrawalLabels = [];
        $withdrawalData = [];
        foreach ($withdrawalsByCourse->take(12) as $row) {
            $withdrawalLabels[] = (string) $row->course_name;
            $withdrawalData[] = (int) $row->total;
        }

        $escBarLabels = [];
        $escBarData = [];
        foreach (array_slice(array_values($escolaridadeCounts), 0, 10) as $row) {
            $escBarLabels[] = $row['label'];
            $escBarData[] = $row['total'];
        }

        $bairroBarLabels = [];
        $bairroBarData = [];
        foreach (array_slice(array_values($bairroCounts), 0, 10) as $row) {
            $bairroBarLabels[] = $row['label'];
            $bairroBarData[] = $row['total'];
        }

        $completionLabels = [];
        $completionRates = [];
        foreach ($completionByCourse->take(12) as $row) {
            $completionLabels[] = $row['course_name'];
            $completionRates[] = $row['rate'];
        }

        return [
            'line' => [
                'labels' => $lineLabels,
                'enrollments' => $enrollmentDaily,
                'students' => $studentDaily,
            ],
            'classStatusDoughnut' => [
                'labels' => $classPieLabels,
                'data' => $classPieData,
            ],
            'sexoDoughnut' => [
                'labels' => $sexoPieLabels,
                'data' => $sexoPieData,
            ],
            'ageBar' => [
                'labels' => $ageBarLabels,
                'data' => $ageBarData,
            ],
            'enrollmentBaseBar' => [
                'labels' => $enrollBaseLabels,
                'data' => $enrollBaseData,
            ],
            'enrollmentNewBar' => [
                'labels' => $enrollNewLabels,
                'data' => $enrollNewData,
            ],
            'withdrawalsBar' => [
                'labels' => $withdrawalLabels,
                'data' => $withdrawalData,
            ],
            'escolaridadeBar' => [
                'labels' => $escBarLabels,
                'data' => $escBarData,
            ],
            'bairroBar' => [
                'labels' => $bairroBarLabels,
                'data' => $bairroBarData,
            ],
            'completionBar' => [
                'labels' => $completionLabels,
                'data' => $completionRates,
            ],
        ];
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return array<string, int>
     */
    private function dayCountMap($rows): array
    {
        $map = [];
        foreach ($rows as $row) {
            $day = $row->day ?? null;
            if ($day instanceof CarbonInterface) {
                $key = $day->format('Y-m-d');
            } else {
                $key = substr((string) $day, 0, 10);
            }
            $map[$key] = (int) ($row->c ?? 0);
        }

        return $map;
    }
}
