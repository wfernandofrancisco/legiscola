<x-layouts.aluno title="Pesquisas de satisfação">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-cyan-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400/90">Feedback</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Pesquisas de satisfação</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Avalie sua experiência nas turmas. Quando a pesquisa for obrigatória, ela precisa ser respondida antes da emissão do certificado.
        </p>
    </div>

    @if ($items->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-700/80 bg-slate-900/30 p-12 text-center">
            <p class="text-base font-semibold text-slate-200">Nenhuma pesquisa disponível</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Quando a secretaria vincular uma pesquisa à sua turma, ela aparecerá aqui.
            </p>
        </div>
    @else
        <ul class="grid gap-5 sm:grid-cols-2">
            @foreach ($items as $item)
                @php
                    /** @var \App\Models\CourseClass $turma */
                    $turma = $item['turma'];
                    /** @var \App\Models\SatisfactionSurvey $survey */
                    $survey = $item['survey'];
                @endphp
                <li class="flex flex-col overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/50 p-6 shadow-lg shadow-black/20">
                    <div class="flex flex-wrap gap-2">
                        @if ($item['completed'])
                            <span class="rounded-full bg-emerald-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-300 ring-1 ring-emerald-400/25">Respondida</span>
                        @elseif ($item['required'])
                            <span class="rounded-full bg-amber-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-amber-300 ring-1 ring-amber-400/25">Obrigatória</span>
                        @else
                            <span class="rounded-full bg-cyan-500/15 px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider text-cyan-300 ring-1 ring-cyan-400/25">Disponível</span>
                        @endif
                    </div>
                    <h3 class="mt-4 text-lg font-bold text-white">{{ $survey->title }}</h3>
                    <p class="mt-1 text-xs text-slate-500">
                        {{ $turma->course?->name ?? 'Curso' }} · {{ $turma->name }}
                    </p>
                    @if ($survey->description)
                        <p class="mt-3 line-clamp-3 text-sm text-slate-400">{{ $survey->description }}</p>
                    @endif
                    <div class="mt-6">
                        <a href="{{ route('app.pesquisas-satisfacao.show', $turma) }}"
                           class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110">
                            {{ $item['completed'] ? 'Ver pesquisa' : 'Responder agora' }}
                        </a>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.aluno>
