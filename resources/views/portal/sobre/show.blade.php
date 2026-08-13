@extends('layouts.portal')

@section('title', 'Sobre a escola')

@section('content')
    <x-portal.page-hero narrow title="Sobre a Escola Legislativa" :subtitle="$portalTenant?->portalBrandTitle()" />

    <div class="mx-auto max-w-4xl space-y-20 px-4 py-16 sm:px-6 lg:px-8">
        @if($sobreEscola->institucional)
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Institucional</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $sobreEscola->institucional !!}</div>
            </section>
        @endif

        @if($sobreEscola->objetivos)
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Objetivos</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $sobreEscola->objetivos !!}</div>
            </section>
        @endif

        @if($sobreEscola->quem_somos)
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Quem somos</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $sobreEscola->quem_somos !!}</div>
            </section>
        @endif

        @if($sobreEscola->eixos->isNotEmpty())
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Eixos de atuação</h2>
                <div class="mt-10 grid gap-6 sm:grid-cols-2">
                    @foreach($sobreEscola->eixos as $eixo)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40">
                            <h3 class="font-bold text-slate-900 dark:text-white">{{ $eixo->titulo }}</h3>
                            @if($eixo->descricao)
                                <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">{{ $eixo->descricao }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        @if($sobreEscola->projeto_pedagogico)
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Projeto pedagógico</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $sobreEscola->projeto_pedagogico !!}</div>
            </section>
        @endif

        @if($sobreEscola->legislacao)
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Legislação e normativos</h2>
                <div class="portal-prose mt-6 max-w-none">{!! $sobreEscola->legislacao !!}</div>
            </section>
        @endif

        @if($sobreEscola->pessoas->isNotEmpty())
            <section data-animate="fadeInUp">
                <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Equipes e lideranças</h2>
                <div class="mt-10 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach($sobreEscola->pessoas as $p)
                        <div class="rounded-2xl border border-slate-200 bg-white p-6 text-center dark:border-slate-800 dark:bg-slate-900/40">
                            <div class="mx-auto mb-4 h-24 w-24 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                @if(!empty($p->foto_path))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($p->foto_path) }}" alt="" class="h-full w-full object-cover"/>
                                @else
                                    <div class="flex h-full items-center justify-center text-2xl font-bold text-slate-400">{{ mb_strtoupper(mb_substr($p->nome, 0, 1)) }}</div>
                                @endif
                            </div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $p->nome }}</p>
                            @if($p->cargo)
                                <p class="mt-2 text-xs font-medium uppercase tracking-wider text-slate-500">{{ $p->cargo }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
