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
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Notícias</p>
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
            <div class="portal-prose mt-12 max-w-none">
                {!! $noticia->noticia !!}
            </div>
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
