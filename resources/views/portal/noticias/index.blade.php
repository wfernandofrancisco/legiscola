@extends('layouts.portal')

@section('title', 'Notícias')

@section('content')
    <x-portal.page-hero title="Notícias" subtitle="Últimos comunicados e transparência institucional da Escola Legislativa." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-3 lg:px-8">
            @foreach($noticias as $noticia)
                <article class="portal-animate-card group flex flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="aspect-[16/10] bg-slate-100 dark:bg-slate-800">
                        @if($noticia->foto_capa)
                            <img src="{{ $noticia->foto_capa_url }}" alt="" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.02]" loading="lazy"/>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-6">
                        <time class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $noticia->publicar_em?->format('d/m/Y') }}</time>
                        <h2 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">
                            <a class="hover:underline" href="{{ route('portal.noticias.show', ['slug' => $noticia->slug]) }}">{{ $noticia->titulo }}</a>
                        </h2>
                        @if($noticia->subtitulo)
                            <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-600 dark:text-slate-300">{{ $noticia->subtitulo }}</p>
                        @endif
                        <a href="{{ route('portal.noticias.show', ['slug' => $noticia->slug]) }}" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold"
                           style="color:var(--portal-primary)">Ler artigo<span aria-hidden="true">→</span></a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mx-auto mt-12 flex max-w-7xl justify-center px-4 sm:px-6 lg:px-8">
            {{ $noticias->links() }}
        </div>
    </section>
@endsection
