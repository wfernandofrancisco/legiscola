<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Enrollment;
use App\Models\Event;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Tenant;
use App\Scopes\TenantScope;
use App\Support\BrazilianStates;
use App\Support\DirectorContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Única porta de entrada para dado de cliente na área do diretor.
 *
 * A área roda sem TenantContext, então nenhuma query aqui pode confiar no TenantScope:
 * todo builder remove o escopo global e aplica explicitamente os tenants da abrangência.
 * Abrangência vazia produz `whereIn(..., [])`, que não retorna nada — falha fechada.
 *
 * Agregados apenas: nenhum método expõe dado pessoal de aluno.
 */
class DirectorInsightsService
{
    /** Turmas que contam como "em andamento" no painel. */
    private const STATUS_EM_ANDAMENTO = 'em_andamento';

    private const STATUS_INSCRICAO = 'inscricao';

    private const STATUS_CONCLUIDO = 'concluido';

    /**
     * Câmaras da abrangência, cada uma com seus números.
     */
    public function perTenant(): Collection
    {
        $tenants = DirectorContext::tenants()
            ->select(['id', 'name', 'nome_fantasia', 'razao_social', 'cidade', 'estado', 'status'])
            ->orderBy('estado')
            ->orderBy('name')
            ->get();

        if ($tenants->isEmpty()) {
            return collect();
        }

        $cursos = $this->countByTenant(Course::class);
        $alunos = $this->countByTenant(Student::class);
        $professores = $this->countByTenant(Teacher::class);
        $matriculas = $this->countByTenant(Enrollment::class);
        $turmasPorStatus = $this->classesByTenantAndStatus();
        $eventosFuturos = $this->upcomingEventsByTenant();

        return $tenants->map(function (Tenant $tenant) use (
            $cursos,
            $alunos,
            $professores,
            $matriculas,
            $turmasPorStatus,
            $eventosFuturos
        ): array {
            $status = $turmasPorStatus->get($tenant->id, collect());

            return [
                'tenant' => $tenant,
                'cursos' => (int) $cursos->get($tenant->id, 0),
                'alunos' => (int) $alunos->get($tenant->id, 0),
                'professores' => (int) $professores->get($tenant->id, 0),
                'matriculas' => (int) $matriculas->get($tenant->id, 0),
                'turmas_total' => (int) $status->sum(),
                'turmas_inscricao' => (int) $status->get(self::STATUS_INSCRICAO, 0),
                'turmas_em_andamento' => (int) $status->get(self::STATUS_EM_ANDAMENTO, 0),
                'turmas_concluidas' => (int) $status->get(self::STATUS_CONCLUIDO, 0),
                'eventos_futuros' => (int) $eventosFuturos->get($tenant->id, 0),
            ];
        });
    }

    /**
     * Mesmos números das câmaras, agrupados por UF.
     */
    public function perUf(?Collection $rows = null): Collection
    {
        $rows ??= $this->perTenant();

        return $rows
            ->groupBy(fn (array $row): string => (string) $row['tenant']->estado)
            ->map(fn (Collection $group, string $uf): array => [
                'uf' => $uf,
                'estado' => BrazilianStates::name($uf) ?? $uf,
                'camaras' => $group->count(),
                'cursos' => (int) $group->sum('cursos'),
                'alunos' => (int) $group->sum('alunos'),
                'professores' => (int) $group->sum('professores'),
                'matriculas' => (int) $group->sum('matriculas'),
                'turmas_inscricao' => (int) $group->sum('turmas_inscricao'),
                'turmas_em_andamento' => (int) $group->sum('turmas_em_andamento'),
                'turmas_concluidas' => (int) $group->sum('turmas_concluidas'),
                'eventos_futuros' => (int) $group->sum('eventos_futuros'),
                'linhas' => $group->values(),
            ])
            ->sortKeys()
            ->values();
    }

    /**
     * Totais consolidados da região inteira.
     *
     * @return array<string, int>
     */
    public function summary(?Collection $rows = null): array
    {
        $rows ??= $this->perTenant();

        return [
            'camaras' => $rows->count(),
            'cursos' => (int) $rows->sum('cursos'),
            'alunos' => (int) $rows->sum('alunos'),
            'professores' => (int) $rows->sum('professores'),
            'matriculas' => (int) $rows->sum('matriculas'),
            'turmas_inscricao' => (int) $rows->sum('turmas_inscricao'),
            'turmas_em_andamento' => (int) $rows->sum('turmas_em_andamento'),
            'turmas_concluidas' => (int) $rows->sum('turmas_concluidas'),
            'eventos_futuros' => (int) $rows->sum('eventos_futuros'),
        ];
    }

    /**
     * Cursos com mais matrículas na região.
     *
     * Não existe tracking de acesso no sistema; matrícula é a métrica de procura disponível.
     */
    public function topCourses(int $limit = 8, ?Tenant $tenant = null): Collection
    {
        $tenantIds = $tenant ? [$tenant->id] : $this->tenantIds();

        if ($tenantIds === []) {
            return collect();
        }

        return Enrollment::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->join('course_classes', 'course_classes.id', '=', 'enrollments.course_class_id')
            ->join('courses', 'courses.id', '=', 'course_classes.course_id')
            ->join('tenants', 'tenants.id', '=', 'enrollments.tenant_id')
            ->whereIn('enrollments.tenant_id', $tenantIds)
            ->selectRaw('courses.id as course_id, courses.name as curso, tenants.name as camara, tenants.estado as uf, COUNT(*) as matriculas')
            ->groupBy('courses.id', 'courses.name', 'tenants.name', 'tenants.estado')
            ->orderByDesc('matriculas')
            ->limit($limit)
            ->get();
    }

    /**
     * Ficha de uma câmara. Lança 404 se ela estiver fora da abrangência.
     *
     * @return array<string, mixed>
     */
    public function tenantDetail(Tenant $tenant): array
    {
        abort_unless(DirectorContext::allows($tenant->id), 404);

        $row = $this->perTenant()->firstWhere(fn (array $r): bool => $r['tenant']->id === $tenant->id);

        $turmasRecentes = CourseClass::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->where('course_classes.tenant_id', $tenant->id)
            ->with(['course' => fn ($q) => $q->withoutGlobalScopes([TenantScope::class])])
            ->withCount('enrollments')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return [
            'tenant' => $tenant,
            'numeros' => $row ?? [],
            'turmas_recentes' => $turmasRecentes,
            'top_cursos' => $this->topCourses(5, $tenant),
        ];
    }

    /**
     * @return list<int>
     */
    private function tenantIds(): array
    {
        return DirectorContext::tenantIds();
    }

    /**
     * Builder já restrito à abrangência, sem depender do TenantScope.
     *
     * @param  class-string<Model>  $modelClass
     */
    private function scoped(string $modelClass): Builder
    {
        $table = (new $modelClass)->getTable();

        return $modelClass::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->whereIn($table.'.tenant_id', $this->tenantIds());
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Collection<int, int>
     */
    private function countByTenant(string $modelClass): Collection
    {
        $table = (new $modelClass)->getTable();

        return $this->scoped($modelClass)
            ->selectRaw($table.'.tenant_id as tenant_id, COUNT(*) as total')
            ->groupBy($table.'.tenant_id')
            ->pluck('total', 'tenant_id');
    }

    /**
     * @return Collection<int, Collection<string, int>>
     */
    private function classesByTenantAndStatus(): Collection
    {
        return $this->scoped(CourseClass::class)
            ->selectRaw('course_classes.tenant_id as tenant_id, course_classes.status as status, COUNT(*) as total')
            ->groupBy('course_classes.tenant_id', 'course_classes.status')
            ->get()
            ->groupBy('tenant_id')
            ->map(fn (Collection $rows): Collection => $rows->pluck('total', 'status'));
    }

    /**
     * @return Collection<int, int>
     */
    private function upcomingEventsByTenant(): Collection
    {
        return $this->scoped(Event::class)
            ->where('events.date_time', '>=', now())
            ->selectRaw('events.tenant_id as tenant_id, COUNT(*) as total')
            ->groupBy('events.tenant_id')
            ->pluck('total', 'tenant_id');
    }
}
