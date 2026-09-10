<?php

namespace App\Http\Controllers\Director;

use App\Enums\CatalogItemTipo;
use App\Enums\CatalogLicenseModalidade;
use App\Http\Controllers\Controller;
use App\Models\CatalogLicense;
use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Models\Event;
use App\Scopes\TenantScope;
use App\Support\DirectorContext;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Agenda regional: palestras (evento) e turmas de curso liberadas pelo diretor.
 */
class AgendaController extends Controller
{
    private const COR_EVENTO = '#16a34a';

    private const COR_EVENTO_BORDA = '#15803d';

    private const COR_EVENTO_PLANEJADO = '#38bdf8';

    private const COR_EVENTO_PLANEJADO_BORDA = '#0284c7';

    private const COR_CURSO = '#ea580c';

    private const COR_CURSO_BORDA = '#c2410c';

    public function index(): View
    {
        $this->authorize('viewAny', CatalogLicense::class);

        $directorId = (int) request()->user()->id;
        $tenantIds = DirectorContext::tenantIds();
        $items = $this->collectItems($directorId, $tenantIds, null, null);
        $conflitos = $this->detectConflicts($items);

        return view('director.agenda.index', [
            'conflitos' => $conflitos,
        ]);
    }

    public function events(Request $request): JsonResponse
    {
        $this->authorize('viewAny', CatalogLicense::class);

        $directorId = (int) $request->user()->id;
        $tenantIds = DirectorContext::tenantIds();
        $start = $request->date('start')?->startOfDay();
        $end = $request->date('end')?->endOfDay();

        $conflictIds = $this->conflictItemIds(
            $this->collectItems($directorId, $tenantIds, null, null)
        );

        return response()->json(
            $this->collectItems($directorId, $tenantIds, $start, $end)
                ->map(function (array $item) use ($conflictIds) {
                    $hasConflict = $conflictIds->contains($item['id']);
                    if ($hasConflict) {
                        $item['extendedProps']['conflito'] = true;
                        $item['extendedProps']['detalhe'] = trim(
                            ($item['extendedProps']['detalhe'] ?? '').' · Conflito presencial no mesmo dia'
                        );
                        $item['classNames'] = array_values(array_unique([
                            ...($item['classNames'] ?? []),
                            'agenda-card--conflito',
                        ]));
                    }

                    return $item;
                })
                ->values()
        );
    }

    public function exportIcs(Request $request): Response
    {
        $this->authorize('viewAny', CatalogLicense::class);

        $directorId = (int) $request->user()->id;
        $tenantIds = DirectorContext::tenantIds();
        $start = CarbonImmutable::now()->subMonth()->startOfDay();
        $end = CarbonImmutable::now()->addYear()->endOfDay();

        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Legiscola//Agenda Diretor//PT',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Agenda Diretor Regional',
        ];

        foreach ($this->collectItems($directorId, $tenantIds, $start, $end) as $item) {
            $startAt = CarbonImmutable::parse($item['start']);
            $uid = $item['id'].'@legiscola';
            $summary = $this->icsEscape($item['title'] ?? 'Agenda');
            $camara = $item['extendedProps']['camara'] ?? '';
            $modalidade = $item['extendedProps']['modalidade'] ?? '';
            $detalhe = $item['extendedProps']['detalhe'] ?? '';
            $description = $this->icsEscape(implode(' · ', array_filter([$camara, $modalidade, $detalhe])));
            $url = $this->icsEscape($item['url'] ?? '');

            $lines[] = 'BEGIN:VEVENT';
            $lines[] = 'UID:'.$uid;
            $lines[] = 'DTSTAMP:'.CarbonImmutable::now('UTC')->format('Ymd\THis\Z');
            $lines[] = 'DTSTART:'.$startAt->utc()->format('Ymd\THis\Z');
            $lines[] = 'DTEND:'.$startAt->utc()->addHour()->format('Ymd\THis\Z');
            $lines[] = 'SUMMARY:'.$summary;
            if ($description !== '') {
                $lines[] = 'DESCRIPTION:'.$description;
            }
            if ($url !== '') {
                $lines[] = 'URL:'.$url;
            }
            $lines[] = 'END:VEVENT';
        }

        $lines[] = 'END:VCALENDAR';

        return response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="agenda-diretor.ics"',
        ]);
    }

    /**
     * @param  list<int>  $tenantIds
     * @return Collection<int, array<string, mixed>>
     */
    private function collectItems(int $directorId, array $tenantIds, $start, $end): Collection
    {
        return $this->eventosConfirmados($directorId, $tenantIds, $start, $end)
            ->concat($this->palestrasPlanejadas($directorId, $tenantIds, $start, $end))
            ->concat($this->turmasDeCurso($directorId, $tenantIds, $start, $end))
            ->values();
    }

    /**
     * Presenciais no mesmo dia (qualquer câmara da região).
     *
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, array{data: string, data_label: string, itens: list<array{id: string, titulo: string, camara: string}>}>
     */
    private function detectConflicts(Collection $items): Collection
    {
        $presenciais = $items->filter(fn (array $item) => $this->isPresencial($item));

        return $presenciais
            ->groupBy(fn (array $item) => CarbonImmutable::parse($item['start'])->toDateString())
            ->filter(fn (Collection $group) => $group->count() > 1)
            ->map(function (Collection $group, string $date) {
                return [
                    'data' => $date,
                    'data_label' => CarbonImmutable::parse($date)->locale('pt_BR')->translatedFormat('d/m/Y (l)'),
                    'itens' => $group->map(fn (array $item) => [
                        'id' => $item['id'],
                        'titulo' => $item['title'],
                        'camara' => $item['extendedProps']['camara'] ?? '—',
                        'url' => $item['url'] ?? null,
                    ])->values()->all(),
                ];
            })
            ->sortBy('data')
            ->values();
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @return Collection<int, string>
     */
    private function conflictItemIds(Collection $items): Collection
    {
        return $this->detectConflicts($items)
            ->flatMap(fn (array $c) => collect($c['itens'])->pluck('id'))
            ->unique()
            ->values();
    }

    /**
     * @param  array<string, mixed>  $item
     */
    private function isPresencial(array $item): bool
    {
        $modalidade = mb_strtolower((string) ($item['extendedProps']['modalidade'] ?? ''));

        return str_contains($modalidade, 'presencial');
    }

    private function icsEscape(string $value): string
    {
        return str_replace(
            ['\\', ';', ',', "\n", "\r"],
            ['\\\\', '\\;', '\\,', '\\n', ''],
            $value
        );
    }

    /**
     * @param  list<int>  $tenantIds
     * @return Collection<int, array<string, mixed>>
     */
    private function eventosConfirmados(int $directorId, array $tenantIds, $start, $end): Collection
    {
        return Event::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->whereIn('tenant_id', $tenantIds)
            ->whereNotNull('catalog_license_id')
            ->whereHas('catalogLicense', fn ($q) => $q->where('director_user_id', $directorId))
            ->with(['tenant', 'catalogItem', 'catalogLicense'])
            ->when($start && $end, fn ($q) => $q->whereBetween('date_time', [$start, $end]))
            ->get()
            ->map(function (Event $evento) {
                return [
                    'id' => 'event-'.$evento->id,
                    'title' => $evento->title,
                    'start' => $evento->date_time->toIso8601String(),
                    'url' => route('diretor.licencas.edit', $evento->catalog_license_id),
                    'backgroundColor' => self::COR_EVENTO,
                    'borderColor' => self::COR_EVENTO_BORDA,
                    'textColor' => '#ffffff',
                    'classNames' => ['agenda-card', 'agenda-card--evento'],
                    'extendedProps' => [
                        'categoria' => 'evento',
                        'badge' => 'Evento',
                        'tipo' => 'confirmado',
                        'camara' => $evento->tenant?->display_name,
                        'modalidade' => $evento->catalogLicense?->modalidade?->label()
                            ?? CatalogLicenseModalidade::Presencial->label(),
                        'professor' => $evento->palestrante_nome ?: $evento->catalogLicense?->professor_nome,
                        'detalhe' => 'Palestra confirmada pela câmara',
                    ],
                ];
            });
    }

    /**
     * @param  list<int>  $tenantIds
     * @return Collection<int, array<string, mixed>>
     */
    private function palestrasPlanejadas(int $directorId, array $tenantIds, $start, $end): Collection
    {
        return CatalogLicense::query()
            ->where('director_user_id', $directorId)
            ->whereIn('tenant_id', $tenantIds)
            ->whereNotNull('palestra_em')
            ->whereDoesntHave('events')
            ->whereHas('catalogItem', fn ($q) => $q->where('tipo', CatalogItemTipo::Palestra))
            ->with(['catalogItem', 'tenant'])
            ->when($start && $end, fn ($q) => $q->whereBetween('palestra_em', [$start, $end]))
            ->get()
            ->map(function (CatalogLicense $licenca) {
                return [
                    'id' => 'license-'.$licenca->id,
                    'title' => $licenca->catalogItem->titulo,
                    'start' => $licenca->palestra_em->toIso8601String(),
                    'url' => route('diretor.licencas.edit', $licenca),
                    'backgroundColor' => self::COR_EVENTO_PLANEJADO,
                    'borderColor' => self::COR_EVENTO_PLANEJADO_BORDA,
                    'textColor' => '#0f172a',
                    'classNames' => ['agenda-card', 'agenda-card--evento-planejado'],
                    'extendedProps' => [
                        'categoria' => 'evento',
                        'badge' => 'Evento',
                        'tipo' => 'planejada',
                        'camara' => $licenca->tenant?->display_name,
                        'modalidade' => $licenca->modalidade?->label() ?? '—',
                        'professor' => $licenca->professor_nome,
                        'detalhe' => 'Agendada na licença — aguardando a câmara confirmar',
                    ],
                ];
            });
    }

    /**
     * Turmas abertas a partir de cursos do catálogo.
     *
     * @param  list<int>  $tenantIds
     * @return Collection<int, array<string, mixed>>
     */
    private function turmasDeCurso(int $directorId, array $tenantIds, $start, $end): Collection
    {
        $turmas = CourseClass::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->whereIn('tenant_id', $tenantIds)
            ->whereHas('course', function ($q) use ($directorId) {
                $q->withoutGlobalScopes([TenantScope::class])
                    ->whereNotNull('catalog_license_id')
                    ->whereHas('catalogLicense', fn ($lq) => $lq->where('director_user_id', $directorId)
                        ->whereHas('catalogItem', fn ($iq) => $iq->where('tipo', CatalogItemTipo::Curso)));
            })
            ->with(['tenant', 'course.catalogItem', 'course.catalogLicense'])
            ->get();

        return $turmas
            ->map(function (CourseClass $turma) {
                $inicio = $this->inicioDaTurma($turma);
                if (! $inicio) {
                    return null;
                }

                return [
                    'id' => 'turma-'.$turma->id,
                    'title' => $turma->name ?: ($turma->course?->name ?? 'Turma'),
                    'start' => $inicio->toIso8601String(),
                    'url' => route('diretor.licencas.edit', $turma->course->catalog_license_id),
                    'backgroundColor' => self::COR_CURSO,
                    'borderColor' => self::COR_CURSO_BORDA,
                    'textColor' => '#ffffff',
                    'classNames' => ['agenda-card', 'agenda-card--curso'],
                    'extendedProps' => [
                        'categoria' => 'curso',
                        'badge' => 'Curso',
                        'tipo' => 'turma',
                        'camara' => $turma->tenant?->display_name,
                        'modalidade' => $turma->tipo_turma ? ucfirst($turma->tipo_turma) : '—',
                        'professor' => $turma->course?->catalogLicense?->professor_nome,
                        'detalhe' => 'Turma aberta pela câmara',
                    ],
                ];
            })
            ->filter()
            ->when($start && $end, function (Collection $items) use ($start, $end) {
                return $items->filter(function (array $item) use ($start, $end) {
                    $at = CarbonImmutable::parse($item['start']);

                    return $at->betweenIncluded($start, $end);
                });
            })
            ->values();
    }

    private function inicioDaTurma(CourseClass $turma): ?CarbonImmutable
    {
        $dataAula = ClassLesson::query()
            ->withoutGlobalScopes([TenantScope::class])
            ->where('course_class_id', $turma->id)
            ->orderBy('date')
            ->orderBy('start_time')
            ->value('date');

        if ($dataAula) {
            return CarbonImmutable::parse($dataAula);
        }

        if ($turma->enrollment_start) {
            return CarbonImmutable::parse($turma->enrollment_start);
        }

        return null;
    }
}
