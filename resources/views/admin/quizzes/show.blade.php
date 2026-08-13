<x-layouts.admin>
    <x-slot name="title">Visualizar Quiz</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header :title="$quiz->title" subtitle="Detalhes do quiz, turmas vinculadas e gabarito." />

    <div class="space-y-6">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Nota minima</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">Status</p>
                    <p class="mt-1">
                        <x-badge :color="$quiz->is_active ? 'green' : 'gray'" :text="$quiz->is_active ? 'Ativo' : 'Inativo'" />
                    </p>
                </div>
                <div class="flex items-end gap-2">
                    <a href="{{ route('admin.quizzes.edit', $quiz) }}"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Editar</a>
                    <a href="{{ route('admin.quizzes.print', $quiz) }}" target="_blank"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-700">Imprimir</a>
                </div>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <h3 class="text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Turmas vinculadas</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($quiz->courseClasses as $courseClass)
                    <span
                        class="rounded-full px-3 py-1 text-xs font-semibold {{ $courseClass->pivot->is_active ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200' }}">
                        {{ $courseClass->name }} - {{ $courseClass->pivot->is_active ? 'Ativo' : 'Inativo' }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            @foreach ($quiz->questions as $index => $question)
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $index + 1 }}. {{ $question->question }}</p>
                    <div class="mt-3 space-y-2">
                        @foreach ($question->answers as $answer)
                            <div class="rounded-lg border px-3 py-2 text-sm {{ $answer->is_correct ? 'border-emerald-300 bg-emerald-50 text-emerald-800 dark:border-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-300' : 'border-gray-200 text-gray-700 dark:border-gray-700 dark:text-gray-300' }}">
                                {{ $answer->answer }} {!! $answer->is_correct ? '<strong>(correta)</strong>' : '' !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin>
