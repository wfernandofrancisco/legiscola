<x-layouts.director>
    <x-page-header title="Agenda"
        subtitle="Cursos e eventos liberados para as câmaras da sua região."
        :action-href="route('diretor.agenda.export')"
        action-text="Exportar ICS" />

    @if ($conflitos->isNotEmpty())
        <div
            class="mb-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-800/60 dark:bg-amber-950/40"
            role="alert">
            <p class="text-sm font-bold text-amber-900 dark:text-amber-100">
                Conflitos presenciais no mesmo dia
            </p>
            <p class="mt-1 text-xs text-amber-800/90 dark:text-amber-200/80">
                Há mais de um compromisso presencial na mesma data. Confira se a agenda e o deslocamento são viáveis.
            </p>
            <ul class="mt-3 space-y-2">
                @foreach ($conflitos as $conflito)
                    <li class="rounded-xl border border-amber-200/80 bg-white/70 px-3 py-2 dark:border-amber-800/40 dark:bg-slate-900/50">
                        <p class="text-xs font-semibold uppercase tracking-wide text-amber-700 dark:text-amber-300">
                            {{ $conflito['data_label'] }}
                        </p>
                        <ul class="mt-1 space-y-0.5 text-sm text-slate-700 dark:text-slate-200">
                            @foreach ($conflito['itens'] as $item)
                                <li>
                                    @if (! empty($item['url']))
                                        <a href="{{ $item['url'] }}" class="font-medium text-indigo-700 hover:underline dark:text-indigo-300">
                                            {{ $item['titulo'] }}
                                        </a>
                                    @else
                                        <span class="font-medium">{{ $item['titulo'] }}</span>
                                    @endif
                                    <span class="text-slate-500 dark:text-slate-400">· {{ $item['camara'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Legenda --}}
    <div
        class="mb-4 flex flex-wrap items-center gap-2 rounded-2xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900 sm:gap-3 sm:px-4">
        <p class="mr-1 text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Legenda</p>

        <span
            class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-800 ring-1 ring-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-200 dark:ring-emerald-800/60">
            <span class="h-2.5 w-2.5 rounded-full bg-emerald-600 shadow-sm shadow-emerald-500/40"></span>
            Evento confirmado
        </span>
        <span
            class="inline-flex items-center gap-2 rounded-full bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-800 ring-1 ring-sky-200 dark:bg-sky-950/40 dark:text-sky-200 dark:ring-sky-800/60">
            <span class="h-2.5 w-2.5 rounded-full bg-sky-400 shadow-sm"></span>
            Evento agendado
        </span>
        <span
            class="inline-flex items-center gap-2 rounded-full bg-orange-50 px-3 py-1.5 text-xs font-semibold text-orange-800 ring-1 ring-orange-200 dark:bg-orange-950/40 dark:text-orange-200 dark:ring-orange-800/60">
            <span class="h-2.5 w-2.5 rounded-full bg-orange-600 shadow-sm shadow-orange-500/40"></span>
            Curso (turma)
        </span>
        <span
            class="inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-900 ring-1 ring-amber-200 dark:bg-amber-950/40 dark:text-amber-100 dark:ring-amber-800/60">
            <span class="h-2.5 w-2.5 rounded-full bg-amber-500 shadow-sm"></span>
            Conflito presencial
        </span>
    </div>

    <div
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white p-3 shadow-sm dark:border-slate-700 dark:bg-slate-900 sm:p-5">
        <div id="diretor-agenda-calendar" class="min-h-[36rem]"></div>
    </div>

    <div id="agenda-popover"
        class="pointer-events-none fixed z-50 hidden max-w-xs overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl dark:border-slate-600 dark:bg-slate-800 dark:shadow-black/40">
        <div id="agenda-popover-accent" class="h-1.5 w-full bg-blue-600"></div>
        <div class="p-3">
            <span id="agenda-popover-badge"
                class="inline-flex rounded-md bg-slate-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-slate-600 dark:bg-slate-700 dark:text-slate-200"></span>
            <p id="agenda-popover-title" class="mt-1.5 font-semibold text-slate-900 dark:text-white"></p>
            <p id="agenda-popover-meta" class="mt-1 text-xs text-slate-500 dark:text-slate-300"></p>
            <p id="agenda-popover-detalhe" class="mt-1 text-xs text-slate-600 dark:text-slate-400"></p>
        </div>
    </div>

    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet" />
        <style>
            #diretor-agenda-calendar .fc {
                --fc-border-color: rgb(226 232 240);
                --fc-button-bg-color: #4f46e5;
                --fc-button-border-color: #4f46e5;
                --fc-button-hover-bg-color: #4338ca;
                --fc-button-hover-border-color: #4338ca;
                --fc-button-active-bg-color: #3730a3;
                --fc-button-active-border-color: #3730a3;
                --fc-today-bg-color: rgb(239 246 255);
                --fc-page-bg-color: transparent;
                --fc-neutral-bg-color: rgb(248 250 252);
                --fc-list-event-hover-bg-color: rgb(241 245 249);
                color: rgb(15 23 42);
            }

            .dark #diretor-agenda-calendar .fc {
                --fc-border-color: rgb(51 65 85);
                --fc-page-bg-color: transparent;
                --fc-neutral-bg-color: rgb(30 41 59);
                --fc-list-event-hover-bg-color: rgb(51 65 85);
                --fc-today-bg-color: rgb(30 58 138 / 0.28);
                --fc-button-bg-color: #6366f1;
                --fc-button-border-color: #6366f1;
                --fc-button-hover-bg-color: #4f46e5;
                --fc-button-hover-border-color: #4f46e5;
                --fc-button-active-bg-color: #4338ca;
                --fc-button-active-border-color: #4338ca;
                --fc-button-text-color: #fff;
                color: rgb(226 232 240);
            }

            .dark #diretor-agenda-calendar .fc-col-header-cell-cushion,
            .dark #diretor-agenda-calendar .fc-daygrid-day-number,
            .dark #diretor-agenda-calendar .fc-list-day-text,
            .dark #diretor-agenda-calendar .fc-list-day-side-text,
            .dark #diretor-agenda-calendar .fc-timegrid-slot-label-cushion,
            .dark #diretor-agenda-calendar .fc-toolbar-title {
                color: rgb(226 232 240);
            }

            .dark #diretor-agenda-calendar .fc-theme-standard td,
            .dark #diretor-agenda-calendar .fc-theme-standard th,
            .dark #diretor-agenda-calendar .fc-theme-standard .fc-scrollgrid {
                border-color: rgb(51 65 85);
            }

            .dark #diretor-agenda-calendar .fc-list-empty {
                background: rgb(15 23 42);
                color: rgb(148 163 184);
            }

            #diretor-agenda-calendar .fc-toolbar-title {
                font-size: 1.15rem;
                font-weight: 700;
            }

            #diretor-agenda-calendar .fc-button {
                border-radius: 0.5rem !important;
                font-weight: 600;
                text-transform: none;
                box-shadow: none !important;
            }

            #diretor-agenda-calendar .fc-event.agenda-card {
                cursor: pointer;
                border-radius: 0.55rem;
                border-width: 0;
                border-left-width: 3px;
                border-left-style: solid;
                padding: 2px 6px;
                font-size: 0.72rem;
                font-weight: 600;
                line-height: 1.25;
                box-shadow: 0 1px 2px rgb(15 23 42 / 0.12);
            }

            #diretor-agenda-calendar .fc-event.agenda-card--evento {
                border-left-color: #14532d;
            }

            #diretor-agenda-calendar .fc-event.agenda-card--evento-planejado {
                border-left-color: #0369a1;
                border-style: solid;
                box-shadow: none;
            }

            #diretor-agenda-calendar .fc-event.agenda-card--curso {
                border-left-color: #9a3412;
            }

            #diretor-agenda-calendar .fc-event.agenda-card--conflito {
                outline: 2px solid #f59e0b;
                outline-offset: 1px;
            }

            #diretor-agenda-calendar .agenda-card-inner {
                display: flex;
                flex-direction: column;
                gap: 1px;
                min-width: 0;
            }

            #diretor-agenda-calendar .agenda-card-badge {
                display: inline-block;
                width: fit-content;
                font-size: 0.58rem;
                font-weight: 800;
                letter-spacing: 0.04em;
                text-transform: uppercase;
                opacity: 0.92;
                line-height: 1.1;
            }

            #diretor-agenda-calendar .agenda-card-title {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            #diretor-agenda-calendar .fc-list-event-title a {
                color: inherit;
            }

            .dark #diretor-agenda-calendar .fc-list-event-dot {
                border-color: currentColor;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/pt-br.global.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const el = document.getElementById('diretor-agenda-calendar');
                if (!el || typeof FullCalendar === 'undefined') return;

                const pop = document.getElementById('agenda-popover');
                const popAccent = document.getElementById('agenda-popover-accent');
                const popBadge = document.getElementById('agenda-popover-badge');
                const popTitle = document.getElementById('agenda-popover-title');
                const popMeta = document.getElementById('agenda-popover-meta');
                const popDetalhe = document.getElementById('agenda-popover-detalhe');

                const calendar = new FullCalendar.Calendar(el, {
                    locale: 'pt-br',
                    initialView: window.matchMedia('(max-width: 768px)').matches ? 'listMonth' : 'dayGridMonth',
                    height: 'auto',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth,timeGridWeek,listMonth',
                    },
                    buttonText: {
                        today: 'Hoje',
                        month: 'Mês',
                        week: 'Semana',
                        list: 'Lista',
                    },
                    navLinks: true,
                    nowIndicator: true,
                    eventDisplay: 'block',
                    events: {
                        url: @json(route('diretor.agenda.events')),
                        failure: function () {
                            alert('Não foi possível carregar a agenda.');
                        },
                    },
                    eventContent: function (arg) {
                        const x = arg.event.extendedProps || {};
                        const badge = x.badge || (x.categoria === 'curso' ? 'Curso' : 'Evento');
                        const title = arg.event.title || '';

                        const wrap = document.createElement('div');
                        wrap.className = 'agenda-card-inner';

                        const b = document.createElement('span');
                        b.className = 'agenda-card-badge';
                        b.textContent = badge;

                        const t = document.createElement('span');
                        t.className = 'agenda-card-title';
                        t.textContent = title;

                        wrap.appendChild(b);
                        wrap.appendChild(t);

                        return { domNodes: [wrap] };
                    },
                    eventMouseEnter: function (info) {
                        const x = info.extendedProps || {};
                        const isCurso = x.categoria === 'curso';
                        const accent = isCurso
                            ? '#ea580c'
                            : (x.tipo === 'planejada' ? '#38bdf8' : '#16a34a');

                        popAccent.style.backgroundColor = accent;
                        popBadge.textContent = x.badge || (isCurso ? 'Curso' : 'Evento');
                        popBadge.style.backgroundColor = isCurso ? 'rgb(255 237 213)' : (x.tipo === 'planejada' ? 'rgb(224 242 254)' : 'rgb(209 250 229)');
                        popBadge.style.color = isCurso ? 'rgb(154 52 18)' : (x.tipo === 'planejada' ? 'rgb(3 105 161)' : 'rgb(6 95 70)');

                        if (document.documentElement.classList.contains('dark')) {
                            popBadge.style.backgroundColor = isCurso
                                ? 'rgb(124 45 18 / 0.45)'
                                : (x.tipo === 'planejada' ? 'rgb(7 89 133 / 0.45)' : 'rgb(6 78 59 / 0.45)');
                            popBadge.style.color = isCurso
                                ? 'rgb(254 215 170)'
                                : (x.tipo === 'planejada' ? 'rgb(186 230 253)' : 'rgb(167 243 208)');
                        }

                        popTitle.textContent = info.event.title;
                        popMeta.textContent = [x.camara, x.modalidade, x.professor].filter(Boolean).join(' · ');
                        popDetalhe.textContent = x.detalhe || '';
                        pop.classList.remove('hidden');

                        const rect = info.el.getBoundingClientRect();
                        pop.style.left = Math.min(rect.left, window.innerWidth - 280) + 'px';
                        pop.style.top = (rect.bottom + 8) + 'px';
                    },
                    eventMouseLeave: function () {
                        pop.classList.add('hidden');
                    },
                    eventClick: function (info) {
                        if (info.event.url) {
                            info.jsEvent.preventDefault();
                            window.location.href = info.event.url;
                        }
                    },
                });

                calendar.render();
            });
        </script>
    @endpush
</x-layouts.director>
