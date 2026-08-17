@php
    $noticia = $noticia ?? null;
    $action = $action ?? 'create';
    $tipoAtual = old('tipo', $noticia?->tipo ?? \App\Models\Noticia::TIPO_COMPLETA);
@endphp

<form id="noticia-form" method="POST"
    action="{{ $action === 'create' ? route('admin.noticias.store') : route('admin.noticias.update', $noticia) }}"
    enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div
            class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                    clip-rule="evenodd" />
            </svg>
            <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-5">
        <fieldset>
            <legend class="mb-3 block text-sm font-medium text-gray-700 dark:text-gray-300">Formato da publicação</legend>
            <div class="grid gap-3 sm:grid-cols-3">
                @foreach ([
                    'completa' => ['Notícia completa', 'Texto, capa e galeria', 'text-indigo-600 bg-indigo-50 dark:bg-indigo-950/40'],
                    'rapida' => ['Notícia rápida', 'Título, link e capa', 'text-amber-700 bg-amber-50 dark:bg-amber-950/40'],
                    'video' => ['Vídeo', 'Link do YouTube', 'text-red-600 bg-red-50 dark:bg-red-950/40'],
                ] as $value => [$label, $hint, $tone])
                    <label class="noticia-type-card relative cursor-pointer rounded-2xl border p-4 transition hover:-translate-y-0.5 hover:shadow-md {{ $tipoAtual === $value ? 'border-indigo-500 ring-2 ring-indigo-500/15' : 'border-gray-200 dark:border-gray-700' }}">
                        <input type="radio" name="tipo" value="{{ $value }}" class="sr-only" {{ $tipoAtual === $value ? 'checked' : '' }}>
                        <span class="mb-3 inline-flex rounded-full px-2.5 py-1 text-[11px] font-bold uppercase tracking-wide {{ $tone }}">{{ $label }}</span>
                        <span class="block text-xs leading-relaxed text-gray-500 dark:text-gray-400">{{ $hint }}</span>
                    </label>
                @endforeach
            </div>
            @error('tipo')
                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
            @enderror
        </fieldset>

        <x-form.input name="titulo" label="Titulo" :required="true" :value="$noticia?->titulo ?? old('titulo')" />

        <x-form.input name="subtitulo" label="Subtitulo" :value="$noticia?->subtitulo ?? old('subtitulo')" />

        <div id="complete-content-fields">
            <label for="noticia_editor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                Conteudo da noticia <span class="text-red-500">*</span>
            </label>
            <textarea id="noticia_editor" name="noticia" rows="10" class="hidden">{{ old('noticia', $noticia?->noticia) }}</textarea>
            <div id="noticia_editor_container"
                class="rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 min-h-[240px]">
            </div>
            @error('noticia')
                <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div id="quick-link-fields" class="hidden rounded-2xl border border-amber-200 bg-amber-50/70 p-5 dark:border-amber-900/60 dark:bg-amber-950/20">
            <x-form.input type="url" name="fonte_url" label="Link da notícia original" :value="$noticia?->fonte_url ?? old('fonte_url')"
                placeholder="https://site-da-noticia.com.br/materia" />
            <p class="mt-2 text-xs text-amber-800/80 dark:text-amber-300/80">Ao clicar no card, o visitante será levado para esta fonte em uma nova aba.</p>
        </div>

        <div id="video-link-fields" class="hidden rounded-2xl border border-red-200 bg-red-50/70 p-5 dark:border-red-900/60 dark:bg-red-950/20">
            <x-form.input type="url" name="video_url" label="Link do vídeo no YouTube" :value="$noticia?->video_url ?? old('video_url')"
                placeholder="https://www.youtube.com/watch?v=..." />
            <p class="mt-2 text-xs text-red-800/80 dark:text-red-300/80">Aceita links youtube.com, youtu.be e Shorts. O vídeo será exibido dentro da página.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-form.input type="datetime-local" name="publicar_em" label="Publicar em" :value="old('publicar_em', $noticia?->publicar_em?->format('Y-m-d\TH:i'))" />
            <x-form.input name="tags" label="Tags (separadas por virgula)" :value="$noticia?->tags ?? old('tags')" />
        </div>

        <div class="space-y-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Foto de capa <span id="cover-required" class="hidden text-red-500">*</span></label>
            @if ($action === 'edit' && $noticia?->foto_capa_url)
                <img id="foto-capa-current" src="{{ $noticia->foto_capa_url }}" alt="Foto de capa atual"
                    class="h-36 w-full max-w-md object-cover rounded-lg border border-gray-200 dark:border-gray-700">
            @endif
            <img id="foto-capa-preview" src="" alt="Preview da foto de capa"
                class="hidden h-36 w-full max-w-md object-cover rounded-lg border border-indigo-200 dark:border-indigo-700">
            <label for="foto_capa"
                class="block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-800/60 px-6 py-8 text-center hover:border-indigo-400 dark:hover:border-indigo-500 transition">
                <input id="foto_capa" type="file" name="foto_capa" accept=".jpg,.jpeg,.png,.webp" class="hidden">
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Clique para escolher a capa</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">JPG, PNG ou WEBP ate 8MB</p>
                    <p id="foto-capa-filename" class="text-xs text-indigo-600 dark:text-indigo-400"></p>
                </div>
            </label>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <x-form.select name="ativo" label="Status" :required="true" :selected="(string) old('ativo', $noticia?->ativo ? '1' : '0')" :options="[
                '1' => 'Ativo',
                '0' => 'Inativo',
            ]" />

            <x-form.select name="is_destaque" label="Destaque" :selected="(string) old('is_destaque', $noticia?->is_destaque ? '1' : '0')" :options="[
                '0' => 'Nao',
                '1' => 'Sim',
            ]" />
        </div>

        <div id="gallery-fields">
            <label for="fotos" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fotos da
                noticia</label>
            <label for="fotos"
                class="block cursor-pointer rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-6 py-8 text-center hover:border-indigo-400 dark:hover:border-indigo-500 transition">
                <input id="fotos" type="file" name="fotos[]" multiple accept=".jpg,.jpeg,.png,.webp"
                    class="hidden" />
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Arraste ou clique para enviar fotos
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Maximo 10 imagens por envio. Ate 2MB por imagem.
                    </p>
                    <p id="fotos-filename" class="text-xs text-indigo-600 dark:text-indigo-400"></p>
                </div>
            </label>
            <div id="fotos-preview-grid" class="mt-3 grid grid-cols-2 md:grid-cols-5 gap-3"></div>
        </div>

        @if ($action === 'edit' && $noticia && $noticia->fotos->count())
            <div id="existing-gallery-fields" class="pt-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Fotos atuais</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ($noticia->fotos as $foto)
                        <label class="block rounded-lg border border-gray-200 dark:border-gray-700 p-2">
                            <img src="{{ $foto->url }}" alt="Foto da noticia"
                                class="w-full h-24 object-cover rounded-md mb-2">
                            <span class="flex items-center gap-2 text-xs text-red-600 dark:text-red-400">
                                <input type="checkbox" name="delete_fotos[]" value="{{ $foto->id }}"
                                    class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                                Remover
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                {{ $action === 'create' ? 'Criar noticia' : 'Salvar alteracoes' }}
            </button>
            <a href="{{ route('admin.noticias.index') }}"
                class="rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Cancelar
            </a>
        </div>
    </div>
</form>

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
    <style>
        .dark .ql-toolbar,
        .dark .ql-container {
            border-color: rgb(55 65 81) !important;
            background: #1e2939;
            color: rgb(243 244 246);
        }

        .dark .ql-editor.ql-blank::before {
            color: rgb(156 163 175);
        }

        .dark .ql-stroke {
            stroke: rgb(209 213 219);
        }

        .dark .ql-fill {
            fill: rgb(209 213 219);
        }

        .dark .ql-picker {
            color: rgb(209 213 219);
        }
    </style>
    <script>
        (function() {
            const typeInputs = Array.from(document.querySelectorAll('input[name="tipo"]'));
            const completeFields = document.getElementById('complete-content-fields');
            const quickFields = document.getElementById('quick-link-fields');
            const videoFields = document.getElementById('video-link-fields');
            const galleryFields = document.getElementById('gallery-fields');
            const existingGalleryFields = document.getElementById('existing-gallery-fields');
            const coverRequired = document.getElementById('cover-required');

            function syncPublicationType() {
                const selected = typeInputs.find((input) => input.checked)?.value || 'completa';

                completeFields?.classList.toggle('hidden', selected !== 'completa');
                quickFields?.classList.toggle('hidden', selected !== 'rapida');
                videoFields?.classList.toggle('hidden', selected !== 'video');
                galleryFields?.classList.toggle('hidden', selected !== 'completa');
                existingGalleryFields?.classList.toggle('hidden', selected !== 'completa');
                coverRequired?.classList.toggle('hidden', selected !== 'rapida');

                document.querySelectorAll('.noticia-type-card').forEach((card) => {
                    const active = card.querySelector('input')?.checked;
                    card.classList.toggle('border-indigo-500', Boolean(active));
                    card.classList.toggle('ring-2', Boolean(active));
                    card.classList.toggle('ring-indigo-500/15', Boolean(active));
                    card.classList.toggle('border-gray-200', !active);
                    card.classList.toggle('dark:border-gray-700', !active);
                });
            }

            typeInputs.forEach((input) => input.addEventListener('change', syncPublicationType));
            syncPublicationType();

            const textarea = document.getElementById('noticia_editor');
            const container = document.getElementById('noticia_editor_container');
            const form = document.getElementById('noticia-form');

            if (textarea && container && form) {
                const quill = new Quill('#noticia_editor_container', {
                    theme: 'snow',
                    placeholder: 'Escreva aqui o conteudo da noticia...',
                    modules: {
                        toolbar: [
                            [{
                                header: [1, 2, 3, false]
                            }],
                            ['bold', 'italic', 'underline'],
                            [{
                                list: 'ordered'
                            }, {
                                list: 'bullet'
                            }],
                            ['link', 'blockquote', 'code-block'],
                            ['clean']
                        ]
                    }
                });

                const initial = textarea.value || '';
                if (initial) {
                    quill.root.innerHTML = initial;
                }

                form.addEventListener('submit', function() {
                    textarea.value = quill.root.innerHTML;
                });
            }

            const capaInput = document.getElementById('foto_capa');
            const capaLabel = document.getElementById('foto-capa-filename');
            const capaPreview = document.getElementById('foto-capa-preview');
            const capaCurrent = document.getElementById('foto-capa-current');
            if (capaInput && capaLabel) {
                capaInput.addEventListener('change', function() {
                    capaLabel.textContent = this.files?.[0]?.name || '';
                    const file = this.files?.[0];

                    if (!file || !capaPreview) {
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        capaPreview.src = e.target?.result || '';
                        capaPreview.classList.remove('hidden');
                        if (capaCurrent) {
                            capaCurrent.classList.add('hidden');
                        }
                    };
                    reader.readAsDataURL(file);
                });
            }

            const fotosInput = document.getElementById('fotos');
            const fotosLabel = document.getElementById('fotos-filename');
            const fotosGrid = document.getElementById('fotos-preview-grid');
            if (fotosInput && fotosLabel) {
                let selectedFiles = [];

                function syncInputFiles() {
                    const dt = new DataTransfer();
                    selectedFiles.forEach((file) => dt.items.add(file));
                    fotosInput.files = dt.files;
                }

                function renderPreviews() {
                    if (!fotosGrid) {
                        return;
                    }

                    fotosGrid.innerHTML = '';

                    selectedFiles.forEach((file) => {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const wrapper = document.createElement('div');
                            wrapper.className =
                                'rounded-lg border border-gray-200 dark:border-gray-700 p-1.5 bg-white dark:bg-gray-800';

                            const img = document.createElement('img');
                            img.src = e.target?.result || '';
                            img.alt = file.name;
                            img.className = 'w-full h-24 object-cover rounded-md';

                            const name = document.createElement('p');
                            name.className = 'mt-1 text-[11px] text-gray-600 dark:text-gray-300 truncate';
                            name.textContent = file.name;

                            wrapper.appendChild(img);
                            wrapper.appendChild(name);
                            fotosGrid.appendChild(wrapper);
                        };
                        reader.readAsDataURL(file);
                    });
                }

                fotosInput.addEventListener('change', function() {
                    const incomingFiles = Array.from(this.files || []);
                    const existingKeys = new Set(
                        selectedFiles.map((file) => `${file.name}-${file.size}-${file.lastModified}`)
                    );

                    incomingFiles.forEach((file) => {
                        const key = `${file.name}-${file.size}-${file.lastModified}`;
                        if (!existingKeys.has(key) && selectedFiles.length < 10) {
                            selectedFiles.push(file);
                            existingKeys.add(key);
                        }
                    });

                    syncInputFiles();
                    renderPreviews();

                    const total = selectedFiles.length;
                    fotosLabel.textContent = total ? `${total} arquivo(s) selecionado(s)` : '';
                });
            }
        })();
    </script>
@endpush
