<div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
    <table class="w-full text-sm">
        <thead class="border-b border-slate-100 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/90">
            <tr>
                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Quiz</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Perguntas</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Turmas</th>
                <th class="px-4 py-3 text-left font-semibold text-slate-700 dark:text-slate-300">Status</th>
                <th class="px-4 py-3 text-right font-semibold text-slate-700 dark:text-slate-300">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @forelse ($quizzes as $quiz)
                <tr class="hover:bg-slate-50/90 dark:hover:bg-slate-800/50">
                    <td class="px-4 py-4">
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $quiz->title }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            Nota mín.: {{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%
                        </p>
                    </td>
                    <td class="px-4 py-4 text-slate-700 dark:text-slate-300">{{ $quiz->questions->count() }}</td>
                    <td class="px-4 py-4">
                        <div class="flex flex-wrap gap-1">
                            @foreach ($quiz->courseClasses as $courseClass)
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-200">
                                    {{ $courseClass->name }}
                                </span>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-4">
                        <x-badge :color="$quiz->is_active ? 'green' : 'gray'" :text="$quiz->is_active ? 'Ativo' : 'Inativo'" />
                    </td>
                    <td class="px-4 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('responsible.quizzes.show', $quiz) }}" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">Ver</a>
                            <a href="{{ route('responsible.quizzes.print', $quiz) }}" target="_blank" rel="noopener" class="rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-semibold text-white hover:bg-slate-800 dark:bg-cyan-600 dark:hover:bg-cyan-500">PDF</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-500 dark:text-slate-400">Nenhum quiz encontrado neste espaço institucional.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($quizzes->hasPages())
        <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-700">
            {{ $quizzes->links() }}
        </div>
    @endif
</div>
