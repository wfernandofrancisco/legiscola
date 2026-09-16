<x-layouts.admin>
    <x-slot name="title">{{ $licenca->catalogItem->titulo }}</x-slot>

    @php
        $item = $licenca->catalogItem;
        $ehPalestra = $item->tipo === \App\Enums\CatalogItemTipo::Palestra;
        $turmas = $licenca->course?->courseClasses ?? collect();
        $eventos = $licenca->events;
        $restantes = $ehPalestra ? $licenca->eventosRestantes() : $licenca->turmasRestantes();
        // Curso sem aula abriria turma vazia: o conteúdo todo mora no catálogo da direção.
        $semAulas = ! $ehPalestra && $item->lessons->isEmpty();
        $podeAbrir = ($ehPalestra ? $licenca->canOpenEvento() : $licenca->canOpenTurma()) && ! $semAulas;
    @endphp

    <x-page-header :title="$item->titulo" :subtitle="$item->resumo ?: 'Conteúdo liberado pela direção regional'"
        :action-href="route('admin.catalogo-regional.index')" action-text="Voltar" />

    <div class="mb-6 grid gap-4 lg:grid-cols-3">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Situação</p>
            <div class="mt-2">
                @if ($licenca->isUsable())
                    <x-badge color="green" text="Disponível" />
                @elseif ($licenca->isExpired())
                    <x-badge color="red" text="Prazo encerrado" />
                @else
                    <x-badge :color="$licenca->status->color()" :text="$licenca->status->label()" />
                @endif
            </div>
            @if ($licenca->exibir_ate)
                <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">
                    Disponível até {{ $licenca->exibir_ate->format('d/m/Y') }}
                </p>
            @endif
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                {{ $ehPalestra ? 'Edições' : 'Turmas' }}</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">
                {{ $ehPalestra ? $eventos->count() : $turmas->count() }}
            </p>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                @if ($restantes === null)
                    Sem limite
                @else
                    {{ $restantes }} {{ $ehPalestra ? 'edição(ões)' : 'turma(s)' }} restante(s)
                @endif
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">Conteúdo</p>
            <p class="mt-1 text-2xl font-semibold tabular-nums text-slate-900 dark:text-white">
                {{ $item->lessons->count() }}</p>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                {{ $item->lessons->count() === 1 ? 'aula' : 'aulas' }}
                @if ($item->workload_hours)
                    · {{ $item->workload_hours }} horas
                @endif
            </p>
        </div>
    </div>

    @if ($item->descricao)
        <div
            class="mb-6 rounded-2xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-700 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300">
            {!! nl2br(e($item->descricao)) !!}
        </div>
    @endif

    @if (!$ehPalestra || $item->lessons->isNotEmpty())
    <section class="mb-6">
        <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">
            {{ $ehPalestra ? 'Conteúdo desta palestra' : 'Aulas deste conteúdo' }}</h2>

        <div
            class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900">
            @forelse ($item->lessons as $aula)
                <div class="flex items-center gap-4 px-4 py-3">
                    <span
                        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-slate-100 text-xs font-semibold text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                        {{ $loop->iteration }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $aula->titulo }}</p>
                        @if ($aula->descricao)
                            <p class="mt-0.5 line-clamp-1 text-xs text-slate-500 dark:text-slate-400">
                                {{ $aula->descricao }}</p>
                        @endif
                    </div>
                    <div class="flex shrink-0 gap-1.5 text-[11px]">
                        @if ($aula->hasVideo())
                            <span
                                class="rounded-md bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Vídeo</span>
                        @endif
                        @if ($aula->hasMaterial())
                            <span
                                class="rounded-md bg-blue-50 px-2 py-0.5 font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">Material</span>
                        @endif
                    </div>
                </div>
            @empty
                <p class="px-4 py-6 text-center text-sm text-slate-500 dark:text-slate-400">
                    Este conteúdo ainda não tem aulas cadastradas.
                </p>
            @endforelse
        </div>
    </section>
    @endif

    @if ($ehPalestra && $eventos->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Edições já agendadas</h2>

            <div
                class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900">
                @foreach ($eventos as $evento)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $evento->title }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $evento->date_time->format('d/m/Y H:i') }}</p>
                        </div>
                        <a href="{{ route('admin.eventos.edit', $evento) }}"
                            class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Abrir
                            evento</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if (!$ehPalestra && $turmas->isNotEmpty())
        <section class="mb-6">
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Turmas já abertas</h2>

            <div
                class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900">
                @foreach ($turmas as $turma)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100">{{ $turma->name }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400">
                                {{ ucfirst(str_replace('_', ' ', $turma->status)) }}</p>
                        </div>
                        <a href="{{ route('admin.turmas.show', $turma) }}"
                            class="text-xs font-semibold text-indigo-600 hover:underline dark:text-indigo-400">Abrir
                            turma</a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
        <div class="border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <h2 class="text-sm font-semibold text-slate-900 dark:text-white">
                {{ $ehPalestra ? 'Agendar esta palestra' : 'Abrir uma turma' }}</h2>
            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                @if ($ehPalestra)
                    A palestra vira um evento da sua câmara: inscrições, presença e certificado ficam com você.
                @elseif ($semAulas)
                    Este curso ainda não tem aulas publicadas pela direção regional.
                @else
                    As {{ $item->lessons->count() }} aulas do diretor são o conteúdo. Aqui você monta a
                    <strong>grade desta turma</strong> (dias e horários). Presença fica em cada aula da grade.
                @endif
            </p>
        </div>

        @if (!$podeAbrir)
            <div class="p-5">
                <div
                    class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-800/50 dark:bg-amber-950/30 dark:text-amber-300">
                    @if ($semAulas)
                        A direção regional ainda não cadastrou as aulas deste curso. Abrir a turma agora deixaria os
                        alunos sem conteúdo — avise a direção para publicar as aulas.
                    @elseif ($licenca->isExpired())
                        O prazo deste conteúdo terminou em {{ $licenca->exibir_ate->format('d/m/Y') }}. Fale com a
                        direção regional para renovar.
                    @elseif (!$licenca->status->isUsable())
                        Este conteúdo está como <strong>{{ $licenca->status->label() }}</strong> e não pode ser usado
                        agora.
                    @elseif ($ehPalestra)
                        Você já agendou o máximo de {{ $licenca->max_turmas }} edição(ões) permitido nesta licença.
                    @else
                        Você já abriu o máximo de {{ $licenca->max_turmas }} turma(s) permitido nesta licença.
                    @endif
                </div>
            </div>
        @elseif ($ehPalestra)
            <form method="POST" action="{{ route('admin.catalogo-regional.eventos.store', $licenca) }}"
                class="space-y-6 p-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-form.input name="title" label="Título do evento" required
                            :value="old('title', $item->titulo)" />
                    </div>

                    @if ($licenca->locksPalestraModalidade())
                        <div>
                            <x-form.select name="modalidade_display" label="Modalidade" disabled
                                :options="\App\Enums\CatalogLicenseModalidade::options()"
                                :selected="$licenca->modalidade->value"
                                hint="Definido pela direção regional — não pode ser alterado." />
                        </div>
                    @endif

                    @if ($licenca->locksPalestraDate())
                        <div>
                            <x-form.input name="date_time_display" label="Data e hora" type="datetime-local" disabled
                                :value="$licenca->palestra_em->format('Y-m-d\TH:i')"
                                hint="Definido pela direção regional — não pode ser alterado." />
                            <input type="hidden" name="date_time" value="{{ $licenca->palestra_em->format('Y-m-d\TH:i') }}">
                        </div>
                    @else
                        <x-form.input name="date_time" label="Data e hora" type="datetime-local" required
                            :value="old('date_time')" />
                    @endif

                    <x-form.input name="palestrante_nome" label="Palestrante"
                        :value="old('palestrante_nome', $licenca->professor_nome)"
                        :hint="$licenca->professor_nome ? 'Sugestão da direção regional.' : null" />

                    @if ($licenca->locksPalestraSeats())
                        <div>
                            <x-form.input name="max_seats_display" label="Vagas" type="number" disabled
                                :value="$licenca->max_inscritos"
                                hint="Definido pela direção regional — não pode ser alterado." />
                            <input type="hidden" name="max_seats" value="{{ $licenca->max_inscritos }}">
                        </div>
                    @else
                        <x-form.input name="max_seats" label="Vagas" type="number"
                            :value="old('max_seats')"
                            hint="Em branco = sem limite." />
                    @endif

                    <div class="flex items-center gap-6 pt-1">
                        {{-- Checkbox desmarcado não é enviado; o hidden garante o "não". --}}
                        <input type="hidden" name="allow_online_registration" value="0" />
                        <x-form.checkbox name="allow_online_registration" label="Inscrição online"
                            :checked="old('allow_online_registration', true)" />
                        <input type="hidden" name="com_certificado" value="0" />
                        <x-form.checkbox name="com_certificado" label="Emite certificado"
                            :checked="old('com_certificado', false)" />
                    </div>

                    <x-form.date name="registration_starts_at" label="Inscrições abrem em"
                        :value="old('registration_starts_at')" />
                    <x-form.date name="registration_ends_at" label="Inscrições encerram em"
                        :value="old('registration_ends_at')" />
                </div>

                <div class="border-t border-slate-200 pt-5 dark:border-slate-700">
                    <p class="mb-4 text-sm font-semibold text-slate-900 dark:text-white">Local</p>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <x-form.input name="zipcode" label="CEP" :value="old('zipcode')" />
                        <div class="lg:col-span-2">
                            <x-form.input name="address" label="Endereço" :value="old('address')" />
                        </div>
                        <x-form.input name="number" label="Número" :value="old('number')" />
                        <x-form.input name="complement" label="Complemento" :value="old('complement')" />
                        <x-form.input name="district" label="Bairro" :value="old('district')" />
                        <x-form.input name="city" label="Cidade" :value="old('city', $licenca->tenant->cidade)" />
                        <x-form.input name="state" label="UF" maxlength="2"
                            :value="old('state', $licenca->tenant->estado)" />
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Agendar palestra
                    </button>
                </div>
            </form>
        @else
            <form method="POST" action="{{ route('admin.catalogo-regional.turmas.store', $licenca) }}"
                class="space-y-6 p-5">
                @csrf

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <x-form.input name="name" label="Nome da turma" required
                            :value="old('name', $item->titulo . ' — Turma ' . ($turmas->count() + 1))" />
                    </div>

                    <x-form.select name="tipo_turma" label="Formato" required
                        :options="['online' => 'Online', 'presencial' => 'Presencial']"
                        :selected="old('tipo_turma', 'online')" />

                    <x-form.input name="max_seats" label="Vagas" type="number" :value="old('max_seats')"
                        hint="Em branco ou 0 = sem limite." />

                    <x-form.date name="enrollment_start" label="Inscrições abrem em"
                        :value="old('enrollment_start')" />
                    <x-form.date name="enrollment_end" label="Inscrições encerram em" :value="old('enrollment_end')" />
                </div>

                <div class="border-t border-slate-200 pt-5 dark:border-slate-700">
                    <p class="mb-4 text-sm font-semibold text-slate-900 dark:text-white">Grade das aulas</p>
                    <p class="mb-4 text-xs text-slate-500 dark:text-slate-400">
                        Preencha a 1ª aula e o intervalo para sugerir as datas, depois ajuste cada aula se precisar (horário diferente, presencial etc.).
                    </p>

                    <div class="mb-5 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <x-form.date name="data_inicio" label="Data da 1ª aula" required
                            :value="old('data_inicio')" />

                        <x-form.input name="intervalo_dias" label="Intervalo entre aulas (dias)" type="number" required
                            :value="old('intervalo_dias', 7)" hint="7 = uma aula por semana." />

                        <x-form.input name="hora_inicio" label="Início" type="time" required
                            :value="old('hora_inicio', '19:00')" />

                        <x-form.input name="hora_fim" label="Término" type="time" required
                            :value="old('hora_fim', '21:00')" />
                    </div>

                    @php $catalogLessons = $item->lessons ?? collect(); @endphp
                    @if ($catalogLessons->isNotEmpty())
                        <div class="space-y-3" id="catalog-grade">
                            @foreach ($catalogLessons as $i => $catalogLesson)
                                <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-800/50">
                                    <input type="hidden" name="grade[{{ $i }}][catalog_lesson_id]" value="{{ $catalogLesson->id }}">
                                    <p class="mb-2 text-sm font-semibold text-slate-900 dark:text-white">{{ $loop->iteration }}. {{ $catalogLesson->titulo }}</p>
                                    <div class="grid grid-cols-1 gap-2 sm:grid-cols-4">
                                        <x-form.input :name="'grade['.$i.'][date]'" label="Data" type="date" :value="old('grade.'.$i.'.date')" />
                                        <x-form.input :name="'grade['.$i.'][start_time]'" label="Início" type="time" :value="old('grade.'.$i.'.start_time', '19:00')" />
                                        <x-form.input :name="'grade['.$i.'][end_time]'" label="Fim" type="time" :value="old('grade.'.$i.'.end_time', '21:00')" />
                                        <label class="mt-6 flex items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                                            <input type="hidden" name="grade[{{ $i }}][is_online]" value="0">
                                            <input type="checkbox" name="grade[{{ $i }}][is_online]" value="1" class="js-catalog-online"
                                                @checked(old('grade.'.$i.'.is_online', old('tipo_turma', 'online') === 'online'))>
                                            Online
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                        class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                        Criar turma com as aulas
                    </button>
                </div>
            </form>
        @endif
    </section>
    @push('scripts')
        <script>
            (function () {
                var start = document.querySelector('[name="data_inicio"]');
                var interval = document.querySelector('[name="intervalo_dias"]');
                var hStart = document.querySelector('[name="hora_inicio"]');
                var hEnd = document.querySelector('[name="hora_fim"]');
                var wrap = document.getElementById('catalog-grade');
                if (!start || !wrap) return;

                function addDays(iso, days) {
                    var d = new Date(iso + 'T00:00:00');
                    d.setDate(d.getDate() + days);
                    var m = String(d.getMonth() + 1).padStart(2, '0');
                    var day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                }

                function fill() {
                    if (!start.value) return;
                    var step = parseInt((interval && interval.value) || '7', 10) || 7;
                    wrap.querySelectorAll('input[type="date"]').forEach(function (input, i) {
                        input.value = addDays(start.value, i * step);
                    });
                    if (hStart) wrap.querySelectorAll('input[type="time"][name*="[start_time]"]').forEach(function (input) {
                        input.value = hStart.value;
                    });
                    if (hEnd) wrap.querySelectorAll('input[type="time"][name*="[end_time]"]').forEach(function (input) {
                        input.value = hEnd.value;
                    });
                }

                [start, interval, hStart, hEnd].forEach(function (el) {
                    if (el) el.addEventListener('change', fill);
                });
            })();
        </script>
    @endpush
</x-layouts.admin>
