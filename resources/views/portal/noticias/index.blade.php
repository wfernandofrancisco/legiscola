@extends('layouts.portal')

@section('title', 'Notícias')

@section('content')
    <x-portal.page-hero title="Notícias" subtitle="Últimos comunicados e transparência institucional da Escola Legislativa." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
            @forelse($noticias as $noticia)
                @php
                    $isQuick = $noticia->tipo === \App\Models\Noticia::TIPO_RAPIDA;
                    $isVideo = $noticia->tipo === \App\Models\Noticia::TIPO_VIDEO;
                    $href = $isQuick
                        ? $noticia->fonte_url
                        : route('portal.noticias.show', ['slug' => $noticia->slug]);
                @endphp
                <article class="portal-animate-card group flex flex-col overflow-hidden rounded-[1.4rem] border border-slate-200/80 bg-white shadow-sm transition duration-300 hover:-translate-y-1.5 hover:shadow-2xl dark:border-slate-800 dark:bg-slate-900/60">
                    <a href="{{ $href }}" @if($isQuick) target="_blank" rel="noopener noreferrer" @endif
                       class="relative block aspect-[16/10] overflow-hidden bg-slate-100 dark:bg-slate-800">
                        @if($noticia->foto_capa_url)
                            <img src="{{ $noticia->foto_capa_url }}" alt="" class="h-full w-full object-cover transition duration-700 group-hover:scale-105" loading="lazy"/>
                        @else
                            <div class="absolute inset-0 bg-linear-to-br from-sky-100 via-white to-emerald-100 dark:from-slate-800 dark:via-slate-900 dark:to-emerald-950"></div>
                        @endif

                        <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-extrabold uppercase tracking-[0.14em] shadow-lg backdrop-blur
                            {{ $isQuick ? 'bg-amber-300/95 text-amber-950' : ($isVideo ? 'bg-red-600/95 text-white' : 'bg-white/90 text-slate-800') }}">
                            @if($isVideo)
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                            @elseif($isQuick)
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-6.75 6.75M9 7.5H6.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25V15"/></svg>
                            @endif
                            {{ $noticia->tipo_label }}
                        </span>

                        @if($isVideo)
                            <span class="absolute inset-0 flex items-center justify-center">
                                <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/95 text-red-600 shadow-2xl transition duration-300 group-hover:scale-110">
                                    <svg class="ml-1 h-7 w-7" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                                </span>
                            </span>
                        @endif
                    </a>
                    <div class="flex flex-1 flex-col p-6">
                        <time class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $noticia->publicar_em?->format('d/m/Y') }}</time>
                        <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                            <a class="decoration-2 underline-offset-4 hover:underline" href="{{ $href }}"
                               @if($isQuick) target="_blank" rel="noopener noreferrer" @endif>{{ $noticia->titulo }}</a>
                        </h2>
                        @if($noticia->subtitulo)
                            <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600 dark:text-slate-300">{{ $noticia->subtitulo }}</p>
                        @endif
                        <a href="{{ $href }}" @if($isQuick) target="_blank" rel="noopener noreferrer" @endif
                           class="mt-5 inline-flex items-center gap-2 text-sm font-semibold" style="color:var(--portal-primary)">
                            {{ $isQuick ? 'Acessar fonte' : ($isVideo ? 'Assistir vídeo' : 'Ler artigo') }}
                            <span aria-hidden="true">{{ $isQuick ? '↗' : '→' }}</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-slate-300 px-6 py-16 text-center dark:border-slate-700">
                    <p class="font-semibold text-slate-700 dark:text-slate-200">Nenhuma notícia publicada ainda.</p>
                </div>
            @endforelse
        </div>
        <div class="mx-auto mt-12 flex max-w-7xl justify-center px-4 sm:px-6 lg:px-8">
            {{ $noticias->links() }}
        </div>
    </section>
@endsection
