{{-- Cadastro rápido de ClassLesson nesta turma — necessário para a chamada por aula aparecer aqui --}}
<div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50/70 p-4 dark:border-emerald-900 dark:bg-emerald-950/25">
    <h3 class="text-sm font-bold text-emerald-950 dark:text-emerald-100">Cadastrar aula rápido</h3>
    <p class="mt-1 text-xs text-emerald-900/90 dark:text-emerald-200/90">
        A chamada desta página usa <strong>aulas com data</strong> (não apenas o cronograma semanal da turma no admin).
    </p>
    <form method="POST" action="{{ route('professor.turmas.ficha-presenca.aula-rapida.store', $turma) }}"
        class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-6 lg:items-end">
        @csrf
        <div class="sm:col-span-2 lg:col-span-2">
            <label for="quick-lesson-title" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Título</label>
            <input id="quick-lesson-title" name="title" type="text" required value="{{ old('title', 'Aula') }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
        </div>
        <div>
            <label for="quick-lesson-date" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Data</label>
            <input id="quick-lesson-date" name="date" type="date" required value="{{ old('date', $date) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
        </div>
        <div>
            <label for="quick-lesson-start" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Início</label>
            <input id="quick-lesson-start" name="start_time" type="time" required
                value="{{ old('start_time', $defaultScheduleStart ?? '19:00') }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
        </div>
        <div>
            <label for="quick-lesson-end" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Fim</label>
            <input id="quick-lesson-end" name="end_time" type="time" required value="{{ old('end_time', $defaultScheduleEnd ?? '22:00') }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
        </div>
        <div class="flex items-center gap-2 pb-2 lg:flex-col lg:items-start lg:pb-0">
            <input type="checkbox" name="is_online" id="quick-lesson-online" value="1" class="rounded border-gray-400" @checked(old('is_online'))>
            <label for="quick-lesson-online" class="text-xs text-gray-700 dark:text-gray-300">Aula online</label>
        </div>
        <div class="sm:col-span-2 lg:col-span-6 flex flex-wrap items-center gap-3">
            <button type="submit"
                class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                Salvar aula
            </button>
            <a href="{{ route('professor.aulas.create', ['course_class_id' => $turma->id]) }}"
                class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">
                Abrir formulário completo →
            </a>
        </div>
    </form>
</div>
