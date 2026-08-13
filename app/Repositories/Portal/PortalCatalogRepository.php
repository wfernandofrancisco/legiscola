<?php

namespace App\Repositories\Portal;

use App\Contracts\Repositories\Portal\PortalCatalogRepositoryInterface;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Event;
use App\Models\Noticia;
use App\Models\ProfessorCredenciamento;
use App\Models\SobreEscola;
use App\Models\Student;
use App\Models\Teacher;
use App\Support\TenantContext;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PortalCatalogRepository implements PortalCatalogRepositoryInterface
{
    public function latestPublishedNews(int $limit): Collection
    {
        return Noticia::query()
            ->where('ativo', true)
            ->where(function ($q): void {
                $q->whereNull('publicar_em')
                    ->orWhere('publicar_em', '<=', now());
            })
            ->with(['user', 'fotos'])
            ->latest('publicar_em')
            ->latest('id')
            ->limit($limit)
            ->get();
    }

    public function paginatePublishedNews(int $perPage): LengthAwarePaginator
    {
        return Noticia::query()
            ->where('ativo', true)
            ->where(function ($q): void {
                $q->whereNull('publicar_em')
                    ->orWhere('publicar_em', '<=', now());
            })
            ->with(['user'])
            ->latest('publicar_em')
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedNewsBySlug(string $slug): ?Noticia
    {
        return Noticia::query()
            ->where('ativo', true)
            ->where('slug', $slug)
            ->where(function ($q): void {
                $q->whereNull('publicar_em')
                    ->orWhere('publicar_em', '<=', now());
            })
            ->with(['user', 'fotos'])
            ->first();
    }

    public function upcomingEvents(int $limit): Collection
    {
        $now = CarbonImmutable::now();

        return Event::query()
            ->where('date_time', '>=', $now)
            ->latest('date_time')
            ->limit($limit)
            ->get();
    }

    public function paginateEvents(int $perPage): LengthAwarePaginator
    {
        return Event::query()
            ->latest('date_time')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findEvent(int $id): ?Event
    {
        return Event::query()->find($id);
    }

    public function paginateActiveCredenciamentos(int $perPage, ?string $pageName = null): LengthAwarePaginator
    {
        return ProfessorCredenciamento::query()
            ->where('is_active', true)
            ->with('anexos')
            ->latest('ano_referencia')
            ->latest('id')
            ->paginate($perPage, ['*'], $pageName ?? 'page')
            ->withQueryString();
    }

    public function paginateTeachers(int $perPage, ?string $pageName = null): LengthAwarePaginator
    {
        return Teacher::query()
            ->where('status', 'ativo')
            ->with('user')
            ->orderBy('full_name')
            ->paginate($perPage, ['*'], $pageName ?? 'page')
            ->withQueryString();
    }

    public function featuredTeachers(int $limit): Collection
    {
        return Teacher::query()
            ->where('status', 'ativo')
            ->with('user')
            ->orderBy('full_name')
            ->limit($limit)
            ->get();
    }

    public function firstSobreEscola(): ?SobreEscola
    {
        return SobreEscola::query()
            ->with(['eixos', 'pessoas'])
            ->orderBy('id')
            ->first();
    }

    public function homeEnrollmentTurmas(int $limit): Collection
    {
        return CourseClass::query()
            ->where('status', 'inscricao')
            ->with(['course.admin', 'teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
            ->withCount([
                'enrollments as matriculas_count' => fn ($q) => $q->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->orderByDesc('enrollment_end')
            ->limit($limit)
            ->get();
    }

    public function homeEmAndamentoTurmas(int $limit): Collection
    {
        return CourseClass::query()
            ->where('status', 'em_andamento')
            ->with(['course.admin', 'teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
            ->withCount([
                'enrollments as matriculas_count' => fn ($q) => $q->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function coursesWithOfferingsPaginated(int $perPage): LengthAwarePaginator
    {
        return Course::query()
            ->where(function ($q): void {
                $q->where('status', 'ativo')
                    ->orWhereHas('courseClasses', fn ($cc) => $cc->where('status', '!=', 'cancelado'));
            })
            ->with([
                'admin',
                'courseClasses' => fn ($q) => $q
                    ->with(['teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
                    ->withCount([
                        'enrollments as matriculas_count' => fn ($enr) => $enr->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
                    ])
                    ->where('status', '!=', 'cancelado')
                    ->orderByRaw("FIELD(status,'cadastrado','inscricao','em_andamento','concluido')"),
            ])
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function concludedCourseClassesPaginated(int $perPage): LengthAwarePaginator
    {
        return CourseClass::query()
            ->where('status', 'concluido')
            ->with(['course.admin', 'teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
            ->withCount([
                'enrollments as matriculas_count' => fn ($enr) => $enr->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
            ])
            ->latest('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findCourseForPortal(int $id): ?Course
    {
        return Course::query()
            ->where(function ($q): void {
                $q->where('status', 'ativo')
                    ->orWhereHas('courseClasses', fn ($cc) => $cc->where('status', '!=', 'cancelado'));
            })
            ->with([
                'admin',
                'curricula',
                'courseClasses' => fn ($q) => $q
                    ->with(['schedules', 'teachers' => fn ($t) => $t->orderBy('course_class_teacher.sort_order')])
                    ->withCount([
                        'enrollments as matriculas_count' => fn ($enr) => $enr->whereIn('status', ['inscrito', 'cursando', 'concluido', 'baixa_presenca']),
                    ])
                    ->where('status', '!=', 'cancelado')
                    ->orderByRaw("FIELD(status,'cadastrado','inscricao','em_andamento','concluido')"),
            ])
            ->find($id);
    }

    public function relatedActiveCourses(int $excludeCourseId, int $limit): Collection
    {
        return Course::query()
            ->where('status', 'ativo')
            ->whereKeyNot($excludeCourseId)
            ->with('admin')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }

    public function portalStats(): array
    {
        $tenantId = TenantContext::getTenantId();

        return [
            'alunos' => (int) Student::query()->count(),
            'cursos' => (int) Course::query()->where('status', 'ativo')->count(),
            'eventos_futuros' => (int) Event::query()
                ->where('date_time', '>=', CarbonImmutable::now())
                ->count(),
            'turmas_ativas' => (int) CourseClass::query()
                ->whereIn('status', ['inscricao', 'em_andamento', 'cadastrado'])
                ->count(),
        ];
    }
}
