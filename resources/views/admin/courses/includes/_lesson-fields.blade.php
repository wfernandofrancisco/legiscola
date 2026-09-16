@php
    /** @var \App\Models\CourseLesson|null $lesson */
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <x-form.input name="title" label="Título da aula" required :value="$lesson?->title" />
    </div>
    <div class="md:col-span-2">
        <x-form.textarea name="description" label="Descrição" rows="3" :value="$lesson?->description" />
    </div>
    <div class="md:col-span-2">
        <x-form.input name="video_url" label="Link do vídeo (YouTube / Vimeo)" :value="$lesson?->video_url"
            hint="Sem data nem horário aqui. Isso entra na grade da turma." />
    </div>
    <div class="md:col-span-2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo de vídeo (MP4)</label>
        @if ($lesson?->video_path)
            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($lesson->video_path) }}" target="_blank" rel="noopener"
                    class="font-medium text-indigo-600 hover:underline dark:text-indigo-400">Vídeo atual</a>
                <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                    <input type="checkbox" name="remove_video" value="1"> Remover arquivo
                </label>
            </div>
        @endif
        <input type="file" name="video_file" accept="video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov"
            class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-100" />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Até 200 MB. Comprima com
            <a href="https://handbrake.fr/" target="_blank" rel="noopener" class="font-medium text-indigo-600 hover:underline">HandBrake</a>
            (Fast 720p30 + Web Optimized).
        </p>
        @error('video_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <x-form.input name="material_url" label="Link do material" :value="$lesson?->material_url" />
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Arquivo de material</label>
        @if ($lesson?->material_file_path)
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $lesson->material_file_name ?: basename($lesson->material_file_path) }}</p>
            <label class="mt-1 flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                <input type="checkbox" name="remove_material" value="1"> Remover arquivo
            </label>
        @endif
        <input type="file" name="material_file" accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.zip,.txt,.png,.jpg,.jpeg,.webp"
            class="mt-2 block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-300 dark:file:bg-gray-700 dark:file:text-gray-100" />
        @error('material_file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
