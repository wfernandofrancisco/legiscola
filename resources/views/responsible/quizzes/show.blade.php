<x-layouts.responsible title="{{ $quiz->title }}" subtitle="Visualização apenas leitura. Edição ficou na administração da escola, se você tiver acesso lá.">
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-10 sm:px-6 lg:px-8">
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('responsible.quizzes.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">← Lista de quizzes</a>
            <a href="{{ route('responsible.quizzes.print', $quiz) }}" target="_blank" rel="noopener" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-cyan-600 dark:hover:bg-cyan-500">Imprimir PDF</a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Nota mínima</p>
                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-white">{{ number_format((float) $quiz->min_score_to_pass, 2, ',', '.') }}%</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</p>
                    <div class="mt-1"><x-badge :color="$quiz->is_active ? 'green' : 'gray'" :text="$quiz->is_active ? 'Ativo' : 'Inativo'" /></div>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <h3 class="text-sm font-bold uppercase tracking-wide text-slate-500 dark:text-slate-400">Turmas vinculadas</h3>
            <div class="mt-4 flex flex-wrap gap-2">
                @foreach ($quiz->courseClasses as $courseClass)
                    <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $courseClass->pivot->is_active ? 'bg-emerald-100 text-emerald-900 dark:bg-emerald-900/35 dark:text-emerald-100' : 'bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200' }}">
                        {{ $courseClass->name }} · {{ $courseClass->pivot->is_active ? 'Ativo para alunos' : 'Inativo' }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="space-y-4">
            @foreach ($quiz->questions as $index => $question)
                <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
                    <p class="text-sm font-bold text-slate-900 dark:text-white">{{ $index + 1 }}. {{ $question->question }}</p>
                    <div class="mt-3 space-y-2">
                        @foreach ($question->answers as $answer)
                            <div class="rounded-lg border px-3 py-2 text-sm {{ $answer->is_correct ? 'border-emerald-300 bg-emerald-50 text-emerald-900 dark:border-emerald-700 dark:bg-emerald-900/25 dark:text-emerald-100' : 'border-slate-200 text-slate-700 dark:border-slate-600 dark:text-slate-300' }}">
                                {{ $answer->answer }} {!! $answer->is_correct ? '<strong>(correta)</strong>' : '' !!}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.responsible>
