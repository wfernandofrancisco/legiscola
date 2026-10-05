@extends('layouts.portal')

@section('title', 'Eventos')

@section('content')
    <x-portal.page-hero title="Eventos" subtitle="Agenda institucional, encontros e atividades junto à comunidade." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            @forelse($eventos as $evento)
                <article class="portal-animate-card overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900/40 {{ $evento->photo_path ? 'sm:flex sm:items-stretch' : 'flex flex-wrap gap-6 p-8' }}">
                    @if($evento->photo_path)
                        <a href="{{ route('portal.eventos.show', ['evento' => $evento->id]) }}"
                           class="relative block shrink-0 overflow-hidden sm:w-64 md:w-72">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evento->photo_path) }}"
                                 alt=""
                                 class="h-44 w-full object-cover sm:h-full sm:min-h-[11rem]"
                                 loading="lazy"/>
                            <span class="absolute left-3 top-3 inline-flex min-w-[3.25rem] flex-col items-center rounded-xl bg-black/55 px-2 py-1.5 text-white shadow-sm backdrop-blur-sm">
                                <span class="text-lg font-black leading-none">{{ $evento->date_time?->format('d') }}</span>
                                <span class="mt-0.5 text-[10px] font-semibold uppercase tracking-wide">{{ $evento->date_time?->format('m/y') }}</span>
                            </span>
                        </a>
                        <div class="min-w-0 flex-1 p-6 sm:p-8">
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
                    @else
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
                    @endif
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
