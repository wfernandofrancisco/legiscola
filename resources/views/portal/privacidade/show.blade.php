@extends('layouts.portal')

@section('title', 'Privacidade e dados pessoais')

@section('content')
    <x-portal.page-hero
        title="Privacidade e dados pessoais"
        subtitle="Informações sobre tratamento de dados pessoais e segurança, em linha com a LGPD (Lei 13.709/2018)."
    />

    <section class="py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            @if($term)
                <div class="rounded-2xl border border-slate-200 bg-white p-8 shadow-sm dark:border-slate-800 dark:bg-slate-900/50">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Versão {{ $term->version }} · atualizado em {{ $term->published_at?->format('d/m/Y') }}</p>
                    <h2 class="mt-2 text-lg font-bold text-slate-900 dark:text-white">{{ $term->title }}</h2>
                    <div class="prose prose-slate prose-sm mt-6 max-w-none dark:prose-invert">
                        {!! $term->body_html !!}
                    </div>
                </div>
            @else
                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                    <p class="font-semibold">Termo em elaboração</p>
                    <p class="mt-2">Ainda não há versão publicada do termo global. Para dúvidas sobre dados pessoais, utilize o <a href="{{ route('portal.contato') }}" class="underline font-medium" style="color:var(--portal-primary)">canal de contato</a> da {{ $tenant->portalBrandTitle() }}.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
