@extends('layouts.portal')

@section('title', 'Professores e credenciados')

@section('content')
    <x-portal.page-hero title="Professores e credenciamento" subtitle="Corpo docente ativo e resoluções em vigência, com documentos de apoio." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h2 class="text-xl font-bold text-slate-900 dark:text-white">Credenciamentos</h2>
            <div class="mt-8 space-y-6">
                @forelse($credenciamentos as $cred)
                    <article class="portal-animate-card rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="flex flex-wrap justify-between gap-4">
                            <h3 class="text-lg font-bold text-slate-900 dark:text-white">{{ $cred->titulo }}</h3>
                            @if($cred->ano_referencia)
                                <span class="h-fit rounded-full px-4 py-1 text-xs font-bold text-white shadow" style="background:linear-gradient(135deg,var(--portal-secondary),var(--portal-primary))">{{ $cred->ano_referencia }}</span>
                            @endif
                        </div>
                        @if($cred->texto)
                            <div class="portal-prose portal-prose--sm mt-4 max-w-none">{!! $cred->texto !!}</div>
                        @endif
                        @if($cred->anexos->isNotEmpty())
                            <ul class="mt-6 space-y-2 text-sm font-medium">
                                @foreach($cred->anexos as $anexo)
                                    @php
                                        $ext = strtolower(pathinfo((string) $anexo->arquivo_path, PATHINFO_EXTENSION));
                                        $isPdf = $ext === 'pdf';
                                    @endphp
                                    <li>
                                        <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($anexo->arquivo_path) }}"
                                           class="inline-flex max-w-full items-center gap-2.5 rounded-lg py-1 text-slate-800 underline decoration-slate-300 underline-offset-2 transition hover:bg-slate-50 hover:decoration-slate-500 dark:text-slate-100 dark:decoration-slate-600 dark:hover:bg-slate-800/50"
                                           target="_blank" rel="noopener">
                                            @if($isPdf)
                                                <span class="inline-flex shrink-0 items-center justify-center rounded-md bg-red-600 p-1.5 text-white shadow-sm ring-1 ring-red-700/30" title="PDF">
                                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zm-1 2l5 5h-5V4zM8.5 18h7v-1.25H8.5V18zm0-3.5h7v-1.25H8.5v1.25zm0-3.5h4.5V9.75H8.5V11z"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex shrink-0 rounded-md bg-slate-200 p-1.5 text-slate-700 dark:bg-slate-700 dark:text-slate-200" title="Arquivo">
                                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                            <span class="min-w-0 break-words">{{ $anexo->titulo ?: 'Anexo' }}@if($isPdf)<span class="sr-only"> (PDF)</span>@endif</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </article>
                @empty
                    <p class="text-slate-500">Sem credenciamentos publicados no período atual.</p>
                @endforelse
            </div>
            <div class="mt-10 flex justify-center">
                {{ $credenciamentos->links() }}
            </div>

            <h2 class="mt-24 text-xl font-bold text-slate-900 dark:text-white">Professores</h2>
            <div class="mt-8 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                @forelse($professores as $professor)
                    <div class="portal-animate-card rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900/40">
                        <div class="mx-auto mb-4 h-28 w-28 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            @if(!empty($professor->photo_path))
                                <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($professor->photo_path) }}" alt="" class="h-full w-full object-cover" loading="lazy"/>
                            @else
                                <div class="flex h-full items-center justify-center text-3xl font-bold text-slate-400">{{ mb_strtoupper(mb_substr($professor->full_name, 0, 1)) }}</div>
                            @endif
                        </div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $professor->full_name }}</p>
                        @if($professor->specialities)
                            <p class="mt-2 text-xs text-slate-500">{{ $professor->specialities }}</p>
                        @endif
                        @if($professor->bio)
                            <p class="mt-3 line-clamp-4 text-xs text-slate-600 dark:text-slate-400">{{ $professor->bio }}</p>
                        @endif
                    </div>
                @empty
                    <p class="col-span-full text-slate-500">Nenhum professor cadastrado.</p>
                @endforelse
            </div>
            <div class="mt-10 flex justify-center">
                {{ $professores->links() }}
            </div>
        </div>
    </section>
@endsection
