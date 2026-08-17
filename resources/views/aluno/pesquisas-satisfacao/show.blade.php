<x-layouts.aluno :title="$survey->title">
    <div class="mb-6">
        <a href="{{ route('app.pesquisas-satisfacao.index') }}" class="text-sm font-semibold text-cyan-400 hover:text-cyan-300">← Pesquisas</a>
    </div>

    <div class="mb-8 rounded-3xl border border-slate-800 bg-gradient-to-br from-slate-900 via-slate-900/90 to-cyan-950/30 p-8 shadow-xl shadow-black/20">
        <p class="text-xs font-semibold uppercase tracking-wider text-cyan-400/90">{{ $turma->course?->name }} · {{ $turma->name }}</p>
        <h1 class="mt-2 text-2xl font-bold text-white sm:text-3xl">{{ $survey->title }}</h1>
        @if ($survey->description)
            <p class="mt-3 max-w-2xl text-sm leading-relaxed text-slate-400">{{ $survey->description }}</p>
        @endif
        @if ($turma->satisfaction_survey_required)
            <p class="mt-4 text-xs font-medium text-amber-300/90">Esta pesquisa é obrigatória para emissão do certificado.</p>
        @endif
    </div>

    @if ($alreadyAnswered)
        <div class="rounded-2xl border border-emerald-500/30 bg-emerald-500/10 px-5 py-4 text-sm text-emerald-200">
            Você já enviou as respostas desta pesquisa. Obrigado!
        </div>
    @else
        <form method="POST" action="{{ route('app.pesquisas-satisfacao.store', $turma) }}" class="space-y-6">
            @csrf
            @foreach ($survey->questions as $question)
                <div class="rounded-2xl border border-slate-800 bg-slate-900/50 p-6">
                    <p class="text-sm font-bold text-white">{{ $loop->iteration }}. {{ $question->question }}</p>

                    @if ($question->isFreeText())
                        <textarea name="answers[{{ $question->id }}][free_text]" rows="3" required
                                  class="mt-4 w-full rounded-xl border border-slate-700 bg-slate-950 px-4 py-3 text-sm text-white outline-none focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">{{ old('answers.'.$question->id.'.free_text') }}</textarea>
                    @else
                        <div class="mt-4 space-y-2">
                            @foreach ($question->options as $option)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-800 bg-slate-950/60 px-4 py-3 text-sm text-slate-200 hover:border-cyan-500/40">
                                    <input type="radio" name="answers[{{ $question->id }}][option_id]" value="{{ $option->id }}" required
                                           @checked((string) old('answers.'.$question->id.'.option_id') === (string) $option->id)
                                           class="border-slate-600 text-cyan-500 focus:ring-cyan-500">
                                    <span>{{ $option->label }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif

                    @error('answers.'.$question->id)
                        <p class="mt-2 text-xs text-rose-400">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach

            <button type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-teal-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-cyan-500/20 transition hover:brightness-110 sm:w-auto">
                Enviar respostas
            </button>
        </form>
    @endif
</x-layouts.aluno>
