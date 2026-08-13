<?php

namespace App\Services\Portal;

use App\Models\ClassLesson;
use App\Models\CourseClass;
use App\Models\Event;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

final class PortalAgendaService
{
    /**
     * @return array{
     *     monthStart: CarbonImmutable,
     *     monthEnd: CarbonImmutable,
     *     monthLabel: string,
     *     prevParam: string,
     *     nextParam: string,
     *     weeks: list<list<array{date: CarbonImmutable, inMonth: bool, items: list<array<string, mixed>>}>>,
     * }
     */
    public function calendarData(CarbonImmutable $month): array
    {
        $monthStart = $month->startOfMonth();
        $monthEnd = $month->endOfMonth();
        $prev = $monthStart->copy()->subMonth();
        $next = $monthStart->copy()->addMonth();

        /** @var array<string, list<array<string, mixed>>> */
        $itemsByDate = [];

        $addItem = function (string $dateKey, array $item) use (&$itemsByDate): void {
            if (! isset($itemsByDate[$dateKey])) {
                $itemsByDate[$dateKey] = [];
            }
            $itemsByDate[$dateKey][] = $item;
        };

        $events = Event::query()
            ->where('date_time', '>=', $monthStart->startOfDay())
            ->where('date_time', '<=', $monthEnd->endOfDay())
            ->orderBy('date_time')
            ->get();

        foreach ($events as $ev) {
            $key = $ev->date_time->toDateString();
            $addItem($key, [
                'kind' => 'event',
                'id' => $ev->id,
                'title' => $ev->title,
                'subtitle' => 'Evento',
                'timeLabel' => $ev->date_time->format('H:i'),
                'sort' => (int) $ev->date_time->format('Hi'),
                'href' => route('portal.eventos.show', ['evento' => $ev->id]),
            ]);
        }

        $turmas = CourseClass::query()
            ->where('status', '!=', 'cancelado')
            ->with(['schedules', 'course'])
            ->get();

        $ids = $turmas->pluck('id')->all();
        $bounds = collect();
        if ($ids !== []) {
            $bounds = ClassLesson::query()
                ->selectRaw('course_class_id, MIN(date) as d0, MAX(date) as d1')
                ->whereIn('course_class_id', $ids)
                ->groupBy('course_class_id')
                ->get()
                ->keyBy('course_class_id');
        }

        foreach ($turmas as $turma) {
            if ($turma->schedules->isEmpty()) {
                continue;
            }

            $bound = $bounds->get($turma->id);
            if ($bound && $bound->d0 !== null && $bound->d1 !== null) {
                $p0 = CarbonImmutable::parse($bound->d0)->startOfDay();
                $p1 = CarbonImmutable::parse($bound->d1)->endOfDay();
            } else {
                if ($turma->enrollment_start === null || $turma->enrollment_end === null) {
                    continue;
                }
                $p0 = CarbonImmutable::parse($turma->enrollment_start)->startOfDay();
                $p1 = CarbonImmutable::parse($turma->enrollment_end)->endOfDay();
                if ($turma->status === 'em_andamento' && $p1->lt(CarbonImmutable::now())) {
                    $p1 = CarbonImmutable::now()->addMonths(6)->endOfDay();
                }
            }

            if ($p1->lt($p0)) {
                continue;
            }

            $rangeStart = $p0->max($monthStart->startOfDay());
            $rangeEnd = $p1->min($monthEnd->endOfDay());
            if ($rangeEnd->lt($rangeStart)) {
                continue;
            }

            foreach ($turma->schedules as $sch) {
                $wd = (int) $sch->weekday;
                $startT = Carbon::parse($sch->start_time)->format('H:i');
                $endT = Carbon::parse($sch->end_time)->format('H:i');
                $sort = (int) str_replace(':', '', $startT);

                $d = $rangeStart->copy();
                while ($d->lte($rangeEnd)) {
                    if ((int) $d->dayOfWeek === $wd) {
                        $key = $d->toDateString();
                        $addItem($key, [
                            'kind' => 'turma',
                            'title' => $turma->name,
                            'subtitle' => $turma->course?->name ?? 'Turma',
                            'timeLabel' => $startT.'–'.$endT,
                            'sort' => $sort,
                            'href' => route('portal.cursos.show', ['curso' => $turma->course_id]),
                        ]);
                    }
                    $d = $d->addDay();
                }
            }
        }

        foreach ($itemsByDate as &$list) {
            usort($list, fn (array $a, array $b): int => ($a['sort'] ?? 0) <=> ($b['sort'] ?? 0));
        }
        unset($list);

        $gridStart = $monthStart->copy()->startOfMonth()->startOfWeek(CarbonInterface::MONDAY);
        $gridEnd = $monthStart->copy()->endOfMonth()->endOfWeek(CarbonInterface::SUNDAY);

        $weeks = [];
        $d = $gridStart;
        while ($d->lte($gridEnd)) {
            $row = [];
            for ($i = 0; $i < 7; $i++) {
                $key = $d->toDateString();
                $row[] = [
                    'date' => $d,
                    'inMonth' => $d->month === $monthStart->month,
                    'items' => $itemsByDate[$key] ?? [],
                ];
                $d = $d->addDay();
            }
            $weeks[] = $row;
        }

        $monthLabel = $monthStart->locale(app()->getLocale())->translatedFormat('F \d\e Y');

        return [
            'monthStart' => $monthStart,
            'monthEnd' => $monthEnd,
            'monthLabel' => ucfirst((string) $monthLabel),
            'prevParam' => $prev->format('Y-m'),
            'nextParam' => $next->format('Y-m'),
            'weeks' => $weeks,
        ];
    }
}
