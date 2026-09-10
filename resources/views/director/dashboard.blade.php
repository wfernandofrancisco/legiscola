<x-layouts.director>
    <x-page-header title="Panorama"
        :subtitle="'Acompanhamento das câmaras em ' . implode(', ', $ufs)"
        :items="[
            ['title' => 'câmaras', 'value' => $summary['camaras'], 'color' => 'sky'],
            ['title' => 'UFs', 'value' => count($ufs), 'color' => 'violet'],
        ]" />

    @include('director.partials._stat-cards', [
        'stats' => [
            ['label' => 'Câmaras', 'value' => $summary['camaras']],
            ['label' => 'Alunos', 'value' => $summary['alunos']],
            ['label' => 'Matrículas', 'value' => $summary['matriculas']],
            ['label' => 'Em inscrição', 'value' => $summary['turmas_inscricao'], 'hint' => 'turmas'],
            ['label' => 'Em andamento', 'value' => $summary['turmas_em_andamento'], 'hint' => 'turmas'],
            ['label' => 'Concluídas', 'value' => $summary['turmas_concluidas'], 'hint' => 'turmas'],
        ],
    ])

    @if ($grupos->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Nenhuma câmara cadastrada nas suas UFs.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Assim que a Central cadastrar um cliente em {{ implode(' ou ', $ufs) }}, ele aparece aqui
                automaticamente.
            </p>
        </div>
    @endif

    @foreach ($grupos as $grupo)
        <section class="mb-6">
            <div class="mb-3 flex items-baseline justify-between gap-3">
                <h2 class="text-lg font-semibold text-slate-900 dark:text-white">
                    {{ $grupo['estado'] }}
                    <span class="ml-1 text-sm font-normal text-slate-500 dark:text-slate-400">
                        {{ $grupo['camaras'] }} {{ $grupo['camaras'] === 1 ? 'câmara' : 'câmaras' }}
                    </span>
                </h2>
                <a href="{{ route('diretor.clientes.index', ['uf' => $grupo['uf']]) }}"
                    class="text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400">Ver detalhes</a>
            </div>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-800/60">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Câmara</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Cursos</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Em inscrição</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Em andamento</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Concluídas</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Professores</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Alunos</th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                    Matrículas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($grupo['linhas'] as $row)
                                <tr class="transition hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('diretor.clientes.show', $row['tenant']) }}"
                                            class="text-sm font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                                            {{ $row['tenant']->display_name }}
                                        </a>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">
                                            {{ $row['tenant']->cidade ?: '—' }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['cursos'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['turmas_inscricao'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['turmas_em_andamento'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['turmas_concluidas'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['professores'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['alunos'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                        {{ $row['matriculas'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 dark:bg-slate-800/60">
                            <tr class="text-sm font-semibold text-slate-900 dark:text-white">
                                <td class="px-4 py-3">Total {{ $grupo['uf'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['cursos'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['turmas_inscricao'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['turmas_em_andamento'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['turmas_concluidas'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['professores'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['alunos'] }}</td>
                                <td class="px-4 py-3 text-right tabular-nums">{{ $grupo['matriculas'] }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </section>
    @endforeach

    @if ($topCursos->isNotEmpty())
        <section>
            <h2 class="mb-1 text-lg font-semibold text-slate-900 dark:text-white">Cursos com mais procura</h2>
            <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">
                Ordenado por número de matrículas na sua região.
            </p>

            <div
                class="overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Curso</th>
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Câmara</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Matrículas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach ($topCursos as $curso)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ $curso->curso }}</td>
                                <td class="px-4 py-3 text-sm text-slate-600 dark:text-slate-300">
                                    {{ $curso->camara }} <span class="text-slate-400">/ {{ $curso->uf }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm tabular-nums text-slate-700 dark:text-slate-200">
                                    {{ $curso->matriculas }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-layouts.director>
