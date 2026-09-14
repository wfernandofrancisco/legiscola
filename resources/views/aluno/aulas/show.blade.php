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
                @if (filled($professorNome))
                    <p class="mt-1 text-sm text-slate-400">
                        <span class="font-semibold text-slate-300">Professor:</span> {{ $professorNome }}
                    </p>
                @endif
            </header>

            @if ($videoEmbedUrl)
                <div class="overflow-hidden rounded-3xl border border-slate-800 bg-black shadow-2xl ring-1 ring-white/5">
                    @if ($videoSourceLabel)
                        <div class="flex items-center justify-between border-b border-white/10 bg-slate-950/80 px-4 py-2">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">{{ $videoSourceLabel }}</span>
                        </div>
                    @endif
                    <div class="aspect-video w-full">
                        <iframe class="h-full w-full"
                                src="{{ $videoEmbedUrl }}?rel=0"
                                title="Vídeo da aula"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="lazy"></iframe>
                    </div>
                </div>
            @elseif ($videoNative && $videoUrl)
                <div class="overflow-hidden rounded-3xl border border-slate-800 bg-black shadow-2xl ring-1 ring-white/5">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-white/10 bg-slate-950/80 px-4 py-2">
                        @if ($videoSourceLabel)
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300/90">{{ $videoSourceLabel }}</span>
                        @else
                            <span></span>
                        @endif
                        <div class="flex items-center gap-1.5" data-video-speed>
                            <span class="mr-1 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Velocidade</span>
                            @foreach ([0.75, 1, 1.25, 1.5, 1.75, 2] as $rate)
                                <button type="button"
                                        data-rate="{{ $rate }}"
                                        class="rounded-lg px-2 py-1 text-xs font-bold transition {{ $rate == 1 ? 'bg-cyan-500 text-slate-950' : 'bg-white/5 text-slate-300 hover:bg-white/10' }}">
                                    {{ rtrim(rtrim(number_format($rate, 2, '.', ''), '0'), '.') }}x
                                </button>
                            @endforeach
                        </div>
                    </div>
                    <div class="aspect-video w-full">
                        <video id="aula-video-player" class="h-full w-full" controls preload="metadata" playsinline>
                            <source src="{{ $videoUrl }}" type="{{ $videoMimeType ?? 'video/mp4' }}">
                            Seu navegador não reproduz este vídeo.
                            <a href="{{ $videoUrl }}" class="text-cyan-300 underline">Abrir o arquivo</a>
                        </video>
                    </div>
                </div>
                @push('scripts')
                    <script>
                        (function () {
                            var video = document.getElementById('aula-video-player');
                            var wrap = document.querySelector('[data-video-speed]');
                            if (!video || !wrap) return;

                            var buttons = wrap.querySelectorAll('button[data-rate]');
                            var storageKey = 'aula-video-speed';

                            function applyRate(rate) {
                                video.playbackRate = rate;
                                try { localStorage.setItem(storageKey, String(rate)); } catch (e) {}
                                buttons.forEach(function (btn) {
                                    var active = parseFloat(btn.getAttribute('data-rate')) === rate;
                                    btn.classList.toggle('bg-cyan-500', active);
                                    btn.classList.toggle('text-slate-950', active);
                                    btn.classList.toggle('bg-white/5', !active);
                                    btn.classList.toggle('text-slate-300', !active);
                                });
                            }

                            buttons.forEach(function (btn) {
                                btn.addEventListener('click', function () {
                                    applyRate(parseFloat(btn.getAttribute('data-rate')));
                                });
                            });

                            var saved = null;
                            try { saved = parseFloat(localStorage.getItem(storageKey)); } catch (e) {}
                            if (saved && !isNaN(saved)) {
                                applyRate(saved);
                            }
                        })();
                    </script>
                @endpush
            @elseif ($videoUrl)
                <div class="space-y-2">
                    @if ($videoSourceLabel)
                        <p class="text-[11px] font-bold uppercase tracking-wider text-slate-500">{{ $videoSourceLabel }}</p>
                    @endif
                    <a href="{{ $videoUrl }}" target="_blank" rel="noopener noreferrer"
                       class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-cyan-500/20">
                        Assistir ao vídeo da aula
                        <span aria-hidden="true">↗</span>
                    </a>
                </div>
            @else
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6 text-sm text-slate-500">Esta aula ainda não tem vídeo cadastrado.</div>
            @endif

            @if ($materialDownloadRoute)
                <a href="{{ $materialDownloadRoute }}"
                   class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-cyan-500/20">
                    Baixar material da aula
                    @if ($materialName)
                        <span class="font-normal text-cyan-300/90">({{ $materialName }})</span>
                    @endif
                    <span aria-hidden="true">↓</span>
                </a>
            @elseif ($materialUrl)
                <a href="{{ $materialUrl }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-2 rounded-xl border border-cyan-500/40 bg-cyan-500/10 px-4 py-3 text-sm font-bold text-cyan-200 transition hover:bg-cyan-500/20">
                    Abrir material da aula
                    @if ($materialName)
                        <span class="font-normal text-cyan-300/90">({{ $materialName }})</span>
                    @endif
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
