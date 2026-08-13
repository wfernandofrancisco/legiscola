@php
    /** @var \App\Models\CourseClass $courseClass */
    $linked = $courseClass->linkedQuizzes ?? collect();
@endphp
<div class="w-full rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quizzes — janela de disponibilidade</h3>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        Os quizzes já são vinculados à turma na tela de edição do quiz. Aqui você define <strong>quando</strong> o aluno pode responder nesta turma.
        Deixe os dois campos vazios para ficar <strong>sempre disponível</strong> (desde que o quiz e o vínculo estejam ativos).
    </p>

    @if ($linked->isEmpty())
        <p class="mt-4 rounded-lg border border-dashed border-gray-300 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
            Nenhum quiz vinculado a esta turma. Edite um quiz em <a href="{{ route('admin.quizzes.index') }}" class="font-semibold text-indigo-600 underline hover:text-indigo-500 dark:text-indigo-400">Quizzes</a> e marque esta turma nas opções.
        </p>
    @else
        <form method="POST" action="{{ route('admin.turmas.quizzes-janelas.update', $courseClass) }}" class="mt-6 space-y-4">
            @csrf
            @method('PUT')
            @if ($errors->has('windows'))
                <p class="text-sm text-red-600 dark:text-red-400">{{ $errors->first('windows') }}</p>
            @endif
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Quiz</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Abre em (opcional)</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Encerra em (opcional)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($linked as $qz)
                            @php
                                $p = $qz->pivot;
                                $o = old('windows.'.$loop->index.'.opens_at', $p->opens_at ? \Illuminate\Support\Carbon::parse($p->opens_at)->format('Y-m-d\TH:i') : '');
                                $c = old('windows.'.$loop->index.'.closes_at', $p->closes_at ? \Illuminate\Support\Carbon::parse($p->closes_at)->format('Y-m-d\TH:i') : '');
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-gray-100">
                                    <input type="hidden" name="windows[{{ $loop->index }}][quiz_id]" value="{{ $qz->id }}" />
                                    <span class="font-medium">{{ $qz->title }}</span>
                                    <span class="mt-1 block text-xs text-gray-500 dark:text-gray-400">{{ $qz->is_active ? 'Quiz ativo' : 'Quiz inativo no cadastro' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <input type="datetime-local" name="windows[{{ $loop->index }}][opens_at]" value="{{ $o }}"
                                        class="w-full max-w-[11rem] rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                                </td>
                                <td class="px-4 py-3">
                                    <input type="datetime-local" name="windows[{{ $loop->index }}][closes_at]" value="{{ $c }}"
                                        class="w-full max-w-[11rem] rounded-lg border border-gray-300 bg-white px-2 py-1.5 text-gray-900 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="submit" class="inline-flex rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Salvar janelas dos quizzes
            </button>
        </form>
    @endif
</div>
