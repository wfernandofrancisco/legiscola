<x-layouts.responsible title="Quizzes das turmas" subtitle="Consulta e impressão dos quizzes apenas deste município/tenant — sem edição pela área gestor aqui (use admin se aplicável).">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <form method="GET" action="{{ route('responsible.quizzes.index') }}" class="mb-6 grid gap-3 sm:flex sm:flex-wrap sm:items-end">
            <div class="flex-1 min-w-[200px]">
                <label for="SearchQuiz" class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Buscar</label>
                <input id="SearchQuiz" name="search" type="text" value="{{ request('search') }}" placeholder="Título..."
                       class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
            </div>
            <div class="sm:w-40">
                <label for="StatusQuiz" class="block text-xs font-semibold uppercase text-slate-500 dark:text-slate-400">Status</label>
                <select id="StatusQuiz" name="status" class="mt-1 w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-950 dark:text-white">
                    <option value="">Todos</option>
                    <option value="1" @selected(request('status') === '1' || request('status') === 'true')>Ativo</option>
                    <option value="0" @selected(request('status') === '0' || request('status') === 'false')>Inativo</option>
                </select>
            </div>
            <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-cyan-600 dark:hover:bg-cyan-500">Filtrar</button>
        </form>

        @include('responsible.quizzes.includes._table', compact('quizzes'))
    </div>
</x-layouts.responsible>
