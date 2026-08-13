<x-layouts.aluno :title="$classLesson->title">
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('app.turmas.show', $classLesson->courseClass) }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">← {{ $classLesson->courseClass?->name }}</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <header>
                <h1 class="text-2xl font-bold text-white sm:text-3xl">{{ $classLesson->title }}</h1>
                <p class="mt-2 text-sm text-slate-400">
                    {{ $classLesson->date?->format('d/m/Y') }}
                    @if ($classLesson->start_time && $classLesson->end_time)
                        · {{ \Illuminate\Support\Str::substr($classLesson->start_time, 0, 5) }} às {{ \Illuminate\Support\Str::substr($classLesson->end_time, 0, 5) }}
                    @endif
                </p>
            </header>

            @if ($youtubeId)
                <div class="overflow-hidden rounded-3xl border border-slate-800 bg-black shadow-2xl ring-1 ring-white/5">
                    <div class="aspect-video w-full">
                        <iframe class="h-full w-full"
                                src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}?rel=0"
                                title="Vídeo da aula"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="lazy"></iframe>
                    </div>
                </div>
            @elseif ($classLesson->video_url)
                <div class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-4 text-sm text-amber-100">
                    O link informado não é um vídeo do YouTube. Peça ao instrutor para usar uma URL do YouTube (youtube.com ou youtu.be).
                </div>
            @else
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-sm text-slate-500">Esta aula ainda não tem vídeo cadastrado.</div>
            @endif

            @if ($classLesson->material_file_path)
                <a href="{{ route('app.aulas.material', $classLesson) }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-cyan-500/20">
                    Baixar material da aula
                    @if ($classLesson->material_file_name)
                        <span class="font-normal text-cyan-300/90">({{ $classLesson->material_file_name }})</span>
                    @endif
                    <span aria-hidden="true">↓</span>
                </a>
            @endif

            @if ($classLesson->material_url)
                <a href="{{ $classLesson->material_url }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-cyan-500/20">
                    Abrir link do material
                    <span aria-hidden="true">↗</span>
                </a>
            @endif

            @if ($canMarkOnlinePresence)
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5">
                    <p class="text-sm font-semibold text-slate-200">Presença nesta aula (online)</p>
                    <p class="mt-1 text-xs text-slate-500">
                        Confirme que assistiu à aula. A frequência conta para o percentual da turma (aulas cadastradas).
                        Data de referência: {{ $classLesson->date?->format('d/m/Y') ?? '—' }}.
                    </p>
                    @if (session('success'))
                        <p class="mt-3 text-sm font-medium text-emerald-400">{{ session('success') }}</p>
                    @endif
                    @if ($onlinePresenceConfirmed)
                        <p class="mt-4 text-sm text-emerald-300">Você já confirmou presença nesta aula.</p>
                    @else
                        <form method="post" action="{{ route('app.aulas.presenca', $classLesson) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-bold text-white transition hover:bg-emerald-500">
                                Confirmar presença nesta aula
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                <p class="text-xs font-bold uppercase text-slate-500">Seu progresso na turma</p>
                <p class="mt-2 text-sm text-slate-400">{{ $classLesson->courseClass?->course?->name }}</p>
                <div class="mt-4 space-y-4">
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-400">
                            <span>Quizzes</span>
                            <span>{{ $quizPct !== null ? $quizPct.'%' : '—' }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-cyan-500" style="width: {{ $quizPct ?? 0 }}%"></div>
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between text-xs font-semibold text-slate-400">
                            <span>Presença</span>
                            <span>{{ $presencePct !== null ? $presencePct.'%' : '—' }}</span>
                        </div>
                        <div class="mt-2 h-2 rounded-full bg-slate-800">
                            <div class="h-full rounded-full bg-emerald-500" style="width: {{ $presencePct ?? 0 }}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</x-layouts.aluno>
