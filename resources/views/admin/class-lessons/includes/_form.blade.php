<form method="POST" enctype="multipart/form-data"
    action="{{ $action === 'edit' ? route('admin.aulas.update', $classLesson) : route('admin.aulas.store') }}"
    class="js-ajax-upload-form w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    @php
        $prefill = isset($prefillCourseClass) ? $prefillCourseClass : null;
        $selectedCourseClassId = old('course_class_id', $classLesson?->course_class_id ?? $prefill?->id);
        $selectedCourseClassName = old('course_class_search', $classLesson?->courseClass?->name ?? $prefill?->name ?? '');
        $aulaDoCatalogo = $classLesson?->isFromCatalog() ? $classLesson->catalogLesson : null;
    @endphp

    @if ($aulaDoCatalogo)
        <div class="mb-6 rounded-lg border border-violet-200 bg-violet-50/70 p-4 dark:border-violet-800/60 dark:bg-violet-950/30">
            <div class="flex flex-wrap items-center gap-2">
                <x-badge color="violet" text="Conteúdo regional" />
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $aulaDoCatalogo->titulo }}</span>
            </div>
            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                O aluno já vê
                <strong>{{ $aulaDoCatalogo->hasVideo() ? 'o vídeo' : 'nenhum vídeo' }}</strong>
                e
                <strong>{{ $aulaDoCatalogo->hasMaterial() ? 'o material' : 'nenhum material' }}</strong>
                desta aula direto do catálogo da direção regional. Os campos abaixo ficam em branco de propósito.
            </p>
            <p class="mt-2 text-xs text-gray-600 dark:text-gray-400">
                Preencha vídeo ou material aqui só para <strong>substituir</strong> o conteúdo regional por uma versão da câmara.
            </p>
        </div>
    @endif

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

        <div class="md:col-span-3 space-y-3 rounded-lg border border-gray-200 bg-gray-50/60 p-4 dark:border-gray-700 dark:bg-gray-900/40">
            <div class="flex flex-wrap items-center gap-2">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Vídeo da aula</p>
                @if ($classLesson?->effectiveVideoSourceLabel())
                    <x-badge :color="$classLesson->isUploadedVideo() || $classLesson->effectiveVideoIsNative() ? 'green' : 'blue'"
                        :text="$classLesson->effectiveVideoSourceLabel()" />
                @endif
            </div>

            <x-form.input name="video_url" label="Link do vídeo (YouTube / Vimeo)" :value="$classLesson?->video_url ?? old('video_url')"
                hint="YouTube e Vimeo tocam embutidos. Se anexar um arquivo abaixo, o arquivo tem prioridade." />

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo de vídeo (MP4)</label>

                @if ($action === 'edit' && $classLesson?->video_path)
                    <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($classLesson->video_path) }}" target="_blank" rel="noopener"
                            class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                            Vídeo anexado atual
                        </a>
                        <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="remove_video" value="1" @checked(old('remove_video'))>
                            Remover arquivo
                        </label>
                    </div>
                    <video controls preload="metadata" class="mt-3 max-h-48 w-full rounded-xl bg-black"
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($classLesson->video_path) }}"></video>
                @endif

                <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
                    class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-100" />
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    MP4, WebM ou MOV, até 200 MB. Preferência: comprima com
                    <a href="https://handbrake.fr/" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">HandBrake</a>
                    (preset Fast 720p30 e marque <strong>Web Optimized</strong>) antes de enviar. Vídeo longo ou maior que isso: use o link do YouTube acima.
                </p>
                @error('video_file')
                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
            </div>
        </div>

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
        @include('partials.ajax-form-upload-progress')
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
