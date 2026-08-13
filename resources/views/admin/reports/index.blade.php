<x-layouts.admin>
    <x-slot name="title">Relatórios do sistema</x-slot>
    <x-slot name="scripts">
        @vite(['resources/js/admin-reports.js'])
    </x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $fmt = fn ($n) => number_format((int) $n, 0, ',', '.');
        $turmasTotal = array_sum($classStatusCounts ?? []);
        $matriculasTotal = array_sum($enrollmentStatusAll ?? []);
        $novasMatriculas = array_sum($newEnrollmentsByStatus ?? []);
        $pdfHref = route('admin.relatorios.sistema.pdf', [
            'data_inicio' => $start->format('Y-m-d'),
            'data_fim' => $end->format('Y-m-d'),
        ]);
        $hasClassPie = ! empty(array_filter($classStatusCounts ?? []));
        $hasSexoPie = $studentsInPeriodCount > 0;
    @endphp

    <script type="application/json" id="admin-reports-charts-data">{!! json_encode($chartsPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}</script>

    <x-page-header title="Relatórios do sistema"
        subtitle="Indicadores, tendências diárias e gráficos de alunos, turmas e matrículas (tela interativa; PDF continua resumido)."
        :items="[
            ['title' => 'alunos na base', 'value' => $fmt($totalStudents), 'color' => 'indigo'],
            ['title' => 'período', 'value' => $start->format('d/m/Y') . ' — ' . $end->format('d/m/Y'), 'color' => 'sky'],
        ]" />

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ $pdfHref }}" target="_blank" rel="noopener"
            class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 dark:bg-white dark:text-slate-900 dark:hover:bg-slate-200">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Baixar PDF
        </a>
    </div>

    <form method="GET" action="{{ route('admin.relatorios.sistema') }}" class="mb-8">
        <x-filter-panel title="Período do relatório"
            subtitle="Gráficos de linha usam cada dia do intervalo. Perfil (idade, sexo, etc.) considera alunos cadastrados no período. Desistências usam a data de atualização da matrícula."
            :reset-href="request()->hasAny(['data_inicio', 'data_fim']) ? route('admin.relatorios.sistema') : null">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <x-form.input type="date" label="Data início" name="data_inicio"
                    value="{{ old('data_inicio', $start->format('Y-m-d')) }}" required />
                <x-form.input type="date" label="Data fim" name="data_fim"
                    value="{{ old('data_fim', $end->format('Y-m-d')) }}" required />
            </div>
            <div class="mt-4 flex flex-wrap gap-2">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
                    Atualizar indicadores
                </button>
            </div>
        </x-filter-panel>
    </form>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Alunos cadastrados</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $fmt($totalStudents) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Com matrícula ativa</p>
            <p class="mt-2 text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ $fmt($matriculatedDistinct) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Turmas (total)</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $fmt($turmasTotal) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Matrículas (vínculos)</p>
            <p class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $fmt($matriculasTotal) }}</p>
        </div>
    </div>

    <div class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Cursos cadastrados</p>
            <p class="mt-2 text-2xl font-bold text-violet-600 dark:text-violet-400">{{ $fmt($totalCourses) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Professores</p>
            <p class="mt-2 text-2xl font-bold text-sky-600 dark:text-sky-400">{{ $fmt($totalTeachers) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Certificados emitidos no período</p>
            <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $fmt($certificatesIssuedInPeriod) }}</p>
        </div>
    </div>

    <div class="mb-8 rounded-2xl border border-indigo-200 bg-indigo-50/80 p-5 dark:border-indigo-900/50 dark:bg-indigo-950/40">
        <h2 class="text-sm font-bold uppercase tracking-wide text-indigo-800 dark:text-indigo-200">No período selecionado</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Novos cadastros de alunos</p>
                <p class="text-2xl font-bold text-indigo-950 dark:text-white">{{ $fmt($studentsInPeriodCount) }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Novas matrículas (registros)</p>
                <p class="text-2xl font-bold text-indigo-950 dark:text-white">{{ $fmt($novasMatriculas) }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Desistências (atualiz. matrícula)</p>
                <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $fmt($withdrawalsInPeriod) }}</p>
            </div>
            <div>
                <p class="text-xs text-indigo-700 dark:text-indigo-300">Turmas criadas</p>
                <p class="text-2xl font-bold text-indigo-950 dark:text-white">{{ $fmt(array_sum($classStatusInPeriod ?? [])) }}</p>
            </div>
        </div>
    </div>

    <section class="mb-10 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-gray-800">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Gráficos</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Linha (evolução diária), rosca (partes do todo) e barras (comparar categorias). Passe o cursor para ver valores.</p>

        <div class="mt-6 space-y-10">
            <div>
                <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Movimentação diária no período</h3>
                <div class="h-72 w-full min-h-[16rem] rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                    <canvas id="chart-report-line" aria-label="Gráfico de linha"></canvas>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Turmas por situação (base)</h3>
                    @if ($hasClassPie)
                        <div class="h-64 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                            <canvas id="chart-class-status" aria-label="Gráfico rosca turmas"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Sem turmas cadastradas.</p>
                    @endif
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Sexo — alunos novos no período</h3>
                    @if ($hasSexoPie)
                        <div class="h-64 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                            <canvas id="chart-sexo" aria-label="Gráfico rosca sexo"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum aluno novo no período para montar o gráfico.</p>
                    @endif
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-3">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Faixa etária (novos no período)</h3>
                    <div class="h-64 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-age" aria-label="Gráfico barras idade"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Escolaridade (novos no período)</h3>
                    <div class="h-64 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-escolaridade" aria-label="Gráfico barras escolaridade"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Bairro — top (novos no período)</h3>
                    <div class="h-64 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-bairro" aria-label="Gráfico barras bairro"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Matrículas por status — base completa</h3>
                    <div class="h-72 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-enrollment-base" aria-label="Gráfico barras matrículas base"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Novas matrículas no período (status atual)</h3>
                    <div class="h-72 w-full rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-enrollment-new" aria-label="Gráfico barras matrículas novas"></canvas>
                    </div>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Desistências no período — por curso</h3>
                    <div class="h-72 w-full min-h-[12rem] rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-withdrawals" aria-label="Gráfico barras desistências"></canvas>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-slate-700 dark:text-slate-300">Taxa de conclusão — turmas encerradas (top cursos)</h3>
                    <div class="h-72 w-full min-h-[12rem] rounded-xl border border-slate-100 bg-slate-50/80 p-2 dark:border-slate-600 dark:bg-slate-900/40">
                        <canvas id="chart-completion" aria-label="Gráfico barras conclusão"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="space-y-8">
        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Alunos novos no período — tabelas</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mesmos dados dos gráficos acima, em formato tabular.</p>
            </div>
            @if ($studentsInPeriodCount === 0)
                <p class="px-6 py-8 text-sm text-gray-500 dark:text-gray-400">Nenhum aluno cadastrado neste período.</p>
            @else
                <div class="grid gap-6 p-6 lg:grid-cols-2">
                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Faixa etária</h3>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600">
                            <table class="w-full text-sm">
                                @foreach ($ageBuckets as $row)
                                    <tr class="border-t border-gray-100 dark:border-gray-700">
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['label'] }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Sexo</h3>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600">
                            <table class="w-full text-sm">
                                @foreach ($sexoCounts as $row)
                                    <tr class="border-t border-gray-100 dark:border-gray-700">
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['label'] }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Escolaridade</h3>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600">
                            <table class="w-full text-sm">
                                @foreach ($escolaridadeCounts as $row)
                                    <tr class="border-t border-gray-100 dark:border-gray-700">
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['label'] }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                    <div>
                        <h3 class="mb-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Bairro (top)</h3>
                        <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-600">
                            <table class="w-full text-sm">
                                @foreach (collect($bairroCounts)->take(12) as $row)
                                    <tr class="border-t border-gray-100 dark:border-gray-700">
                                        <td class="px-4 py-2 text-gray-700 dark:text-gray-300">{{ $row['label'] }}</td>
                                        <td class="px-4 py-2 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($row['total']) }}</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Turmas por situação</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Visão geral da base e turmas criadas no período.</p>
            </div>
            <div class="overflow-x-auto p-6">
                <table class="w-full min-w-[480px] text-sm">
                    <thead class="bg-gray-50 text-left dark:bg-gray-700">
                        <tr>
                            <th class="rounded-tl-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Situação</th>
                            <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Total na base</th>
                            <th class="rounded-tr-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Criadas no período</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($classStatusLabels as $key => $label)
                            <tr>
                                <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $label }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">{{ $fmt($classStatusCounts[$key] ?? 0) }}</td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-300">{{ $fmt($classStatusInPeriod[$key] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Matrículas por status</h2>
            </div>
            <div class="grid gap-6 p-6 lg:grid-cols-2">
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">Base completa</h3>
                    <table class="w-full text-sm">
                        @foreach ($enrollmentStatusLabels as $key => $label)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $label }}</td>
                                <td class="py-2 text-right font-semibold">{{ $fmt($enrollmentStatusAll[$key] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                <div>
                    <h3 class="mb-2 text-sm font-semibold text-gray-600 dark:text-gray-400">Novas matrículas no período (por status atual)</h3>
                    <table class="w-full text-sm">
                        @foreach ($enrollmentStatusLabels as $key => $label)
                            <tr class="border-t border-gray-100 dark:border-gray-700">
                                <td class="py-2 text-gray-700 dark:text-gray-300">{{ $label }}</td>
                                <td class="py-2 text-right font-semibold">{{ $fmt($newEnrollmentsByStatus[$key] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Desistências no período — por curso</h2>
            </div>
            <div class="overflow-x-auto p-6">
                @if ($withdrawalsByCourse->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Nenhuma desistência registrada com atualização neste período.</p>
                @else
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-left dark:bg-gray-700">
                            <tr>
                                <th class="rounded-tl-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Curso</th>
                                <th class="rounded-tr-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Desistências</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @foreach ($withdrawalsByCourse as $row)
                                <tr>
                                    <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $row->course_name }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">{{ $fmt($row->total) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </section>

        <section class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="border-b border-gray-100 px-6 py-4 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Taxa de conclusão por curso</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Turmas encerradas: concluídos ÷ total de matrículas na turma.</p>
            </div>
            <div class="p-6">
                @if ($bestCompletion)
                    <p class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950/50 dark:text-emerald-200">
                        <span class="font-semibold">Maior taxa:</span> {{ $bestCompletion['course_name'] }}
                        — {{ number_format($bestCompletion['rate'], 1, ',', '.') }}%
                        ({{ $fmt($bestCompletion['concluded']) }} / {{ $fmt($bestCompletion['total']) }} matrículas em turmas concluídas)
                    </p>
                @endif
                @if ($completionByCourse->isEmpty())
                    <p class="text-sm text-gray-500 dark:text-gray-400">Ainda não há turmas concluídas com matrículas para calcular.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[520px] text-sm">
                            <thead class="bg-gray-50 text-left dark:bg-gray-700">
                                <tr>
                                    <th class="rounded-tl-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200">Curso</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Concluídos</th>
                                    <th class="px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">Total</th>
                                    <th class="rounded-tr-lg px-4 py-3 font-semibold text-gray-700 dark:text-gray-200 text-right">%</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach ($completionByCourse as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-800 dark:text-gray-200">{{ $row['course_name'] }}</td>
                                        <td class="px-4 py-3 text-right">{{ $fmt($row['concluded']) }}</td>
                                        <td class="px-4 py-3 text-right">{{ $fmt($row['total']) }}</td>
                                        <td class="px-4 py-3 text-right font-semibold">{{ number_format($row['rate'], 1, ',', '.') }}%</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>
    </div>
</x-layouts.admin>
