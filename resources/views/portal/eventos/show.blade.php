@extends('layouts.portal')

@section('title', $evento->title)

@section('meta_description')
    {{ \Illuminate\Support\Str::limit(strip_tags((string) $evento->description), 160) }}
@endsection

@push('meta')
    @if($evento->photo_path)
        <meta property="og:image" content="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evento->photo_path) }}"/>
    @endif
@endpush

@section('content')
    @php
        $addr = trim(collect([
            $evento->address && $evento->number ? $evento->address.', '.$evento->number : ($evento->address ?? ''),
            $evento->district,
            $evento->city,
            $evento->state ? $evento->state.', '.$evento->zipcode : $evento->zipcode,
        ])->filter()->join(' — '));
        $heroSubtitle = collect([
            $evento->date_time?->format('d/m/Y — H:i'),
            $evento->city ? trim($evento->city.($evento->state ? ' · '.$evento->state : '')) : null,
        ])->filter()->join(' · ');
    @endphp

    <x-portal.page-hero align="center" narrow :title="$evento->title" :subtitle="($heroSubtitle ?? '') !== '' ? $heroSubtitle : 'Evento institucional'">
        <x-slot name="actions">
            <a href="{{ route('portal.eventos.index') }}"
               class="inline-flex shrink-0 rounded-full border border-slate-800/15 bg-white/90 px-5 py-2.5 text-sm font-semibold text-slate-800 shadow-sm backdrop-blur hover:bg-white dark:border-white/20 dark:bg-slate-900/60 dark:text-slate-100 dark:hover:bg-slate-800">
                ← Agenda
            </a>
            <a href="{{ route('portal.contato') }}"
               class="inline-flex shrink-0 rounded-full px-5 py-2.5 text-sm font-semibold text-white shadow-md hover:opacity-95"
               style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                Fale conosco
            </a>
        </x-slot>
    </x-portal.page-hero>

    <article>
        @if($evento->photo_path)
            <div class="mx-auto max-w-4xl px-4 pt-10 sm:px-6 lg:px-8">
                <div class="overflow-hidden rounded-2xl border border-slate-200/80 shadow-lg dark:border-slate-800">
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($evento->photo_path) }}" alt="" class="aspect-[21/9] w-full object-cover sm:aspect-[2/1]" loading="lazy"/>
                </div>
            </div>
        @endif

        <div class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
            <p class="text-xs font-semibold uppercase tracking-[0.2em]" style="color:var(--portal-primary)">Evento</p>
            <time class="mt-2 block text-sm text-slate-500 dark:text-slate-400">{{ $evento->date_time?->format('d/m/Y — H:i') }}</time>

            @if($addr !== '')
                <div class="mt-8 rounded-2xl border border-slate-200 bg-slate-50 p-6 text-sm dark:border-slate-800 dark:bg-slate-900/50">
                    <p class="font-semibold text-slate-900 dark:text-white">Local</p>
                    <p class="mt-2 text-slate-600 dark:text-slate-300">{{ $addr }}</p>
                </div>
            @endif

            @if($evento->max_seats !== null && $evento->max_seats > 0)
                <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">
                    Limite de <span class="font-bold text-slate-900 dark:text-white">{{ $evento->max_seats }}</span> {{ $evento->max_seats === 1 ? 'inscrição' : 'inscrições' }}
                    @if($inscricaoPortalAberta)
                        — {{ $evento->enrollments_count }} {{ $evento->enrollments_count === 1 ? 'confirmada' : 'confirmadas' }} até o momento.
                    @endif
                </p>
            @elseif($evento->allow_online_registration && $inscricaoPortalAberta)
                <p class="mt-4 text-sm font-medium text-slate-600 dark:text-slate-400">
                    Vagas não numeradas — {{ $evento->enrollments_count }} {{ $evento->enrollments_count === 1 ? 'inscrição' : 'inscrições' }} até o momento.
                </p>
            @endif

            @if($evento->allow_online_registration && $evento->registration_starts_at && $evento->registration_ends_at)
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                    Período de inscrição online:
                    <time datetime="{{ $evento->registration_starts_at->toIso8601String() }}">{{ $evento->registration_starts_at->format('d/m/Y H:i') }}</time>
                    a
                    <time datetime="{{ $evento->registration_ends_at->toIso8601String() }}">{{ $evento->registration_ends_at->format('d/m/Y H:i') }}</time>.
                </p>
            @endif

            @if($errors->any())
                <div class="mt-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($inscricaoPortalAberta)
                <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/60">
                    <p class="text-sm font-semibold text-slate-900 dark:text-white">Inscrição online</p>
                    @if($jaInscritoNoEvento)
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Você já está inscrito neste evento.</p>
                    @elseif($podeInscricaoPortal)
                        <form method="post" action="{{ route('portal.eventos.inscrever', $evento) }}" class="mt-4">
                            @csrf
                            <button type="submit"
                                class="inline-flex rounded-full px-7 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:opacity-95"
                                style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                                Confirmar minha inscrição
                            </button>
                        </form>
                    @else
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                            Para se inscrever, entre com a conta de aluno (mesmo login da área do aluno).
                        </p>
                        <a href="{{ route('portal.acesso.login') }}"
                            class="mt-4 inline-flex rounded-full border border-slate-300 px-6 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800">
                            Entrar
                        </a>
                    @endif
                </div>
            @endif

            @if($evento->description)
                <div class="portal-prose mt-10 max-w-none">
                    {!! $evento->description !!}
                </div>
            @endif

            <div class="mt-12 flex flex-wrap gap-4">
                <a href="{{ route('portal.eventos.index') }}" class="inline-flex rounded-full border border-slate-300 px-6 py-2 text-sm font-semibold hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-800">← Agenda</a>
                <a href="{{ route('portal.contato') }}" class="inline-flex rounded-full px-7 py-2 text-sm font-semibold text-white shadow-lg hover:opacity-95"
                   style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">Falar conosco</a>
            </div>
        </div>
    </article>
@endsection
