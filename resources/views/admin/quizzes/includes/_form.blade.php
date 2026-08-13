@php
    $quiz = $quiz ?? null;
    $action = $action ?? 'create';
    $classes = $classes ?? collect();

    $oldQuestions = old('questions');
    if ($oldQuestions) {
        $initialQuestions = $oldQuestions;
    } elseif ($quiz) {
        $initialQuestions = $quiz->questions
            ->map(function ($question) {
                $correctIndex = $question->answers->search(fn($answer) => (bool) $answer->is_correct);
                return [
                    'text' => $question->question,
                    'correct_answer' => $correctIndex === false ? 0 : $correctIndex,
                    'answers' => $question->answers->map(fn($answer) => ['text' => $answer->answer])->values()->all(),
                ];
            })
            ->values()
            ->all();
    } else {
        $initialQuestions = [
            [
                'text' => '',
                'correct_answer' => 0,
                'answers' => [['text' => ''], ['text' => '']],
            ],
        ];
    }
@endphp

<form method="POST"
    action="{{ $action === 'edit' ? route('admin.quizzes.update', $quiz) : route('admin.quizzes.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados do Quiz</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="title" label="Titulo" :required="true" :value="$quiz?->title ?? old('title')" />
            <x-form.input name="min_score_to_pass" label="Nota minima (%)" type="number" step="0.01" min="0"
                max="100" :required="true" :value="$quiz?->min_score_to_pass ?? old('min_score_to_pass', 70)" />

            <div class="md:col-span-2">
                <x-form.select name="course_class_ids[]" label="Turmas" :selected="old(
                    'course_class_ids',
                    $quiz?->courseClasses->pluck('id')->map(fn($id) => (string) $id)->all() ?? [],
                )" :multiple="true" :required="true">
                    @foreach ($classes as $class)
                        <option value="{{ $class->id }}" @selected(collect(old('course_class_ids', $quiz?->courseClasses->pluck('id')->all() ?? []))->contains((string) $class->id) ||
                                collect(old('course_class_ids', $quiz?->courseClasses->pluck('id')->all() ?? []))->contains($class->id))>
                            {{ $class->name }} ({{ $class->tipo_turma }})
                        </option>
                    @endforeach
                </x-form.select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Use CTRL para selecionar mais de uma turma.</p>
            </div>

            <div class="md:col-span-2">
                <x-form.checkbox name="is_active" label="Ativar quiz" :checked="$quiz?->is_active ?? old('is_active', true)" />
            </div>
        </div>
    </fieldset>

    <fieldset class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Perguntas e Alternativas</legend>
        <div id="questions-container" class="space-y-4"></div>
        <button type="button" id="add-question-btn"
            class="mt-3 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
            + Adicionar pergunta
        </button>
    </fieldset>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Quiz' }}
        </button>
        <a href="{{ route('admin.quizzes.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>

@push('scripts')
    <script>
        (function() {
            const questionsContainer = document.getElementById('questions-container');
            const addQuestionBtn = document.getElementById('add-question-btn');
            const initialQuestions = @json($initialQuestions);
            let questionIndex = 0;

            function answerTemplate(qIndex, aIndex, value = '', checked = false) {
                return `
                    <div class="grid gap-2 md:grid-cols-[1fr_auto]">
                        <input type="text" name="questions[${qIndex}][answers][${aIndex}][text]" value="${value}" required placeholder="Alternativa ${aIndex + 1}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">
                        <label class="inline-flex items-center gap-2 text-xs font-medium text-gray-700 dark:text-gray-300">
                            <input type="radio" name="questions[${qIndex}][correct_answer]" value="${aIndex}" ${checked ? 'checked' : ''} class="border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-700">
                            Correta
                        </label>
                    </div>
                `;
            }

            function addQuestion(prefill = null) {
                const qIndex = questionIndex++;
                const wrapper = document.createElement('div');
                wrapper.className = 'rounded-lg border border-gray-200 p-4 dark:border-gray-700';
                wrapper.innerHTML = `
                    <div class="mb-3 flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pergunta ${qIndex + 1}</p>
                        <button type="button" class="remove-question rounded border border-rose-200 px-2 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-300 dark:hover:bg-rose-900/30">Remover</button>
                    </div>
                    <textarea name="questions[${qIndex}][text]" required rows="2" placeholder="Digite a pergunta..." class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm text-gray-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">${prefill?.text ?? ''}</textarea>
                    <div class="mt-3 space-y-2" data-answers></div>
                    <button type="button" class="add-answer mt-3 rounded border border-gray-300 px-2 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">+ Alternativa</button>
                `;

                const answers = wrapper.querySelector('[data-answers]');
                const prefilledAnswers = prefill?.answers?.length ? prefill.answers : [{
                    text: ''
                }, {
                    text: ''
                }];
                const correctAnswer = Number(prefill?.correct_answer ?? 0);
                prefilledAnswers.forEach((answer, index) => {
                    answers.insertAdjacentHTML('beforeend', answerTemplate(qIndex, index, answer.text ?? '', index === correctAnswer));
                });

                wrapper.querySelector('.remove-question').addEventListener('click', function() {
                    wrapper.remove();
                });

                wrapper.querySelector('.add-answer').addEventListener('click', function() {
                    const nextIndex = answers.children.length;
                    answers.insertAdjacentHTML('beforeend', answerTemplate(qIndex, nextIndex));
                });

                questionsContainer.appendChild(wrapper);
            }

            addQuestionBtn.addEventListener('click', function() {
                addQuestion();
            });

            if (initialQuestions.length > 0) {
                initialQuestions.forEach((question) => addQuestion(question));
            } else {
                addQuestion();
            }
        })();
    </script>
@endpush
