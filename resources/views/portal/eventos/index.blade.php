@extends('layouts.portal')

@section('title', 'Eventos')

@section('content')
    <x-portal.page-hero title="Eventos" subtitle="Agenda institucional, encontros e atividades junto à comunidade." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @forelse($eventos as $evento)
                <article class="portal-animate-card flex flex-wrap gap-6 rounded-2xl border border-slate-200/80 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="flex h-24 w-24 shrink-0 flex-col items-center justify-center rounded-2xl text-white shadow-inner"
                         style="background:linear-gradient(145deg,var(--portal-secondary),var(--portal-primary))">
                        <span class="text-2xl font-black">{{ $evento->date_time?->format('d') }}</span>
                        <span class="text-xs font-semibold uppercase">{{ $evento->date_time?->format('m/y') }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white">
                            <a href="{{ route('portal.eventos.show', ['evento' => $evento->id]) }}" class="hover:underline">{{ $evento->title }}</a>
                        </h2>
                        <p class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-500">
                            <span>{{ $evento->date_time?->format('d/m/Y H:i') }}</span>
                            @if($evento->city)
                                <span>{{ $evento->city }}{{ $evento->state ? ' — '.$evento->state : '' }}</span>
                            @endif
                        </p>
                        @if($evento->description)
                            <p class="mt-4 line-clamp-3 text-slate-600 dark:text-slate-300">{{ strip_tags($evento->description) }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="text-center text-slate-500">Nenhum evento cadastrado.</p>
            @endforelse
            <div class="flex justify-center pt-8">
                {{ $eventos->links() }}
            </div>
        </div>
    </section>
@endsection
