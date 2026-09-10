<x-layouts.director>
    <x-page-header :title="$tenant->display_name"
        :subtitle="($tenant->cidade ?: '—') . ' / ' . $tenant->estado"
        :action-href="route('diretor.clientes.index')" action-text="Voltar" />

    @include('director.partials._stat-cards', [
        'stats' => [
            ['label' => 'Cursos', 'value' => $numeros['cursos'] ?? 0],
            ['label' => 'Professores', 'value' => $numeros['professores'] ?? 0],
            ['label' => 'Alunos', 'value' => $numeros['alunos'] ?? 0],
            ['label' => 'Matrículas', 'value' => $numeros['matriculas'] ?? 0],
            ['label' => 'Turmas', 'value' => $numeros['turmas_total'] ?? 0],
            ['label' => 'Eventos futuros', 'value' => $numeros['eventos_futuros'] ?? 0],
        ],
    ])

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="lg:col-span-2">
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Turmas recentes</h2>

            @if ($turmas_recentes->isEmpty())
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                    Esta câmara ainda não abriu turmas.
                </div>
            @else
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Turma</th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Situação</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Matrículas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($turmas_recentes as $turma)
                                <tr>
                                    <td class="px-4 py-3">
                                        <p class="text-sm font-medium text-slate-900 dark:text-slate-100">
                                            {{ $turma->name }}</p>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $turma->course?->name ?? 'Curso removido' }}</p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <x-badge :color="match ($turma->status) {
                                            'em_andamento' => 'green',
                                            'inscricao' => 'yellow',
                                            'concluido' => 'blue',
                                            'cancelado' => 'red',
                                            default => 'gray',
                                        }"
                                            :text="ucfirst(str_replace('_', ' ', $turma->status))" />
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $turma->enrollments_count }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>

        <section>
            <h2 class="mb-3 text-lg font-semibold text-slate-900 dark:text-white">Cursos com mais procura</h2>

            @if ($top_cursos->isEmpty())
                <div
                    class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
                    Ainda sem matrículas registradas.
                </div>
            @else
                <ol
                    class="divide-y divide-slate-200 overflow-hidden rounded-2xl border border-slate-200 bg-white dark:divide-slate-700 dark:border-slate-700 dark:bg-slate-900">
                    @foreach ($top_cursos as $curso)
                        <li class="flex items-center justify-between gap-3 px-4 py-3">
                            <span class="text-sm text-slate-800 dark:text-slate-200">{{ $curso->curso }}</span>
                            <span
                                class="shrink-0 rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-semibold tabular-nums text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                                {{ $curso->matriculas }}
                            </span>
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>
    </div>
</x-layouts.director>
