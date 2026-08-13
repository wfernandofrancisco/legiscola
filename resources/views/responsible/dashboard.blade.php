<x-layouts.responsible title="Painel" subtitle="Olá, {{ $user->name }} — acesse os módulos pelo menu acima.">
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <a href="{{ route('responsible.quizzes.index') }}"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400/50 hover:shadow-md">
                <h2 class="text-lg font-bold text-slate-900">Quiz turmas</h2>
                <p class="mt-2 text-sm text-slate-600">Listar e imprimir quizzes das turmas.</p>
            </a>
            <a href="{{ route('professor.turmas.index') }}"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400/50 hover:shadow-md">
                <h2 class="text-lg font-bold text-slate-900">Turmas e presença</h2>
                <p class="mt-2 text-sm text-slate-600">Chamadas e fichas nas turmas (/docente).</p>
            </a>
            <a href="{{ route('responsible.perfil.edit') }}"
                class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-cyan-400/50 hover:shadow-md">
                <h2 class="text-lg font-bold text-slate-900">Meu perfil</h2>
                <p class="mt-2 text-sm text-slate-600">Dados e senha (sem entrar na administração da escola).</p>
            </a>
        </div>

        <p class="mt-10 text-center text-sm text-slate-500">
            Docentes da escola legislativa usam o painel em
            <a href="{{ route('professor.dashboard') }}" class="font-semibold text-cyan-700 hover:underline">/docente</a>.
        </p>
    </div>
</x-layouts.responsible>
