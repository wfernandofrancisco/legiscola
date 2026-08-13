<x-layouts.aluno :title="$quiz->title">
    <div class="mb-6">
        <a href="{{ route('app.quizzes.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">← Voltar aos quizzes</a>
    </div>

    <header class="mb-8 rounded-2xl border border-slate-800 bg-slate-900/50 p-6">
        <h1 class="text-xl font-bold text-white sm:text-2xl">{{ $quiz->title }}</h1>
        <p class="mt-2 text-sm text-slate-400">Turma <span class="text-slate-200">{{ $courseClass->name }}</span> ({{ $courseClass->tipo_turma }}). Responda todas as questões e envie ao finalizar.</p>
    </header>

    <form method="POST" action="{{ route('app.quizzes.submit', $quiz) }}" class="space-y-4">
        @csrf

        @foreach ($quiz->questions as $questionIndex => $question)
            <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-5">
                <p class="text-sm font-semibold text-white">{{ $questionIndex + 1 }}. {{ $question->question }}</p>
                <div class="mt-3 space-y-2">
                    @foreach ($question->answers as $answer)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-700/80 bg-slate-950/40 px-3 py-2.5 text-sm text-slate-200 transition hover:border-cyan-500/40 hover:bg-slate-900">
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $answer->id }}" required class="border-slate-600 text-cyan-500 focus:ring-cyan-500/40">
                            <span>{{ $answer->answer }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach

        <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/20 hover:brightness-110">
            Enviar respostas
        </button>
    </form>
</x-layouts.aluno>
