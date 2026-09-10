@php
    /** @var \App\Models\CatalogLesson|null $lesson */
@endphp

<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-form.input name="titulo" label="Título da aula" required :value="$lesson?->titulo" />
    </div>

    <div class="sm:col-span-2">
        <x-form.textarea name="descricao" label="Descrição" rows="3" :value="$lesson?->descricao" />
    </div>

    <div class="sm:col-span-2">
        <x-form.input name="video_url" label="Link do vídeo" :value="$lesson?->video_url"
            hint="YouTube e Vimeo tocam embutidos. Se enviar um arquivo abaixo, o arquivo tem prioridade." />
    </div>

    <div class="sm:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Arquivo de vídeo (MP4)</label>

        @if ($lesson?->video_path)
            <div class="mb-2 flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ Storage::disk('public')->url($lesson->video_path) }}" target="_blank"
                    rel="noopener" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                    Vídeo atual
                </a>
                <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remove_video" value="1"
                        class="rounded border-slate-300 text-red-600 focus:ring-red-500" />
                    Remover arquivo
                </label>
            </div>
            <video controls preload="metadata" class="mb-3 max-h-48 w-full rounded-xl bg-black"
                src="{{ Storage::disk('public')->url($lesson->video_path) }}"></video>
        @endif

        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-300 dark:file:bg-indigo-950/50 dark:file:text-indigo-300" />
        <p class="mt-1 text-[11px] text-slate-400">MP4, WebM ou MOV, até 200 MB. Enviar arquivo substitui o vídeo anterior.</p>
    </div>

    <x-form.input name="video_duracao_segundos" label="Duração do vídeo (segundos)" type="number"
        :value="$lesson?->video_duracao_segundos" />

    <x-form.input name="material_url" label="Link do material" :value="$lesson?->material_url" />

    <div class="sm:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-slate-700 dark:text-slate-200">Arquivo de material</label>

        @if ($lesson?->material_file_path)
            <div class="mb-2 flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ Storage::disk('public')->url($lesson->material_file_path) }}" target="_blank"
                    rel="noopener" class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">
                    {{ $lesson->material_file_name ?: 'Arquivo atual' }}
                </a>
                <label class="flex items-center gap-2 text-slate-600 dark:text-slate-300">
                    <input type="checkbox" name="remove_material" value="1"
                        class="rounded border-slate-300 text-red-600 focus:ring-red-500" />
                    Remover
                </label>
            </div>
        @endif

        <input type="file" name="material_file"
            class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-slate-300 dark:file:bg-indigo-950/50 dark:file:text-indigo-300" />
        <p class="mt-1 text-[11px] text-slate-400">PDF, Office, imagem ou ZIP, até 20 MB.</p>
    </div>
</div>
