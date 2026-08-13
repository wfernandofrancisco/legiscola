<form method="POST" enctype="multipart/form-data"
    action="{{ $action === 'edit' ? route('admin.aulas.update', $classLesson) : route('admin.aulas.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    @php
        $prefill = isset($prefillCourseClass) ? $prefillCourseClass : null;
        $selectedCourseClassId = old('course_class_id', $classLesson?->course_class_id ?? $prefill?->id);
        $selectedCourseClassName = old('course_class_search', $classLesson?->courseClass?->name ?? $prefill?->name ?? '');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="relative md:col-span-1">
            <input type="hidden" id="course_class_id" name="course_class_id" value="{{ $selectedCourseClassId }}" />
            <x-form.input id="course_class_search" name="course_class_search" label="Turma" :required="true"
                :value="$selectedCourseClassName" autocomplete="off" hint="Digite para buscar a turma." />
            <div id="course-class-search-results"
                class="hidden absolute z-30 mt-1 w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-lg max-h-56 overflow-y-auto">
            </div>
        </div>
        <x-form.input name="title" label="Título" :value="$classLesson?->title ?? old('title')" />
        <x-form.input name="date" label="Data" type="date" :value="$classLesson?->date?->format('Y-m-d') ?? old('date')" />
        <x-form.input name="start_time" label="Início" type="time" :value="$classLesson?->start_time ?? old('start_time')" />
        <x-form.input name="end_time" label="Fim" type="time" :value="$classLesson?->end_time ?? old('end_time')" />
        <div class="flex items-center gap-2 mt-6">
            <input type="checkbox" name="is_online" value="1" @checked(old('is_online', $classLesson?->is_online))>
            <label class="text-sm text-gray-700 dark:text-gray-300">Aula online</label>
        </div>
        <x-form.input name="video_url" label="URL do vídeo" :value="$classLesson?->video_url ?? old('video_url')" />
        <x-form.input name="material_url" label="URL do material (link externo)" :value="$classLesson?->material_url ?? old('material_url')"
            hint="Opcional. Use para link externo; para arquivo no servidor use o campo abaixo." />
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo da aula (material no storage)</label>
            <input type="file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.txt,.png,.jpg,.jpeg,.webp"
                class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-100" />
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Até 20 MB. PDF, Office, ZIP ou imagens. O aluno baixa por link autenticado na página da aula.</p>
            @if ($action === 'edit' && $classLesson?->material_file_path)
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    Arquivo atual: <span class="font-medium">{{ $classLesson->material_file_name ?: basename($classLesson->material_file_path) }}</span>
                </p>
                <label class="mt-2 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="remove_material_file" value="1" @checked(old('remove_material_file'))>
                    Remover arquivo atual
                </label>
            @endif
            @error('material_file')
                <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
        </div>
    </div>
    <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
        <button type="submit" class="inline-flex rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Aula' }}
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function() {
                var courseClassSearch = document.getElementById('course_class_search');
                var courseClassId = document.getElementById('course_class_id');
                var resultsBox = document.getElementById('course-class-search-results');
                if (!courseClassSearch || !courseClassId || !resultsBox) return;

                var timer;
                var searchUrl = '{{ route('admin.aulas.turmas.search') }}';

                function hideResults() {
                    resultsBox.classList.add('hidden');
                    resultsBox.innerHTML = '';
                }

                function renderResults(items) {
                    if (!Array.isArray(items) || !items.length) {
                        resultsBox.innerHTML =
                            '<div class="px-3 py-2 text-sm text-gray-500">Nenhuma turma encontrada.</div>';
                    } else {
                        resultsBox.innerHTML = items.map(function(item) {
                            var course = item.course ? '<div class="text-xs text-gray-500">Curso: ' + item.course + '</div>' : '';
                            return '<button type="button" data-id="' + item.id + '" data-name="' + item.name
                                .replace(/"/g, '&quot;') +
                                '" class="w-full text-left px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-200"><div class="font-medium">' +
                                item.name + '</div>' + course + '</button>';
                        }).join('');
                    }
                    resultsBox.classList.remove('hidden');
                }

                function fetchCourseClasses(q) {
                    resultsBox.innerHTML = '<div class="px-3 py-2 text-sm text-gray-500">Buscando...</div>';
                    resultsBox.classList.remove('hidden');

                    fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            renderResults(data);
                        })
                        .catch(function() {
                            resultsBox.innerHTML =
                                '<div class="px-3 py-2 text-sm text-red-500">Erro ao buscar turmas.</div>';
                        });
                }

                courseClassSearch.addEventListener('input', function() {
                    courseClassId.value = '';
                    clearTimeout(timer);
                    var q = courseClassSearch.value.trim();
                    if (q.length < 2) {
                        hideResults();
                        return;
                    }
                    timer = setTimeout(function() {
                        fetchCourseClasses(q);
                    }, 300);
                });

                courseClassSearch.addEventListener('focus', function() {
                    var q = courseClassSearch.value.trim();
                    if (q.length >= 2) {
                        clearTimeout(timer);
                        timer = setTimeout(function() {
                            fetchCourseClasses(q);
                        }, 100);
                    }
                });

                resultsBox.addEventListener('click', function(e) {
                    var btn = e.target.closest('button[data-id]');
                    if (!btn) return;
                    courseClassId.value = btn.getAttribute('data-id');
                    courseClassSearch.value = btn.getAttribute('data-name');
                    hideResults();
                });

                document.addEventListener('click', function(e) {
                    if (!resultsBox.contains(e.target) && e.target !== courseClassSearch) {
                        hideResults();
                    }
                });
            })();
        </script>
    @endpush
@endonce
