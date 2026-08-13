@extends('layouts.portal')

@section('title', 'Turmas')

@section('content')
    <x-portal.page-hero title="Turmas e ofertas" subtitle="Cada turma está vinculada ao seu curso. Filtre por situação, consulte vagas, datas e carga horária.">
        <x-slot name="actions">
            <a href="{{ route('portal.cursos.historico') }}"
               class="inline-flex shrink-0 rounded-full border border-slate-300/90 bg-white/60 px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm backdrop-blur hover:bg-white dark:border-slate-600 dark:bg-slate-900/50 dark:text-slate-100 dark:hover:bg-slate-800">
                Histórico de turmas →
            </a>
        </x-slot>
    </x-portal.page-hero>

    @if($cursosSemTurmaPortal->isNotEmpty())
        <div class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
            <div class="rounded-2xl border border-amber-200/90 bg-amber-50 px-5 py-4 text-sm leading-relaxed text-amber-950 dark:border-amber-500/35 dark:bg-amber-950/40 dark:text-amber-100">
                <p class="font-semibold">Curso{{ $cursosSemTurmaPortal->count() === 1 ? '' : 's' }} sem turma publicada neste momento</p>
                <ul class="mt-2 list-inside list-disc space-y-1">
                    @foreach($cursosSemTurmaPortal as $cu)
                        <li><a href="{{ route('portal.cursos.show', ['curso' => $cu->id]) }}" class="underline decoration-amber-600/50 underline-offset-2 hover:no-underline dark:decoration-amber-400/45">{{ $cu->name }}</a></li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @php($tabQueryBase = array_filter([
        'per_page' => request()->filled('per_page') ? request()->integer('per_page') : null,
        'page' => null,
    ]))

    <section class="no-portal-animate py-12">
        <div class="mx-auto flex max-w-7xl flex-wrap gap-2 px-4 sm:px-6 lg:px-8">
            @foreach($turmaTabs as $key => $label)
                <a href="{{ route('portal.cursos.index', $tabQueryBase + ['situacao' => $key]) }}"
                   class="rounded-full px-5 py-2 text-sm font-semibold transition
                        {{ ($situacao ?? '') === $key
                           ? 'text-white shadow-md'
                           : 'border border-slate-300 bg-white text-slate-700 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200' }}"
                   style="{{ ($situacao ?? '') === $key ? 'background: linear-gradient(135deg, var(--portal-primary), var(--portal-secondary));' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <div class="mx-auto mt-10 max-w-7xl px-4 sm:px-6 lg:px-8">
            @php($hasTurma = false)
            <div class="space-y-4">
                @foreach($cursos as $curso)
                    @foreach($curso->courseClasses->where('status', $situacao) as $turma)
                        @php($hasTurma = true)
                        @include('portal.partials.course-class-card', ['turma' => $turma, 'tone' => match ($situacao) {
                            'em_andamento' => 'tertiary',
                            'cadastrado' => 'secondary',
                            default => 'primary',
                        }])
                    @endforeach
                @endforeach
            </div>
            @if(! $hasTurma)
                <p class="rounded-2xl border border-dashed border-slate-300 bg-white/70 py-14 text-center text-slate-500 dark:border-slate-700 dark:bg-slate-900/30">
                    Nenhuma turma nesta situação no momento.
                </p>
                @if(($situacao ?? '') !== 'concluido')
                    <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
                        Turmas já encerradas ficam na página
                        <a href="{{ route('portal.cursos.historico') }}" class="font-semibold underline decoration-slate-400 underline-offset-2 hover:text-slate-900 dark:hover:text-white">Histórico de turmas</a>.
                    </p>
                @endif
            @endif
        </div>

        <div class="mx-auto mt-12 flex max-w-7xl justify-center px-4 sm:px-6 lg:px-8">
            {{ $cursos->appends(['situacao' => $situacao])->links() }}
        </div>
    </section>
@endsection
