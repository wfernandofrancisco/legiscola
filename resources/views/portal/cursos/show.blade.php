@extends('layouts.portal')

@section('title', $curso->name)

@section('meta_description')
    {{ \Illuminate\Support\Str::limit(strip_tags((string) ($curso->description ?: $curso->name)), 160) }}
@endsection

@section('content')
    @php
        $wd = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
        $now = now();
        $studentEnrollmentStatuses = $studentEnrollmentStatuses ?? [];
        $heroSubtitle = $curso->description
            ? \Illuminate\Support\Str::limit(strip_tags((string) $curso->description), 240)
            : collect([
                $curso->workload_hours ? $curso->workload_hours.' horas' : null,
                $curso->admin?->name ? 'Coordenação: '.$curso->admin->name : null,
                $curso->courseClasses->count().' turma(s) pública(s)',
            ])->filter()->join(' · ');
    @endphp

    <x-portal.page-hero :title="$curso->name" :subtitle="$heroSubtitle">
        <x-slot name="actions">
            @if($curso->courseClasses->where('status', 'inscricao')->isNotEmpty())
                <a href="{{ route('portal.acesso.register') }}" class="inline-flex items-center rounded-full px-8 py-3 text-sm font-bold text-white shadow-xl transition hover:opacity-95"
                   style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                    Solicitar participação
                </a>
            @endif
            <a href="{{ route('portal.acesso.login') }}" class="inline-flex items-center rounded-full border-2 border-white/75 bg-white/90 px-8 py-3 text-sm font-bold text-slate-900 shadow-xl transition hover:bg-slate-100">
                Área do aluno
            </a>
            <a href="{{ route('portal.cursos.index') }}" class="inline-flex items-center rounded-full border-2 border-white/75 px-8 py-3 text-sm font-bold text-white transition hover:bg-white/10">
                Ver turmas
            </a>
        </x-slot>
    </x-portal.page-hero>

    <section class="border-b border-slate-200 bg-slate-50/60 py-8 dark:border-slate-800 dark:bg-slate-900/30">
        <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
            <p class="text-center text-xs font-bold uppercase tracking-[0.28em]" style="color:var(--portal-primary)">Curso institucional</p>
            <dl class="mt-6 flex flex-wrap justify-center gap-x-10 gap-y-4 text-center text-sm text-slate-600 dark:text-slate-400">
                @if($curso->workload_hours)
                    <div><dt class="font-semibold text-slate-900 dark:text-white">Carga horária</dt><dd class="mt-1">{{ $curso->workload_hours }} horas</dd></div>
                @endif
                @if($curso->admin)
                    <div><dt class="font-semibold text-slate-900 dark:text-white">Coordenação</dt><dd class="mt-1">{{ $curso->admin->name }}</dd></div>
                @endif
                <div><dt class="font-semibold text-slate-900 dark:text-white">Turmas públicas</dt><dd class="mt-1">{{ $curso->courseClasses->count() }}</dd></div>
            </dl>
        </div>
    </section>

    @if($curso->description)
        <section class="py-14">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Sobre o programa</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $curso->description !!}</div>
            </div>
        </section>
    @endif

    @if($curso->curricula->isNotEmpty())
        <section class="border-y border-slate-200 bg-slate-50/80 py-14 dark:border-slate-800 dark:bg-slate-900/40">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Estrutura curricular</h2>
                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    @foreach($curso->curricula->sortBy('position') as $curriculum)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-950/40">
                            <p class="text-sm font-semibold" style="color:var(--portal-primary)">{{ $curriculum->workload_hours ? $curriculum->workload_hours.'h · ' : '' }}{{ $curriculum->name }}</p>
                            @if($curriculum->description)
                                <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">{{ $curriculum->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Turmas e cronograma</h2>
            <p class="mt-3 max-w-2xl text-sm text-slate-600 dark:text-slate-300">Cronogramas indicativos de encontros (consulte sempre a sala de aula oficial).</p>
            @if(session('success'))
                <div class="mt-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-semibold text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif
            <div class="mt-10 space-y-8">
                @foreach($curso->courseClasses as $turma)
                    @php
                        $mat = (int) ($turma->matriculas_count ?? 0);
                        $livre = $turma->max_seats !== null ? max(0, (int) $turma->max_seats - $mat) : null;
                        $enrollmentStatus = $studentEnrollmentStatuses[$turma->id] ?? null;
                        $inscricaoAberta = ($turma->status ?? '') === 'inscricao'
                            && $turma->enrollment_start
                            && $turma->enrollment_end
                            && $turma->enrollment_start->lte($now)
                            && $turma->enrollment_end->gte($now)
                            && ($livre === null || $livre > 0);
                        $statusUi = match ($turma->status ?? '') {
                            'inscricao' => [
                                'label' => 'Inscrições abertas',
                                'wrap' => 'border-emerald-500/90 bg-emerald-600 text-white ring-2 ring-emerald-500/30 shadow-md shadow-emerald-900/20',
                            ],
                            'em_andamento' => [
                                'label' => 'Turma em andamento',
                                'wrap' => 'border-sky-500/90 bg-sky-600 text-white ring-2 ring-sky-500/25 shadow-md shadow-sky-900/20',
                            ],
                            'cadastrado' => [
                                'label' => 'Cadastrada',
                                'wrap' => 'border-amber-400/90 bg-amber-500 text-amber-950 ring-2 ring-amber-400/35 shadow-md',
                            ],
                            'concluido' => [
                                'label' => 'Turma concluída',
                                'wrap' => 'border-slate-500/70 bg-slate-600 text-white ring-2 ring-slate-500/25 shadow',
                            ],
                            'cancelado' => [
                                'label' => 'Turma cancelada',
                                'wrap' => 'border-red-500/90 bg-red-600 text-white ring-2 ring-red-500/30 shadow-md',
                            ],
                            default => [
                                'label' => $turma->status ? ucfirst(str_replace('_', ' ', (string) $turma->status)) : 'Turma',
                                'wrap' => 'border-slate-400 bg-slate-700 text-white ring-2 ring-slate-500/20',
                            ],
                        };
                    @endphp
                    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/30">
                        <div class="border-b border-slate-100 bg-slate-50/80 px-6 py-5 dark:border-slate-800 dark:bg-slate-900/60">
                            <div class="flex flex-wrap items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <span class="inline-flex max-w-full items-center rounded-full border-2 px-3.5 py-1.5 text-[10px] font-black uppercase leading-tight tracking-[0.16em] sm:px-4 sm:py-2 sm:text-[11px] {{ $statusUi['wrap'] }}">
                                        {{ $statusUi['label'] }}
                                    </span>
                                    <h3 class="mt-3 text-xl font-bold text-slate-900 dark:text-white">{{ $turma->name }}</h3>
                                    @if($turma->relationLoaded('teachers') && $turma->teachers->isNotEmpty())
                                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                                            <span class="font-semibold text-slate-800 dark:text-slate-200">Docente(s):</span>
                                            {{ $turma->teachers->pluck('full_name')->filter()->implode(', ') }}
                                        </p>
                                    @endif
                                </div>
                                <div class="text-right text-xs text-slate-500 dark:text-slate-400">
                                    @if($turma->enrollment_start && $turma->enrollment_end)
                                        Inscrições: {{ $turma->enrollment_start->format('d/m/Y') }} — {{ $turma->enrollment_end->format('d/m/Y') }}<br/>
                                    @endif
                                    @if($livre !== null)
                                        <span class="mt-2 inline-flex rounded-full px-3 py-1 font-semibold text-white" style="background:var(--portal-secondary)">{{ $livre }} vaga{{ $livre === 1 ? '' : 's' }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="grid gap-6 px-6 py-6 lg:grid-cols-2">
                            <div>
                                <p class="text-sm font-bold text-slate-900 dark:text-white">Cronograma semanal</p>
                                @if($turma->schedules->isEmpty())
                                    <p class="mt-3 text-sm text-slate-500">Horários a definir pela coordenação.</p>
                                @else
                                    <ul class="mt-4 space-y-2">
                                        @foreach($turma->schedules->sortBy('weekday') as $sch)
                                            <li class="rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-sm dark:border-slate-800 dark:bg-slate-950/50">
                                                <span class="font-semibold">{{ $wd[$sch->weekday % 7] ?? $sch->weekday }}</span>
                                                {{ \Illuminate\Support\Carbon::parse($sch->start_time)->format('H:i') }}
                                                –
                                                {{ \Illuminate\Support\Carbon::parse($sch->end_time)->format('H:i') }}
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                            <div class="rounded-2xl bg-gradient-to-br from-slate-100 to-white p-6 text-sm text-slate-600 dark:from-slate-900 dark:to-slate-950 dark:text-slate-300">
                                <p>Este espaço institucional oferece transparência acadêmica. Confirme presencialidade ou modalidade ao realizar sua inscrição.</p>
                                @if($enrollmentStatus)
                                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                                        Você já está inscrito nesta turma.
                                    </div>
                                @elseif($inscricaoAberta)
                                    @auth
                                        <form method="POST" action="{{ route('portal.cursos.turmas.inscrever', ['curso' => $curso->id, 'turma' => $turma->id]) }}" class="mt-4">
                                            @csrf
                                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                                                style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                                                Inscrever-me nesta turma
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('portal.acesso.login') }}" class="mt-4 inline-flex w-full items-center justify-center rounded-xl px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                                            style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                                            Entrar para se inscrever
                                        </a>
                                    @endauth
                                @else
                                    <a href="{{ route('portal.acesso.login') }}" class="mt-4 inline-flex font-semibold" style="color:var(--portal-primary)">Acompanhar pela área logada →</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    @if($cursosRelacionados->isNotEmpty())
        <section class="pb-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Explore também</h2>
                <div class="mt-8 grid gap-6 lg:grid-cols-3">
                    @foreach($cursosRelacionados as $rel)
                        <a href="{{ route('portal.cursos.show', ['curso' => $rel->id]) }}" class="group rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40">
                            <p class="text-lg font-bold text-slate-900 group-hover:underline dark:text-white">{{ $rel->name }}</p>
                            @if($rel->workload_hours)
                                <p class="mt-3 text-xs font-semibold text-slate-500">{{ $rel->workload_hours }} horas • {{ $rel->admin?->name }}</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
