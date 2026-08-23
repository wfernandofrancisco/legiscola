<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlunoBairro;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Student;
use App\Support\NominatimGeocoder;
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

        $mapCenter = [
            'lat' => (float) config('map.default_latitude'),
            'lng' => (float) config('map.default_longitude'),
            'zoom' => (int) config('map.default_zoom'),
            'city' => (string) config('map.default_city'),
            'uf' => (string) config('map.default_uf'),
        ];

        return view('admin.students.geolocation', compact('breadcrumbs', 'bairros', 'cursos', 'turmas', 'mapCenter'));
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
            ->where(function (Builder $q): void {
                $q->where(function (Builder $inner): void {
                    $inner->whereNotNull('latitude')->whereNotNull('longitude');
                })->orWhere(function (Builder $inner): void {
                    $inner->whereNotNull('cidade')->where('cidade', '!=', '');
                });
            });

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
                $q->where(function (Builder $inner): void {
                    $inner->whereNull('latitude')->orWhereNull('longitude');
                })->where(function (Builder $inner): void {
                    $inner->whereNull('cidade')->orWhere('cidade', '');
                });
            })
            ->count();

        $cityCache = [];
        $markers = $students->map(function (Student $student) use (&$cityCache) {
            $coords = $this->resolveStudentCoordinates($student, $cityCache);
            if ($coords === null) {
                return null;
            }

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
                'lat' => $coords['latitude'],
                'lng' => $coords['longitude'],
                'bairro' => $student->bairro,
                'cidade' => $student->cidade,
                'sexo' => $student->sexo,
                'matricula' => $student->enrollment_number,
                'enrollments' => $enrollments,
            ];
        })->filter()->values()->all();

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

    /**
     * @param  array<string, array{latitude: float, longitude: float}|null>  $cityCache
     * @return array{latitude: float, longitude: float}|null
     */
    private function resolveStudentCoordinates(Student $student, array &$cityCache): ?array
    {
        if ($student->latitude !== null && $student->longitude !== null) {
            return [
                'latitude' => (float) $student->latitude,
                'longitude' => (float) $student->longitude,
            ];
        }

        $cidade = trim((string) $student->cidade);
        if ($cidade === '') {
            return null;
        }

        $uf = strtoupper(trim((string) ($student->uf ?: config('map.default_uf'))));
        $cacheKey = mb_strtolower($cidade).'|'.$uf;

        if (! array_key_exists($cacheKey, $cityCache)) {
            $cityCache[$cacheKey] = $this->coordinatesForCity($cidade, $uf);
        }

        $coords = $cityCache[$cacheKey];
        if ($coords === null) {
            return null;
        }

        $jitterLat = (($student->id % 7) - 3) * 0.00045;
        $jitterLng = (($student->id % 5) - 2) * 0.00045;

        return [
            'latitude' => $coords['latitude'] + $jitterLat,
            'longitude' => $coords['longitude'] + $jitterLng,
        ];
    }

    /**
     * @return array{latitude: float, longitude: float}|null
     */
    private function coordinatesForCity(string $cidade, string $uf): ?array
    {
        $defaultCity = mb_strtolower(trim((string) config('map.default_city')));
        $defaultUf = strtoupper(trim((string) config('map.default_uf')));

        if (mb_strtolower($cidade) === $defaultCity && ($uf === '' || $uf === $defaultUf)) {
            return [
                'latitude' => (float) config('map.default_latitude'),
                'longitude' => (float) config('map.default_longitude'),
            ];
        }

        return NominatimGeocoder::geocode([
            'cidade' => $cidade,
            'uf' => $uf !== '' ? $uf : $defaultUf,
        ]);
    }
}
