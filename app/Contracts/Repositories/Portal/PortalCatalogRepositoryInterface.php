<?php

namespace App\Contracts\Repositories\Portal;

use App\Models\Course;
use App\Models\Event;
use App\Models\Noticia;
use App\Models\SobreEscola;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface PortalCatalogRepositoryInterface
{
    /** @return \Illuminate\Support\Collection<int, \App\Models\Noticia> */
    public function latestPublishedNews(int $limit): Collection;

    public function paginatePublishedNews(int $perPage): LengthAwarePaginator;

    public function findPublishedNewsBySlug(string $slug): ?Noticia;

    /** @return \Illuminate\Support\Collection<int, \App\Models\Event> */
    public function upcomingEvents(int $limit): Collection;

    public function paginateEvents(int $perPage): LengthAwarePaginator;

    public function findEvent(int $id): ?Event;

    public function paginateActiveCredenciamentos(int $perPage, ?string $pageName = null): LengthAwarePaginator;

    public function paginateTeachers(int $perPage, ?string $pageName = null): LengthAwarePaginator;

    /** @return \Illuminate\Support\Collection<int, \App\Models\Teacher> */
    public function featuredTeachers(int $limit): Collection;

    public function firstSobreEscola(): ?SobreEscola;

    /** Turmas em inscrição (limite para home). */
    public function homeEnrollmentTurmas(int $limit): Collection;

    /** Turmas em andamento (limite para home). */
    public function homeEmAndamentoTurmas(int $limit): Collection;

    /** Cursos «ativos» com ao menos uma turma não cancelada. */
    public function coursesWithOfferingsPaginated(int $perPage): LengthAwarePaginator;

    /** Histórico: turmas concluídas. */
    public function concludedCourseClassesPaginated(int $perPage): LengthAwarePaginator;

    public function findCourseForPortal(int $id): ?Course;

    /** @return \Illuminate\Support\Collection<int, \App\Models\Course> */
    public function relatedActiveCourses(int $excludeCourseId, int $limit): Collection;

    /**
     * @return array{alunos:int,cursos:int,eventos_futuros:int,turmas_ativas:int}
     */
    public function portalStats(): array;
}
