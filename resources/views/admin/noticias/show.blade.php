<x-layouts.admin>
    <x-slot name="title">{{ $noticia->titulo }}</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-subpage-header :title="$noticia->titulo" subtitle="Detalhes da noticia publicada." />

    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8 space-y-6">
        <div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Informacoes gerais</h3>
            @if ($noticia->foto_capa_url)
                <img src="{{ $noticia->foto_capa_url }}" alt="Foto de capa"
                    class="h-48 w-full max-w-2xl object-cover rounded-lg border border-gray-200 dark:border-gray-700 mb-4">
            @endif
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Subtitulo</dt>
                    <dd class="mt-1 text-gray-900 dark:text-white">{{ $noticia->subtitulo ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Slug</dt>
                    <dd class="mt-1 text-gray-900 dark:text-white">{{ $noticia->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Tags</dt>
                    <dd class="mt-1 text-gray-900 dark:text-white">{{ $noticia->tags ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-500 uppercase">Publicar em</dt>
                    <dd class="mt-1 text-gray-900 dark:text-white">{{ $noticia->publicar_em?->format('d/m/Y H:i') ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Conteudo</h3>
            <div class="prose prose-sm max-w-none dark:prose-invert whitespace-pre-line text-gray-700 dark:text-gray-300">
                {{ $noticia->noticia }}
            </div>
        </div>

        @if ($noticia->fotos->count())
            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Fotos</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach ($noticia->fotos as $foto)
                        <img src="{{ $foto->url }}" alt="Foto da noticia" class="w-full h-28 object-cover rounded-lg">
                    @endforeach
                </div>
            </div>
        @endif

        <div class="pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center gap-3">
            <a href="{{ route('admin.noticias.edit', $noticia) }}"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
                Editar
            </a>
            <a href="{{ route('admin.noticias.index') }}"
                class="rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                Voltar
            </a>
        </div>
    </div>
</x-layouts.admin>
