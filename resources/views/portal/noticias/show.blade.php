@extends('layouts.portal')

@section('title', $noticia->titulo)

@section('meta_description')
    {{ \Illuminate\Support\Str::limit(strip_tags((string) ($noticia->subtitulo ?: $noticia->titulo)), 160) }}
@endsection

@push('meta')
    @if($noticia->foto_capa)
        <meta property="og:image" content="{{ $noticia->foto_capa_url }}"/>
    @endif
@endpush

@section('content')
    <article class="pb-20 pt-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">
                {{ $noticia->tipo === \App\Models\Noticia::TIPO_VIDEO ? 'Vídeo' : 'Notícias' }}
            </p>
            <time class="mt-3 block text-sm text-slate-500">{{ $noticia->publicar_em?->format('d/m/Y H:i') }}</time>
            <h1 class="mt-4 text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white">{{ $noticia->titulo }}</h1>
            @if($noticia->subtitulo)
                <p class="mt-4 text-xl leading-relaxed text-slate-600 dark:text-slate-300">{{ $noticia->subtitulo }}</p>
            @endif
            @if($noticia->foto_capa)
                <figure class="mt-10 overflow-hidden rounded-3xl shadow-2xl ring-1 ring-slate-200/60 dark:ring-slate-800">
                    <img src="{{ $noticia->foto_capa_url }}" alt="" class="w-full object-cover" loading="lazy"/>
                </figure>
            @endif

            @if($noticia->tipo === \App\Models\Noticia::TIPO_VIDEO && $noticia->youtube_embed_url)
                <div class="mt-10 overflow-hidden rounded-3xl bg-black shadow-2xl ring-1 ring-slate-200/60 dark:ring-slate-800">
                    <iframe
                        class="aspect-video w-full"
                        src="{{ $noticia->youtube_embed_url }}"
                        title="{{ $noticia->titulo }}"
                        loading="lazy"
                        referrerpolicy="strict-origin-when-cross-origin"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen></iframe>
                </div>
            @endif

            @if($noticia->noticia)
                <div class="portal-prose mt-12 max-w-none">
                    {!! $noticia->noticia !!}
                </div>
            @endif

            @if($noticia->fotos->isNotEmpty())
                <div class="mt-12 grid gap-4 sm:grid-cols-2">
                    @foreach($noticia->fotos as $foto)
                        <figure class="overflow-hidden rounded-2xl bg-slate-100 dark:bg-slate-800">
                            <img src="{{ $foto->url }}" alt="{{ $foto->legenda ?: $noticia->titulo }}" class="aspect-[4/3] h-full w-full object-cover" loading="lazy">
                            @if($foto->legenda)
                                <figcaption class="px-4 py-3 text-xs text-slate-500">{{ $foto->legenda }}</figcaption>
                            @endif
                        </figure>
                    @endforeach
                </div>
            @endif
            @if($noticia->user)
                <p class="mt-12 border-t border-slate-200 pt-8 text-sm text-slate-500 dark:border-slate-800">
                    Conteúdo produzido por <span class="font-medium text-slate-700 dark:text-slate-300">{{ $noticia->user->name }}</span>
                </p>
            @endif
            <div class="mt-10">
                <a href="{{ route('portal.noticias.index') }}" class="inline-flex rounded-full border border-slate-300 px-6 py-2 text-sm font-semibold hover:bg-white dark:border-slate-700 dark:hover:bg-slate-900">← Todas as notícias</a>
            </div>
        </div>
    </article>
@endsection
