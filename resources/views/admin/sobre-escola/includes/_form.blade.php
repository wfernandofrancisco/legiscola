@php
    $item = $item ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST"
    action="{{ $action === 'edit' ? route('admin.sobre-escola.update', $item) : route('admin.sobre-escola.store') }}"
    enctype="multipart/form-data"
    id="sobre-escola-form"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-5">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Institucional</label>
            <textarea class="js-ckeditor" name="institucional">{{ $item?->institucional ?? old('institucional') }}</textarea>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Objetivos</label>
            <textarea class="js-ckeditor" name="objetivos">{{ $item?->objetivos ?? old('objetivos') }}</textarea>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Quem somos (texto introdutório)</label>
            <textarea class="js-ckeditor" name="quem_somos">{{ $item?->quem_somos ?? old('quem_somos') }}</textarea>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Projeto pedagógico</label>
            <textarea class="js-ckeditor" name="projeto_pedagogico">{{ $item?->projeto_pedagogico ?? old('projeto_pedagogico') }}</textarea>
        </div>
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Legislação</label>
            <textarea class="js-ckeditor" name="legislacao">{{ $item?->legislacao ?? old('legislacao') }}</textarea>
        </div>
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Eixos temáticos</h3>

        @php
            $existingEixos = $item?->eixos ?? collect();
            $oldTitles = old('eixo_titulos', []);
            $oldDescriptions = old('eixo_descricoes', []);
            $rows = max(4, count($existingEixos), count($oldTitles));
        @endphp

        @for ($i = 0; $i < $rows; $i++)
            @php
                $defaultTitle = $oldTitles[$i] ?? ($existingEixos[$i]->titulo ?? '');
                $defaultDescription = $oldDescriptions[$i] ?? ($existingEixos[$i]->descricao ?? '');
            @endphp
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
                <x-form.input name="eixo_titulos[]" label="Título do eixo {{ $i + 1 }}" :value="$defaultTitle" />
                <x-form.input name="eixo_descricoes[]" label="Descrição do eixo {{ $i + 1 }}" :value="$defaultDescription" />
            </div>
        @endfor
    </div>

    <div class="mt-6 rounded-xl border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Quem somos - Pessoas</h3>

        @if ($action === 'edit' && $item?->pessoas?->count())
            <div class="mb-4 space-y-2">
                @foreach ($item->pessoas as $pessoa)
                    <label class="flex items-center justify-between gap-3 text-sm border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-2">
                        <span class="flex items-center gap-3">
                            @if ($pessoa->foto_path)
                                <img src="{{ asset('storage/' . $pessoa->foto_path) }}" class="w-10 h-10 rounded-full object-cover" alt="{{ $pessoa->nome }}">
                            @endif
                            <span>
                                <strong>{{ $pessoa->nome }}</strong> - {{ $pessoa->cargo }}
                            </span>
                        </span>
                        <span class="inline-flex items-center gap-1">
                            <input type="checkbox" name="delete_pessoas[]" value="{{ $pessoa->id }}"
                                class="rounded border-gray-300 text-red-600 focus:ring-red-500">
                            <span class="text-xs text-gray-500">Excluir</span>
                        </span>
                    </label>
                @endforeach
            </div>
        @endif

        @for ($i = 0; $i < 6; $i++)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-4 rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                <div class="md:col-span-1 flex items-center justify-center">
                    <img id="pessoa-preview-{{ $i }}"
                        src="https://placehold.co/88x88/e5e7eb/6b7280?text=Foto"
                        alt="Pré-visualização"
                        class="w-22 h-22 rounded-full object-cover border border-gray-200 dark:border-gray-700">
                </div>
                <div class="md:col-span-2 grid grid-cols-1 gap-3">
                    <x-form.input name="pessoa_nomes[]" label="Nome {{ $i + 1 }}" :value="old('pessoa_nomes.' . $i)" />
                    <x-form.input name="pessoa_cargos[]" label="Cargo {{ $i + 1 }}" :value="old('pessoa_cargos.' . $i)" />
                </div>
                <div class="md:col-span-1">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto {{ $i + 1 }}</label>
                    <input type="file" name="pessoa_fotos[]" accept="image/*" data-preview-target="pessoa-preview-{{ $i }}"
                        class="js-pessoa-foto w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG/JPG até 2MB</p>
                </div>
            </div>
        @endfor
    </div>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Salvar Conteúdo' }}
        </button>
        <a href="{{ route('admin.sobre-escola.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable_inline {
            min-height: 220px;
        }

        .dark .ck.ck-toolbar,
        .dark .ck.ck-editor__main > .ck-editor__editable {
            background: #1f2937;
            color: #e5e7eb;
            border-color: #374151;
        }

        .dark .ck.ck-editor__top .ck-sticky-panel .ck-sticky-panel__content,
        .dark .ck.ck-toolbar,
        .dark .ck.ck-dropdown__panel,
        .dark .ck.ck-list {
            background: #111827;
            border-color: #374151;
        }

        .dark .ck.ck-button,
        .dark .ck.ck-button .ck-button__label,
        .dark .ck.ck-dropdown .ck-dropdown__button,
        .dark .ck.ck-dropdown .ck-button__label,
        .dark .ck.ck-list__item .ck-button {
            color: #e5e7eb;
        }

        .dark .ck.ck-button .ck-icon,
        .dark .ck.ck-dropdown .ck-icon {
            color: #e5e7eb;
        }

        .dark .ck.ck-button:not(.ck-disabled):hover,
        .dark .ck.ck-button:not(.ck-disabled):focus,
        .dark .ck.ck-button.ck-on,
        .dark .ck.ck-list__item .ck-button:hover,
        .dark .ck.ck-list__item .ck-button.ck-on {
            background: #374151;
            color: #ffffff;
        }

        .dark .ck.ck-toolbar .ck-toolbar__separator {
            background: #4b5563;
        }

        .dark .ck.ck-editor__editable a {
            color: #93c5fd;
        }

        .dark .ck.ck-editor__editable blockquote {
            border-left-color: #6366f1;
            color: #d1d5db;
        }
    </style>
    <script>
        (function() {
            document.querySelectorAll('.js-ckeditor').forEach(function(el) {
                ClassicEditor.create(el, {
                    toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', '|', 'undo', 'redo']
                }).catch(function(error) {
                    console.error(error);
                });
            });

            document.querySelectorAll('.js-pessoa-foto').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const file = e.target.files && e.target.files[0];
                    const previewId = e.target.getAttribute('data-preview-target');
                    const preview = previewId ? document.getElementById(previewId) : null;
                    if (!file || !preview) return;

                    const reader = new FileReader();
                    reader.onload = function(ev) {
                        preview.src = ev.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            });
        })();
    </script>
@endpush
