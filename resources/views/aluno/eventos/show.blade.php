<x-layouts.aluno :title="$event->title">
    <div class="mb-6">
        <a href="{{ route('app.eventos.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">← Meus eventos</a>
    </div>

    <div class="grid gap-8 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <header class="rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-violet-950/30 p-8 shadow-xl shadow-black/20">
                <p class="text-xs font-semibold uppercase tracking-wider text-violet-300/90">Evento</p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">{{ $event->title }}</h1>
                <p class="mt-3 text-sm text-slate-400">
                    {{ $event->date_time?->format('d/m/Y H:i') ?? 'Data a definir' }}
                </p>
                @if ($event->address || $event->city)
                    <p class="mt-2 text-sm text-slate-500">
                        {{ collect([$event->address, $event->number, $event->district, $event->city, $event->state])->filter()->implode(', ') }}
                    </p>
                @endif
                @if (filled($event->description))
                    <div class="prose prose-invert prose-sm mt-6 max-w-none text-slate-300">
                        {!! nl2br(e($event->description)) !!}
                    </div>
                @endif
            </header>

            @if ($event->isGeofenceCheckInEnabled())
                <section class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6">
                    <h2 class="text-base font-bold text-white">Chamada por georreferência</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-400">
                        Para registrar presença, use o GPS do seu celular no local do evento.
                        Raio permitido: <span class="font-semibold text-slate-300">{{ $event->geofence_raio_metros }} metros</span>.
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        Janela: {{ $event->presenca_inicio_em?->format('d/m/Y H:i') }}
                        até {{ $event->presenca_fim_em?->format('d/m/Y H:i') }}
                    </p>

                    @if ($enrollment->presente)
                        <div class="mt-5 rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200">
                            Presença confirmada
                            @if ($enrollment->checkin_em)
                                em {{ $enrollment->checkin_em->format('d/m/Y H:i') }}.
                            @else
                                .
                            @endif
                        </div>
                    @elseif (! $windowOpen)
                        <div class="mt-5 rounded-xl border border-amber-500/30 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                            @if (now()->lt($event->presenca_inicio_em))
                                A chamada ainda não começou. Volte no horário liberado.
                            @else
                                O horário para registrar presença já encerrou.
                            @endif
                        </div>
                    @else
                        <div class="mt-5 space-y-3">
                            <p id="geo-status" class="text-sm text-slate-400">Toque no botão para usar sua localização atual.</p>
                            <form id="geo-checkin-form" method="post" action="{{ route('app.eventos.presenca', $event) }}" class="space-y-3">
                                @csrf
                                <input type="hidden" name="latitude" id="checkin-latitude" value="">
                                <input type="hidden" name="longitude" id="checkin-longitude" value="">
                                <input type="hidden" name="accuracy" id="checkin-accuracy" value="">
                                <button type="button" id="geo-checkin-btn"
                                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-4 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60 sm:w-auto">
                                    Registrar minha presença
                                </button>
                            </form>
                        </div>
                    @endif
                </section>
            @else
                <section class="rounded-2xl border border-slate-800 bg-slate-900/40 p-6 text-sm text-slate-400">
                    Este evento não usa chamada por georreferência. A presença é registrada pela organização no local.
                </section>
            @endif
        </div>

        <aside class="space-y-4">
            <div class="rounded-2xl border border-slate-800 bg-slate-900/60 p-5">
                <p class="text-xs font-bold uppercase text-slate-500">Sua inscrição</p>
                <p class="mt-3 text-sm font-semibold {{ $enrollment->presente ? 'text-emerald-300' : 'text-slate-200' }}">
                    {{ $enrollment->presente ? 'Presente' : 'Inscrito' }}
                </p>
                @if ($event->com_certificado)
                    <p class="mt-3 text-xs leading-relaxed text-slate-500">
                        Evento com certificado: a emissão depende da presença confirmada.
                    </p>
                @endif
            </div>
        </aside>
    </div>

    @if ($canCheckIn)
        @push('scripts')
            <script>
                (function () {
                    var btn = document.getElementById('geo-checkin-btn');
                    var form = document.getElementById('geo-checkin-form');
                    var statusEl = document.getElementById('geo-status');
                    var latInput = document.getElementById('checkin-latitude');
                    var lngInput = document.getElementById('checkin-longitude');
                    var accInput = document.getElementById('checkin-accuracy');
                    if (!btn || !form || !statusEl) return;

                    function setStatus(msg, isError) {
                        statusEl.textContent = msg;
                        statusEl.className = 'text-sm ' + (isError ? 'text-rose-300' : 'text-slate-400');
                    }

                    btn.addEventListener('click', function () {
                        if (!navigator.geolocation) {
                            setStatus('Seu navegador não oferece geolocalização. Use um celular com GPS.', true);
                            return;
                        }

                        btn.disabled = true;
                        setStatus('Obtendo sua localização…', false);

                        navigator.geolocation.getCurrentPosition(
                            function (pos) {
                                latInput.value = pos.coords.latitude;
                                lngInput.value = pos.coords.longitude;
                                accInput.value = pos.coords.accuracy != null ? Math.round(pos.coords.accuracy) : '';
                                setStatus('Localização obtida. Enviando…', false);
                                form.submit();
                            },
                            function (err) {
                                btn.disabled = false;
                                var msg = 'Não foi possível obter a localização.';
                                if (err && err.code === 1) msg = 'Permissão de localização negada. Ative o GPS e permita o acesso.';
                                if (err && err.code === 2) msg = 'Localização indisponível. Verifique o GPS e tente novamente.';
                                if (err && err.code === 3) msg = 'Tempo esgotado ao obter a localização. Tente novamente.';
                                setStatus(msg, true);
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 20000,
                                maximumAge: 0
                            }
                        );
                    });
                })();
            </script>
        @endpush
    @endif
</x-layouts.aluno>
