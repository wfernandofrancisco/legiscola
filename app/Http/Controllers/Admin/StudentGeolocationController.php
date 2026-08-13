<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlunoBairro;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentGeolocationController extends Controller
{
    public function index(): View
    {
        $breadcrumbs = [
            ['label' => 'Painel', 'href' => route('admin.dashboard')],
            ['label' => 'Alunos', 'href' => route('admin.alunos.index')],
            ['label' => 'Mapa'],
        ];

        $bairros = AlunoBairro::agrupadosPorBairro();
        $cursos = Course::query()->orderBy('name')->get(['id', 'name']);
        $turmas = CourseClass::query()
            ->with('course:id,name')
            ->orderBy('name')
            ->get(['id', 'name', 'course_id']);

        return view('admin.students.geolocation', compact('breadcrumbs', 'bairros', 'cursos', 'turmas'));
    }

    public function markers(Request $request): JsonResponse
    {
        $tenantId = (int) $request->user()->tenant_id;

        $validated = $request->validate([
            'sexo' => ['nullable', 'string', 'in:masculino,feminino,outro,nao_informado'],
            'bairro' => ['nullable', 'string', 'max:191'],
            'course_id' => ['nullable', 'integer'],
            'course_class_id' => ['nullable', 'integer'],
            'enrollment_status' => ['nullable', 'string', 'in:em_andamento,desistido,concluido'],
        ]);

        $courseId = isset($validated['course_id']) && (int) $validated['course_id'] > 0
            ? (int) $validated['course_id']
            : null;
        $courseClassId = isset($validated['course_class_id']) && (int) $validated['course_class_id'] > 0
            ? (int) $validated['course_class_id']
            : null;

        if ($courseId !== null && $courseId > 0) {
            $exists = Course::query()->whereKey($courseId)->where('tenant_id', $tenantId)->exists();
            abort_unless($exists, 404);
        }
        if ($courseClassId !== null && $courseClassId > 0) {
            $exists = CourseClass::query()->whereKey($courseClassId)->where('tenant_id', $tenantId)->exists();
            abort_unless($exists, 404);
        }

        $query = Student::query()
            ->with([
                'user:id,name,email',
                'enrollments' => function ($q): void {
                    $q->whereNotNull('course_class_id')
                        ->with([
                            'courseClass' => function ($cc): void {
                                $cc->select('id', 'name', 'course_id', 'status')
                                    ->with(['course:id,name']);
                            },
                        ]);
                },
            ])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (! empty($validated['sexo'])) {
            $query->where('sexo', $validated['sexo']);
        }

        if (! empty($validated['bairro'])) {
            $query->where('bairro', $validated['bairro']);
        }

        $this->applyEnrollmentScope($query, $validated['enrollment_status'] ?? null, $courseId, $courseClassId);

        $students = $query->orderBy('enrollment_number')->get();

        $skipped = (int) Student::query()
            ->when(! empty($validated['sexo']), fn (Builder $q) => $q->where('sexo', $validated['sexo']))
            ->when(! empty($validated['bairro']), fn (Builder $q) => $q->where('bairro', $validated['bairro']))
            ->tap(fn (Builder $q) => $this->applyEnrollmentScope($q, $validated['enrollment_status'] ?? null, $courseId, $courseClassId))
            ->where(function (Builder $q): void {
                $q->whereNull('latitude')->orWhereNull('longitude');
            })
            ->count();

        $markers = $students->map(function (Student $student) {
            $enrollments = $student->enrollments->map(function ($e) {
                $turma = $e->courseClass;

                return [
                    'status' => $e->status,
                    'turma' => $turma?->name,
                    'curso' => $turma?->course?->name,
                    'turma_status' => $turma?->status,
                ];
            })->values()->all();

            return [
                'id' => $student->id,
                'name' => (string) ($student->user?->name ?? '—'),
                'email' => (string) ($student->user?->email ?? $student->email ?? ''),
                'lat' => (float) $student->latitude,
                'lng' => (float) $student->longitude,
                'bairro' => $student->bairro,
                'sexo' => $student->sexo,
                'matricula' => $student->enrollment_number,
                'enrollments' => $enrollments,
            ];
        })->values()->all();

        return response()->json([
            'markers' => $markers,
            'skipped_no_coords' => $skipped,
            'total_on_map' => count($markers),
        ]);
    }

    private function applyEnrollmentScope(
        Builder $query,
        ?string $enrollmentStatus,
        ?int $courseId,
        ?int $courseClassId,
    ): void {
        $hasCourseFilter = $courseId !== null && $courseId > 0;
        $hasClassFilter = $courseClassId !== null && $courseClassId > 0;

        if ($enrollmentStatus === null || $enrollmentStatus === '') {
            if ($hasClassFilter) {
                $query->whereHas('enrollments', function (Builder $e) use ($courseClassId): void {
                    $e->where('course_class_id', $courseClassId);
                });

                return;
            }
            if ($hasCourseFilter) {
                $query->whereHas('enrollments', function (Builder $e) use ($courseId): void {
                    $e->whereHas('courseClass', fn (Builder $cc) => $cc->where('course_id', $courseId));
                });
            }

            return;
        }

        if ($enrollmentStatus === 'desistido') {
            $query->whereHas('enrollments', function (Builder $e) use ($courseId, $courseClassId): void {
                $e->where('status', 'desistido')
                    ->when($courseClassId, fn (Builder $x) => $x->where('course_class_id', $courseClassId))
                    ->when($courseId && ! $courseClassId, function (Builder $x) use ($courseId): void {
                        $x->whereHas('courseClass', fn (Builder $cc) => $cc->where('course_id', $courseId));
                    });
            });

            return;
        }

        if ($enrollmentStatus === 'concluido') {
            $query->whereHas('enrollments', function (Builder $e) use ($courseId, $courseClassId): void {
                $e->where('status', 'concluido')
                    ->when($courseClassId, fn (Builder $x) => $x->where('course_class_id', $courseClassId))
                    ->when($courseId && ! $courseClassId, function (Builder $x) use ($courseId): void {
                        $x->whereHas('courseClass', fn (Builder $cc) => $cc->where('course_id', $courseId));
                    });
            });

            return;
        }

        if ($enrollmentStatus === 'em_andamento') {
            $query->whereHas('enrollments', function (Builder $e) use ($courseId, $courseClassId): void {
                $e->whereIn('status', ['inscrito', 'cursando'])
                    ->whereHas('courseClass', function (Builder $cc): void {
                        $cc->where('status', '!=', 'cancelado');
                    })
                    ->when($courseClassId, fn (Builder $x) => $x->where('course_class_id', $courseClassId))
                    ->when($courseId && ! $courseClassId, function (Builder $x) use ($courseId): void {
                        $x->whereHas('courseClass', fn (Builder $cc) => $cc->where('course_id', $courseId));
                    });
            });
        }
    }
}
