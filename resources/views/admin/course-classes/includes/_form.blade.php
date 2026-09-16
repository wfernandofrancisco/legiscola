<form method="POST" action="{{ $action === 'edit' ? route('admin.turmas.update', $courseClass) : route('admin.turmas.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif
    @php
        $selectedCourseId = (int) old('course_id', $courseClass?->course_id);
        $selectedCourseName = old('course_search', $courseClass?->course?->name ?? '');
        $initialSchedules = old('schedules', $courseClass?->schedules?->map(fn($s) => [
            'weekday' => $s->weekday,
            'start_time' => substr((string) $s->start_time, 0, 5),
            'end_time' => substr((string) $s->end_time, 0, 5),
        ])->values()->toArray() ?? []);
        if (empty($initialSchedules)) {
            $initialSchedules = [['weekday' => '', 'start_time' => '', 'end_time' => '']];
        }
        $teachers = $teachers ?? collect();
        $selectedTeacherIds = old('teacher_ids', $courseClass?->teachers?->pluck('id')->all() ?? []);
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <input type="hidden" id="course_id" name="course_id" value="{{ $selectedCourseId ?: '' }}" />
        <div class="relative md:col-span-1">
            <x-form.input id="course_search" name="course_search" label="Curso" :required="true" :value="$selectedCourseName"
                autocomplete="off" hint="Digite para buscar cursos." />
            <div id="course-search-results"
                class="hidden absolute z-30 mt-1 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-56 overflow-y-auto"></div>
        </div>
        <x-form.input name="name" label="Nome" :value="$courseClass?->name ?? old('name')" />
        <x-form.select name="tipo_turma" label="Tipo da turma" :options="['presencial' => 'Presencial', 'online' => 'Online']"
            :selected="$courseClass?->tipo_turma ?? old('tipo_turma', 'presencial')" />
        <x-form.input name="max_seats" label="Vagas" type="number" :value="$courseClass?->max_seats ?? old('max_seats')" />
        <x-form.input name="enrollment_start" label="Início inscrição" type="datetime-local" :value="old('enrollment_start', optional($courseClass?->enrollment_start)->format('Y-m-d\TH:i'))" />
        <x-form.input name="enrollment_end" label="Fim inscrição" type="datetime-local" :value="old('enrollment_end', optional($courseClass?->enrollment_end)->format('Y-m-d\TH:i'))" />
        <x-form.input name="certificado_disponivel_ate" label="Data limite para emissão do certificado" type="datetime-local"
            :value="old('certificado_disponivel_ate', optional($courseClass?->certificado_disponivel_ate)->format('Y-m-d\TH:i'))"
            hint="Até esta data o aluno pode acessar e baixar o certificado desta turma. Deixe em branco para manter disponível sem prazo." />
        @php
            $satisfactionSurveys = $satisfactionSurveys ?? collect();
            $surveyOptions = ['' => 'Nenhuma'] + $satisfactionSurveys->mapWithKeys(
                fn ($s) => [(string) $s->id => $s->title]
            )->all();
        @endphp
        <x-form.select name="satisfaction_survey_id" label="Pesquisa de satisfação"
            :options="$surveyOptions"
            :selected="old('satisfaction_survey_id', $courseClass?->satisfaction_survey_id)"
            hint="Selecione uma pesquisa ativa para vincular a esta turma." />
        <div class="md:col-span-3">
            <x-form.checkbox name="satisfaction_survey_required" label="Obrigar o aluno a responder a pesquisa antes de emitir o certificado"
                :checked="(bool) ($courseClass?->satisfaction_survey_required ?? false)"
                hint="Só vale se houver uma pesquisa selecionada acima." />
        </div>
        <x-form.select name="status" label="Status" :options="[
            'cadastrado' => 'Cadastrado',
            'inscricao' => 'Inscrição',
            'em_andamento' => 'Em andamento',
            'concluido' => 'Concluído',
            'cancelado' => 'Cancelado',
        ]" :selected="$courseClass?->status ?? old('status', 'cadastrado')" />
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-1">Professores da turma</label>
            <select name="teacher_ids[]" multiple size="6"
                class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2 text-sm text-gray-900 dark:text-gray-100">
                @foreach($teachers as $t)
                    <option value="{{ $t->id }}" @selected(in_array((int) $t->id, array_map('intval', (array) $selectedTeacherIds), true))>
                        {{ $t->full_name ?: $t->email ?: 'Professor #'.$t->id }}
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Segure Ctrl (Windows) ou Cmd (Mac) para selecionar vários. Aparecem no portal e na área do aluno.</p>
            @error('teacher_ids')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
            @error('teacher_ids.*')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        @if ($action !== 'edit')
        <div class="md:col-span-3" id="grade-wrapper">
            <div class="rounded-xl border border-indigo-200 bg-indigo-50/40 p-4 dark:border-indigo-800 dark:bg-indigo-950/20">
                <div class="mb-3">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">Grade desta turma</p>
                    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                        Lista as aulas do curso. Aqui você define dia, horário e se é presencial ou online.
                        O conteúdo (vídeo/material) continua no curso.
                    </p>
                </div>
                <div class="mb-4 grid grid-cols-1 gap-3 sm:grid-cols-4">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">1ª aula</label>
                        <input type="date" id="grade-fill-date" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Intervalo (dias)</label>
                        <input type="number" id="grade-fill-interval" value="7" min="1" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Início</label>
                        <input type="time" id="grade-fill-start" value="19:00" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-300">Término</label>
                        <input type="time" id="grade-fill-end" value="21:00" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100">
                    </div>
                </div>
                <button type="button" id="grade-fill-btn"
                    class="mb-4 rounded-lg border border-indigo-300 bg-white px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-50 dark:border-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200">
                    Preencher datas em sequência
                </button>
                @error('grade')
                    <p class="mb-3 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <div id="grade-empty" class="rounded-lg border border-dashed border-gray-300 bg-white px-4 py-6 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-400">
                    Selecione um curso com aulas cadastradas para montar a grade.
                </div>
                <div id="grade-rows" class="hidden space-y-3"></div>
            </div>
        </div>
        @endif
        <div class="md:col-span-3" id="schedule-wrapper">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                <div class="flex items-center justify-between mb-3">
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Horários da turma (presencial)</h4>
                    <button type="button" id="add-schedule-row"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-indigo-200 dark:border-indigo-800 bg-indigo-50 dark:bg-indigo-950/30 px-3 py-2 text-sm font-medium text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar horário
                    </button>
                </div>
                <div id="schedule-rows" class="space-y-2">
                    @foreach($initialSchedules as $i => $schedule)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-2 schedule-row">
                            <x-form.select name="schedules[{{ $i }}][weekday]" label="Dia da semana" :options="[
                                '1' => 'Segunda',
                                '2' => 'Terça',
                                '3' => 'Quarta',
                                '4' => 'Quinta',
                                '5' => 'Sexta',
                                '6' => 'Sábado',
                                '0' => 'Domingo',
                            ]" :selected="$schedule['weekday']" />
                            <x-form.input name="schedules[{{ $i }}][start_time]" label="Início" type="time" :value="$schedule['start_time']" />
                            <x-form.input name="schedules[{{ $i }}][end_time]" label="Fim" type="time" :value="$schedule['end_time']" />
                            <div class="flex items-end">
                                <button type="button"
                                    class="remove-schedule-row inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/30 px-3 py-2 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-red-500/30 transition">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                    Remover
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
        <button type="submit" class="inline-flex rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Turma' }}
        </button>
    </div>
</form>
@once
    @push('scripts')
        <script>
            (function () {
                var courseSearch = document.getElementById('course_search');
                var courseId = document.getElementById('course_id');
                var resultsBox = document.getElementById('course-search-results');
                if (!courseSearch || !courseId || !resultsBox) return;

                var timer;
                var endpoint = @json(route('admin.cursos.search'));
                var gradeUrlTpl = @json(url('/admin/escola/cursos/__ID__/aulas-grade'));
                var tipoTurma = document.querySelector('[name="tipo_turma"]');
                var scheduleWrapper = document.getElementById('schedule-wrapper');
                var scheduleRows = document.getElementById('schedule-rows');
                var addScheduleRow = document.getElementById('add-schedule-row');
                var gradeRows = document.getElementById('grade-rows');
                var gradeEmpty = document.getElementById('grade-empty');
                var gradeFillBtn = document.getElementById('grade-fill-btn');
                var oldGrade = @json(old('grade', []));

                function hideResults() {
                    resultsBox.classList.add('hidden');
                    resultsBox.innerHTML = '';
                }

                function selectCourse(course) {
                    courseId.value = course.id;
                    courseSearch.value = course.name;
                    hideResults();
                    loadGrade(course.id);
                }

                function renderResults(courses) {
                    if (!courses.length) {
                        resultsBox.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Nenhum curso encontrado.</div>';
                    } else {
                        resultsBox.innerHTML = courses.map(function(course) {
                            return '<button type="button" data-id="' + course.id + '" data-name="' + course.name.replace(/"/g, '&quot;') + '" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200">' + course.name + '</button>';
                        }).join('');
                    }
                    resultsBox.classList.remove('hidden');
                }

                courseSearch.addEventListener('input', function () {
                    courseId.value = '';
                    clearTimeout(timer);
                    var q = courseSearch.value.trim();
                    if (q.length < 2) {
                        hideResults();
                        return;
                    }

                    timer = setTimeout(function () {
                        fetch(endpoint + '?q=' + encodeURIComponent(q), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function (res) { return res.json(); })
                        .then(renderResults)
                        .catch(hideResults);
                    }, 250);
                });

                resultsBox.addEventListener('click', function (e) {
                    var btn = e.target.closest('button[data-id]');
                    if (!btn) return;
                    selectCourse({
                        id: btn.getAttribute('data-id'),
                        name: btn.getAttribute('data-name')
                    });
                });

                document.addEventListener('click', function (e) {
                    if (!resultsBox.contains(e.target) && e.target !== courseSearch) {
                        hideResults();
                    }
                });

                function isOnlineDefault() {
                    return tipoTurma && tipoTurma.value === 'online';
                }

                function addDays(iso, days) {
                    var d = new Date(iso + 'T00:00:00');
                    d.setDate(d.getDate() + days);
                    var m = String(d.getMonth() + 1).padStart(2, '0');
                    var day = String(d.getDate()).padStart(2, '0');
                    return d.getFullYear() + '-' + m + '-' + day;
                }

                function renderGrade(lessons) {
                    if (!gradeRows || !gradeEmpty) return;
                    if (!lessons.length) {
                        gradeRows.innerHTML = '';
                        gradeRows.classList.add('hidden');
                        gradeEmpty.classList.remove('hidden');
                        gradeEmpty.textContent = 'Este curso ainda não tem aulas de conteúdo. Cadastre as aulas no curso e volte aqui.';
                        return;
                    }
                    gradeEmpty.classList.add('hidden');
                    gradeRows.classList.remove('hidden');
                    gradeRows.innerHTML = lessons.map(function (lesson, index) {
                        var old = oldGrade[index] || {};
                        var courseLessonId = lesson.course_lesson_id || '';
                        var catalogLessonId = lesson.catalog_lesson_id || '';
                        var onlineChecked = old.is_online !== undefined ? !!Number(old.is_online) : isOnlineDefault();
                        return '<div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">'
                            + '<p class="mb-2 text-sm font-semibold text-gray-900 dark:text-white">' + (index + 1) + '. ' + lesson.title + '</p>'
                            + (courseLessonId ? '<input type="hidden" name="grade[' + index + '][course_lesson_id]" value="' + courseLessonId + '">' : '')
                            + (catalogLessonId ? '<input type="hidden" name="grade[' + index + '][catalog_lesson_id]" value="' + catalogLessonId + '">' : '')
                            + '<div class="grid grid-cols-1 gap-2 sm:grid-cols-4">'
                            + '<input type="date" name="grade[' + index + '][date]" value="' + (old.date || '') + '" class="js-grade-date rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required>'
                            + '<input type="time" name="grade[' + index + '][start_time]" value="' + (old.start_time || '19:00') + '" class="js-grade-start rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required>'
                            + '<input type="time" name="grade[' + index + '][end_time]" value="' + (old.end_time || '21:00') + '" class="js-grade-end rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" required>'
                            + '<label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300"><input type="hidden" name="grade[' + index + '][is_online]" value="0"><input type="checkbox" name="grade[' + index + '][is_online]" value="1"' + (onlineChecked ? ' checked' : '') + '> Online</label>'
                            + '</div></div>';
                    }).join('');
                }

                function loadGrade(id) {
                    if (!id || !gradeRows) return;
                    fetch(gradeUrlTpl.replace('__ID__', id), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(function (res) { return res.json(); })
                    .then(function (data) { renderGrade(data.lessons || []); })
                    .catch(function () { renderGrade([]); });
                }

                if (gradeFillBtn) {
                    gradeFillBtn.addEventListener('click', function () {
                        var start = document.getElementById('grade-fill-date').value;
                        var interval = parseInt(document.getElementById('grade-fill-interval').value, 10) || 7;
                        var tStart = document.getElementById('grade-fill-start').value;
                        var tEnd = document.getElementById('grade-fill-end').value;
                        if (!start) return;
                        var dates = gradeRows.querySelectorAll('.js-grade-date');
                        dates.forEach(function (input, i) {
                            input.value = addDays(start, i * interval);
                        });
                        gradeRows.querySelectorAll('.js-grade-start').forEach(function (input) { input.value = tStart; });
                        gradeRows.querySelectorAll('.js-grade-end').forEach(function (input) { input.value = tEnd; });
                    });
                }

                if (courseId && courseId.value) {
                    loadGrade(courseId.value);
                }

                function toggleScheduleVisibility() {
                    if (!tipoTurma || !scheduleWrapper) return;
                    scheduleWrapper.style.display = tipoTurma.value === 'presencial' ? 'block' : 'none';
                }

                function rowTemplate(index) {
                    return `
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-2 schedule-row">
                        <div class="relative">
                            <label class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium text-gray-800 dark:text-white">Dia da semana</label>
                            <select name="schedules[${index}][weekday]" class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100">
                                <option value="">Selecione</option><option value="1">Segunda</option><option value="2">Terça</option><option value="3">Quarta</option><option value="4">Quinta</option><option value="5">Sexta</option><option value="6">Sábado</option><option value="0">Domingo</option>
                            </select>
                        </div>
                        <div class="relative"><label class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium text-gray-800 dark:text-white">Início</label><input type="time" name="schedules[${index}][start_time]" class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100"></div>
                        <div class="relative"><label class="absolute left-3 -top-2 bg-white dark:bg-gray-800 px-1 text-[11px] font-medium text-gray-800 dark:text-white">Fim</label><input type="time" name="schedules[${index}][end_time]" class="block w-full rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 px-3 py-2.5 text-sm text-gray-900 dark:text-gray-100"></div>
                        <div class="flex items-end">
                            <button type="button" class="remove-schedule-row inline-flex w-full items-center justify-center gap-1.5 rounded-lg border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950/30 px-3 py-2 text-sm font-medium text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/40 focus:outline-none focus:ring-2 focus:ring-red-500/30 transition">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Remover
                            </button>
                        </div>
                    </div>`;
                }

                if (tipoTurma) {
                    tipoTurma.addEventListener('change', toggleScheduleVisibility);
                    toggleScheduleVisibility();
                }

                if (addScheduleRow && scheduleRows) {
                    addScheduleRow.addEventListener('click', function () {
                        var idx = scheduleRows.querySelectorAll('.schedule-row').length;
                        scheduleRows.insertAdjacentHTML('beforeend', rowTemplate(idx));
                    });
                    scheduleRows.addEventListener('click', function (e) {
                        var btn = e.target.closest('.remove-schedule-row');
                        if (!btn) return;
                        var row = btn.closest('.schedule-row');
                        if (!row) return;
                        if (scheduleRows.querySelectorAll('.schedule-row').length === 1) return;
                        row.remove();
                    });
                }
            })();
        </script>
    @endpush
@endonce
