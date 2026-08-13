<x-layouts.admin>
    <x-slot name="title">Painel da Escola</x-slot>
    <x-slot name="subtitle">Indicadores operacionais do tenant</x-slot>

    @php
        $fmt = fn ($value) => number_format((int) $value, 0, ',', '.');
        $turmasTotal = max(1, (int) ($stats['turmas_total'] ?? 0));
        $alunosTotal = max(1, (int) ($stats['alunos_cadastrados'] ?? 0));
        $sexoMax = max(1, collect($sexoCounts ?? [])->max('total') ?? 0);
        $ageMax = max(1, collect($ageBuckets ?? [])->max('total') ?? 0);

        $heroCards = [
            [
                'label' => 'Turmas em inscrição',
                'value' => $stats['turmas_inscricao'] ?? 0,
                'hint' => 'Com vagas abertas no portal',
                'href' => route('admin.turmas.index', ['status' => 'inscricao']),
                'tone' => 'from-emerald-500 to-teal-600',
                'ring' => 'ring-emerald-400/30',
            ],
            [
                'label' => 'Turmas ativas',
                'value' => $stats['turmas_ativas'] ?? 0,
                'hint' => 'Aulas em andamento',
                'href' => route('admin.turmas.index', ['status' => 'em_andamento']),
                'tone' => 'from-sky-500 to-blue-700',
                'ring' => 'ring-sky-400/30',
            ],
            [
                'label' => 'Turmas encerradas',
                'value' => $stats['turmas_encerradas'] ?? 0,
                'hint' => 'Base para certificados',
                'href' => route('admin.turmas.index', ['status' => 'concluido']),
                'tone' => 'from-stone-600 to-slate-900',
                'ring' => 'ring-slate-400/25',
            ],
            [
                'label' => 'Alunos matriculados',
                'value' => $stats['alunos_matriculados'] ?? 0,
                'hint' => 'Com vínculo em alguma turma',
                'href' => route('admin.alunos.index'),
                'tone' => 'from-amber-500 to-orange-700',
                'ring' => 'ring-amber-400/30',
            ],
        ];

        $quickActions = [
            ['label' => 'Nova turma', 'desc' => 'Abrir calendário e inscrição', 'href' => route('admin.turmas.create')],
            ['label' => 'Alunos', 'desc' => 'Cadastro e busca', 'href' => route('admin.alunos.index')],
            ['label' => 'Aulas', 'desc' => 'Agenda pedagógica', 'href' => route('admin.aulas.index')],
            ['label' => 'Certificados', 'desc' => 'Templates e emissão', 'href' => route('admin.templates-certificado.index')],
        ];
    @endphp

    <div class="space-y-8">
        <section class="relative overflow-hidden rounded-[2rem] border border-slate-200 bg-blue-800 dark:bg-slate-950 p-6 text-white shadow-2xl shadow-slate-950/10 dark:border-slate-800">
            <div class="absolute inset-0 opacity-35" style="background-image: radial-gradient(circle at 12% 18%, rgba(20,184,166,.55), transparent 28%), radial-gradient(circle at 82% 12%, rgba(245,158,11,.35), transparent 24%), linear-gradient(135deg, rgba(15,23,42,.92), rgba(2,6,23,1));"></div>
            <div class="absolute -right-24 top-0 h-72 w-72 rounded-full border border-white/10"></div>
            <div class="absolute bottom-0 left-0 h-px w-full bg-linear-to-r from-transparent via-white/30 to-transparent"></div>

            <div class="relative grid gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-end">
                <div>
                    <p class="text-xs font-black uppercase tracking-[0.32em] text-teal-200">Radar institucional</p>
                    <h1 class="mt-4 max-w-3xl text-4xl font-black tracking-tight sm:text-5xl">
                        Visão de gestão da Escola Legislativa
                    </h1>
                    <p class="mt-4 max-w-2xl text-sm leading-6 text-slate-300">
                        Acompanhe inscrições, andamento das turmas, matrículas e perfil dos alunos em um painel único para tomada de decisão.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs text-slate-300">Alunos cadastrados</p>
                        <p class="mt-2 text-3xl font-black">{{ $fmt($stats['alunos_cadastrados'] ?? 0) }}</p>
                    </div>
                    <div class="rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur">
                        <p class="text-xs text-slate-300">Concluintes</p>
                        <p class="mt-2 text-3xl font-black text-emerald-200">{{ $fmt($stats['alunos_concluintes'] ?? 0) }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            @foreach ($heroCards as $card)
                <a href="{{ $card['href'] }}"
                    class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900">
                    <div class="absolute -right-10 -top-10 h-28 w-28 rounded-full bg-linear-to-br {{ $card['tone'] }} opacity-20 blur-sm transition group-hover:scale-125"></div>
                    <div class="relative">
                        <div class="mb-5 inline-flex h-11 w-11 items-center justify-center rounded-2xl bg-linear-to-br {{ $card['tone'] }} text-white ring-4 {{ $card['ring'] }}">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M6 7v11a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7M9 11h6M9 15h4" />
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
                        <div class="mt-2 flex items-end justify-between gap-3">
                            <p class="text-4xl font-black tracking-tight text-slate-950 dark:text-white">{{ $fmt($card['value']) }}</p>
                            <span class="text-xs font-bold uppercase tracking-wide text-slate-400 group-hover:text-slate-700 dark:group-hover:text-slate-200">Abrir</span>
                        </div>
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">{{ $card['hint'] }}</p>
                    </div>
                </a>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs font-black uppercase tracking-[0.22em] text-amber-600 dark:text-amber-300">Turmas</p>
                        <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Distribuição por status</h2>
                    </div>
                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        Total: {{ $fmt($stats['turmas_total'] ?? 0) }}
                    </span>
                </div>

                <div class="mt-6 space-y-4">
                    @foreach ([
                        ['key' => 'inscricao', 'label' => 'Em inscrição', 'bar' => 'bg-emerald-500'],
                        ['key' => 'em_andamento', 'label' => 'Ativas', 'bar' => 'bg-sky-500'],
                        ['key' => 'concluido', 'label' => 'Encerradas', 'bar' => 'bg-slate-700 dark:bg-slate-300'],
                        ['key' => 'cadastrado', 'label' => 'Cadastradas', 'bar' => 'bg-amber-500'],
                        ['key' => 'cancelado', 'label' => 'Canceladas', 'bar' => 'bg-rose-500'],
                    ] as $row)
                        @php
                            $value = (int) ($classStatusCounts[$row['key']] ?? 0);
                            $pct = $turmasTotal > 0 ? min(100, round(($value / $turmasTotal) * 100)) : 0;
                        @endphp
                        <div>
                            <div class="mb-1 flex items-center justify-between text-sm">
                                <span class="font-semibold text-slate-700 dark:text-slate-200">{{ $row['label'] }}</span>
                                <span class="text-slate-500 dark:text-slate-400">{{ $fmt($value) }}</span>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="h-full rounded-full {{ $row['bar'] }}" style="width: {{ $pct }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <p class="text-xs font-black uppercase tracking-[0.22em] text-teal-600 dark:text-teal-300">Alunos</p>
                <h2 class="mt-1 text-xl font-black text-slate-950 dark:text-white">Situação acadêmica</h2>

                <div class="mt-6 grid grid-cols-2 gap-3">
                    @foreach ([
                        ['label' => 'Cadastrados', 'value' => $stats['alunos_cadastrados'] ?? 0],
                        ['label' => 'Com turma', 'value' => $stats['alunos_matriculados'] ?? 0],
                        ['label' => 'Concluíram', 'value' => $stats['alunos_concluintes'] ?? 0],
                        ['label' => 'Sem turma', 'value' => $stats['alunos_sem_turma'] ?? 0],
                    ] as $row)
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/40">
                            <p class="text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $row['label'] }}</p>
                            <p class="mt-2 text-3xl font-black text-slate-950 dark:text-white">{{ $fmt($row['value']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-black text-slate-950 dark:text-white">Alunos por sexo</h2>
                <div class="mt-5 space-y-3">
                    @foreach ($sexoCounts as $row)
                        @php $pct = $sexoMax > 0 ? min(100, round(((int) $row['total'] / $sexoMax) * 100)) : 0; @endphp
                        <div class="grid grid-cols-[7.5rem_1fr_3rem] items-center gap-3 text-sm">
                            <span class="font-semibold text-slate-600 dark:text-slate-300">{{ $row['label'] }}</span>
                            <div class="h-9 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                <div class="flex h-full items-center rounded-full bg-linear-to-r from-cyan-500 to-teal-500 px-3 text-xs font-bold text-white" style="width: {{ $pct }}%">
                                    {{ $row['total'] > 0 ? $fmt($row['total']) : '' }}
                                </div>
                            </div>
                            <span class="text-right font-black text-slate-900 dark:text-white">{{ $fmt($row['total']) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-black text-slate-950 dark:text-white">Faixa etária</h2>
                <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($ageBuckets as $bucket)
                        @php $height = $ageMax > 0 ? max(12, min(100, round(((int) $bucket['total'] / $ageMax) * 100))) : 12; @endphp
                        <div class="rounded-2xl border border-slate-100 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                            <div class="flex h-24 items-end rounded-xl bg-white p-2 dark:bg-slate-900">
                                <div class="w-full rounded-lg bg-linear-to-t from-amber-600 to-amber-300" style="height: {{ $height }}%"></div>
                            </div>
                            <p class="mt-3 text-xs font-semibold text-slate-500 dark:text-slate-400">{{ $bucket['label'] }}</p>
                            <p class="text-2xl font-black text-slate-950 dark:text-white">{{ $fmt($bucket['total']) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <h2 class="text-xl font-black text-slate-950 dark:text-white">Ações rápidas</h2>
                <div class="mt-5 grid gap-3 sm:grid-cols-2">
                    @foreach ($quickActions as $action)
                        <a href="{{ $action['href'] }}" class="group rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:-translate-y-0.5 hover:border-cyan-300 hover:bg-cyan-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-cyan-700 dark:hover:bg-cyan-950/20">
                            <p class="font-black text-slate-950 dark:text-white">{{ $action['label'] }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $action['desc'] }}</p>
                            <p class="mt-3 text-xs font-bold uppercase tracking-wide text-cyan-600 dark:text-cyan-300">Acessar →</p>
                        </a>
                    @endforeach
                </div>
            </div>

            <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-slate-950 dark:text-white">Próximas inscrições</h2>
                    <a href="{{ route('admin.turmas.index', ['status' => 'inscricao']) }}" class="text-xs font-bold uppercase tracking-wide text-cyan-600 hover:underline dark:text-cyan-300">Ver todas</a>
                </div>
                <div class="mt-5 space-y-3">
                    @forelse ($openClasses as $class)
                        @php
                            $used = (int) ($class->matriculas_count ?? 0);
                            $seats = $class->max_seats !== null ? (int) $class->max_seats : null;
                            $seatLabel = $seats !== null ? $used.' / '.$seats.' vagas' : $used.' matrícula(s)';
                        @endphp
                        <a href="{{ route('admin.turmas.show', ['turma' => $class, 'tab' => 'matriculas']) }}" class="block rounded-2xl border border-slate-100 bg-slate-50 p-4 transition hover:border-emerald-300 hover:bg-emerald-50 dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-emerald-700 dark:hover:bg-emerald-950/20">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <p class="font-black text-slate-950 dark:text-white">{{ $class->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">{{ $class->course?->name ?? 'Curso não informado' }}</p>
                                </div>
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300">{{ $seatLabel }}</span>
                            </div>
                            <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                                Encerramento: {{ $class->enrollment_end?->format('d/m/Y H:i') ?? 'sem data' }}
                            </p>
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-200 p-8 text-center text-sm text-slate-500 dark:border-slate-800 dark:text-slate-400">
                            Nenhuma turma em inscrição no momento.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-layouts.admin>
