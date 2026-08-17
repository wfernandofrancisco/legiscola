@php
    $survey = $survey ?? null;
    $action = $action ?? 'create';

    $oldQuestions = old('questions');
    if ($oldQuestions) {
        $initialQuestions = $oldQuestions;
    } elseif ($survey) {
        $initialQuestions = $survey->questions
            ->map(fn ($question) => [
                'question' => $question->question,
                'tipo' => $question->tipo,
                'options' => $question->options->map(fn ($opt) => ['label' => $opt->label])->values()->all(),
            ])
            ->values()
            ->all();
    } else {
        $initialQuestions = [
            [
                'question' => '',
                'tipo' => 'choices',
                'options' => [['label' => ''], ['label' => '']],
            ],
        ];
    }
@endphp

<form method="POST"
    action="{{ $action === 'edit' ? route('admin.pesquisas-satisfacao.update', $survey) : route('admin.pesquisas-satisfacao.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados da pesquisa</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <x-form.input name="title" label="Título" :required="true" :value="$survey?->title ?? old('title')" />
            </div>
            <div class="md:col-span-2">
                <x-form.textarea name="description" label="Descrição" rows="3"
                    :value="$survey?->description ?? old('description')"
                    hint="Texto explicativo exibido para o aluno antes de responder." />
            </div>
            <div class="md:col-span-2">
                <x-form.checkbox name="is_active" label="Pesquisa ativa" :checked="$survey?->is_active ?? old('is_active', true)" />
            </div>
        </div>
    </fieldset>

    <fieldset class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Perguntas</legend>
        <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
            Cada pergunta pode ser <strong>campo livre</strong> (texto) ou <strong>opções</strong> (o aluno só seleciona).
        </p>
        <div id="survey-questions" class="space-y-4"></div>
        <button type="button" id="add-survey-question"
            class="mt-3 rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
            + Adicionar pergunta
        </button>
        @error('questions')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar alterações' : 'Criar pesquisa' }}
        </button>
        <a href="{{ route('admin.pesquisas-satisfacao.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>

@push('scripts')
    <script>
        (function () {
            var container = document.getElementById('survey-questions');
            var addBtn = document.getElementById('add-survey-question');
            var initial = @json($initialQuestions);
            var qIndex = 0;

            function escapeHtml(value) {
                return String(value || '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');
            }

            function optionRow(qIdx, oIdx, label) {
                return '<div class="flex gap-2 option-row">' +
                    '<input type="text" name="questions[' + qIdx + '][options][' + oIdx + '][label]" value="' + escapeHtml(label) + '" placeholder="Opção ' + (oIdx + 1) + '" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">' +
                    '<button type="button" class="remove-option rounded-lg border border-red-200 px-2 text-xs text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-300">Remover</button>' +
                    '</div>';
            }

            function questionCard(data) {
                var idx = qIndex++;
                var tipo = data.tipo || 'choices';
                var options = (data.options && data.options.length) ? data.options : [{ label: '' }, { label: '' }];
                var optionsHtml = options.map(function (opt, i) {
                    return optionRow(idx, i, opt.label || opt || '');
                }).join('');

                var card = document.createElement('div');
                card.className = 'rounded-xl border border-gray-200 p-4 dark:border-gray-700 survey-question';
                card.dataset.index = String(idx);
                card.innerHTML =
                    '<div class="mb-3 flex items-start justify-between gap-3">' +
                        '<p class="text-sm font-semibold text-gray-800 dark:text-gray-100">Pergunta <span class="q-num"></span></p>' +
                        '<button type="button" class="remove-question text-xs font-semibold text-red-600 hover:underline">Remover</button>' +
                    '</div>' +
                    '<div class="grid gap-3 md:grid-cols-3">' +
                        '<div class="md:col-span-2">' +
                            '<label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Nome da pergunta</label>' +
                            '<input type="text" name="questions[' + idx + '][question]" value="' + escapeHtml(data.question || '') + '" required class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">' +
                        '</div>' +
                        '<div>' +
                            '<label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Tipo de resposta</label>' +
                            '<select name="questions[' + idx + '][tipo]" class="question-tipo w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100">' +
                                '<option value="choices"' + (tipo === 'choices' ? ' selected' : '') + '>Opções (selecionar)</option>' +
                                '<option value="free_text"' + (tipo === 'free_text' ? ' selected' : '') + '>Campo livre (texto)</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    '<div class="options-wrap mt-3 space-y-2' + (tipo === 'free_text' ? ' hidden' : '') + '">' +
                        '<p class="text-xs font-medium text-gray-600 dark:text-gray-300">Opções</p>' +
                        '<div class="options-list space-y-2">' + optionsHtml + '</div>' +
                        '<button type="button" class="add-option mt-1 text-xs font-semibold text-indigo-600 hover:underline">+ Opção</button>' +
                    '</div>';

                return card;
            }

            function renumber() {
                container.querySelectorAll('.survey-question').forEach(function (card, i) {
                    var num = card.querySelector('.q-num');
                    if (num) num.textContent = String(i + 1);
                });
            }

            function syncTipo(card) {
                var select = card.querySelector('.question-tipo');
                var wrap = card.querySelector('.options-wrap');
                if (!select || !wrap) return;
                wrap.classList.toggle('hidden', select.value === 'free_text');
            }

            addBtn.addEventListener('click', function () {
                container.appendChild(questionCard({
                    question: '',
                    tipo: 'choices',
                    options: [{ label: '' }, { label: '' }]
                }));
                renumber();
            });

            container.addEventListener('click', function (e) {
                var removeQ = e.target.closest('.remove-question');
                if (removeQ) {
                    var card = removeQ.closest('.survey-question');
                    if (container.querySelectorAll('.survey-question').length <= 1) return;
                    card.remove();
                    renumber();
                    return;
                }

                var addOpt = e.target.closest('.add-option');
                if (addOpt) {
                    var cardAdd = addOpt.closest('.survey-question');
                    var list = cardAdd.querySelector('.options-list');
                    var idx = cardAdd.dataset.index;
                    var oIdx = list.querySelectorAll('.option-row').length;
                    list.insertAdjacentHTML('beforeend', optionRow(idx, oIdx, ''));
                    return;
                }

                var removeOpt = e.target.closest('.remove-option');
                if (removeOpt) {
                    var listRem = removeOpt.closest('.options-list');
                    if (listRem.querySelectorAll('.option-row').length <= 2) return;
                    removeOpt.closest('.option-row').remove();
                }
            });

            container.addEventListener('change', function (e) {
                if (!e.target.classList.contains('question-tipo')) return;
                syncTipo(e.target.closest('.survey-question'));
            });

            initial.forEach(function (item) {
                container.appendChild(questionCard(item));
            });
            renumber();
            container.querySelectorAll('.survey-question').forEach(syncTipo);
        })();
    </script>
@endpush
