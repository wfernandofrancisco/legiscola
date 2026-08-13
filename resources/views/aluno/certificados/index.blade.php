<x-layouts.aluno title="Certificados">
    <div class="mb-10 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-indigo-950/40 p-8 shadow-xl shadow-black/30">
        <p class="text-xs font-semibold uppercase tracking-wider text-emerald-400/90">Conquistas</p>
        <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">Seus certificados</h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">
            Baixe os PDFs de cursos concluídos ou de eventos com certificado emitido pela secretaria. Cada arquivo pode ser validado publicamente pelo código no rodapé do documento.
        </p>
    </div>

    @if ($certificates->isEmpty())
        <div class="rounded-3xl border border-dashed border-slate-700/80 bg-slate-900/30 p-12 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-800/80 ring-1 ring-white/5">
                <svg class="h-7 w-7 text-slate-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.967 3.746 3.746 0 0 1-3.967 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.967-1.043 3.745 3.745 0 0 1-1.043-3.967A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.967 3.746 3.746 0 0 1 3.967-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.967 1.043 3.745 3.745 0 0 1 1.043 3.967A3.745 3.745 0 0 1 21 12Z" />
                </svg>
            </div>
            <p class="text-base font-semibold text-slate-200">Nenhum certificado disponível</p>
            <p class="mx-auto mt-2 max-w-md text-sm text-slate-500">
                Quando você concluir uma turma ou participar de um evento com certificado, o arquivo aparecerá aqui após a emissão pela equipe.
            </p>
        </div>
    @else
        <ul class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach ($certificates as $certificate)
                @php
                    $isEvent = (bool) $certificate->event_id;
                    $title = $isEvent
                        ? ($certificate->event?->title ?? 'Evento')
                        : ($certificate->course?->name ?? 'Curso');
                    $subtitle = $isEvent
                        ? 'Certificado de participação no evento'
                        : 'Certificado de conclusão de curso';
                @endphp
                <li class="group relative flex flex-col overflow-hidden rounded-2xl border border-slate-800/90 bg-slate-900/50 shadow-lg shadow-black/20 transition hover:border-cyan-500/35 hover:bg-slate-900/80">
                    <div class="pointer-events-none absolute -right-8 -top-8 h-32 w-32 rounded-full bg-gradient-to-br from-cyan-500/20 to-indigo-600/10 blur-2xl transition group-hover:from-cyan-400/25 group-hover:to-indigo-500/15" aria-hidden="true"></div>
                    <div class="relative flex flex-1 flex-col p-6">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-[10px] font-bold uppercase tracking-wider {{ $isEvent ? 'bg-violet-500/15 text-violet-300 ring-1 ring-violet-400/25' : 'bg-emerald-500/15 text-emerald-300 ring-1 ring-emerald-400/25' }}">
                                {{ $isEvent ? 'Evento' : 'Curso' }}
                            </span>
                            <span class="rounded-lg bg-white/5 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-slate-500">PDF</span>
                        </div>
                        <h3 class="mt-4 text-lg font-bold leading-snug text-white group-hover:text-cyan-100">{{ $title }}</h3>
                        <p class="mt-1.5 text-xs leading-relaxed text-slate-500">{{ $subtitle }}</p>
                        <p class="mt-4 text-[11px] font-medium text-slate-500">
                            Emitido em <span class="text-slate-400">{{ $certificate->issued_at?->format('d/m/Y H:i') }}</span>
                        </p>
                        <div class="mt-6 flex flex-wrap gap-2 border-t border-slate-800/80 pt-5">
                            <a href="{{ route('app.certificados.baixar', $certificate) }}"
                               class="inline-flex flex-1 min-w-[8rem] items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-2.5 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:brightness-110 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400/80">
                                <svg class="h-4 w-4 shrink-0 opacity-90" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                Baixar
                            </a>
                            <a href="{{ route('certificados.validar.por-hash', $certificate->validation_hash) }}"
                               target="_blank" rel="noopener noreferrer"
                               class="inline-flex items-center justify-center rounded-xl border border-slate-600/80 bg-slate-800/40 px-4 py-2.5 text-xs font-semibold text-slate-300 transition hover:border-slate-500 hover:bg-slate-800/70 hover:text-white">
                                Validar
                            </a>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
</x-layouts.aluno>
