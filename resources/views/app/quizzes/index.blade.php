<x-layouts.aluno title="Quizzes">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-indigo-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400/90">Avaliação online</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Quizzes das suas turmas</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Cada quiz segue a janela de abertura definida pela escola na turma. Você também encontra os atalhos na página da turma.
        </p>
    </div>

    <div class="space-y-4">
    @forelse ($quizzes as $quiz)
        @php
            $lastAttempt = $quiz->attempts->first();
            $cc = $quiz->courseClasses->first(fn ($c) => $courseClassIds->contains($c->id));
            $pivot = $cc?->pivot;
            $open = $pivot && \App\Support\CourseClassQuizAvailability::isOpenNow($pivot->opens_at, $pivot->closes_at);
            $statusLabel = $pivot
                ? \App\Support\CourseClassQuizAvailability::statusLabel($pivot->opens_at, $pivot->closes_at)
                : '—';
        @endphp
        <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 shadow-lg shadow-black/20">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 flex-1">
                    <h3 class="text-lg font-bold text-white">{{ $quiz->title }}</h3>
                    <p class="mt-1 text-sm text-slate-400">
                        Turma: <span class="text-slate-200">{{ $cc?->name ?? '—' }}</span>
                        · Nota mínima: {{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%
                    </p>
                    <p class="mt-2 inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide {{ $open ? 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/30' : 'bg-slate-700/50 text-slate-400 ring-1 ring-white/10' }}">
                        {{ $statusLabel }}
                    </p>
                </div>
                @if ($open)
                    <a href="{{ route('app.quizzes.show', $quiz) }}"
                       class="inline-flex shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 hover:brightness-110">
                        Fazer quiz
                    </a>
                @else
                    <span class="inline-flex shrink-0 cursor-not-allowed items-center justify-center rounded-xl border border-slate-700 bg-slate-800/50 px-5 py-2.5 text-sm font-semibold text-slate-500">
                        Indisponível
                    </span>
                @endif
            </div>

            @if ($lastAttempt)
                <div class="mt-4 rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                    Última tentativa:
                    <strong class="text-white">{{ number_format((float) $lastAttempt->score, 2, ',', '.') }}%</strong>
                    ({{ $lastAttempt->correct_answers }}/{{ $lastAttempt->total_questions }}) —
                    {{ $lastAttempt->passed ? 'Aprovado' : 'Não aprovado' }}
                </div>
            @endif
        </div>
    @empty
        <div class="rounded-3xl border border-dashed border-slate-700/80 bg-slate-900/30 p-12 text-center">
            <p class="font-semibold text-slate-200">Nenhum quiz ativo</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">Não há quizzes liberados para as turmas em que você está matriculado.</p>
        </div>
    @endforelse
    </div>
</x-layouts.aluno>
