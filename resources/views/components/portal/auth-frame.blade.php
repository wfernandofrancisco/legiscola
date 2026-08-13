@props([
    'title',
    'subtitle' => null,
    'contextLabel' => 'Área do aluno',
    'contextHint' => null,
])

@php
    $portalAuthHint = $contextHint ?? 'Cadastro e acesso seguros à área do aluno, cursos e comunicações da escola.';
@endphp

<section class="relative flex-1 overflow-hidden py-12 sm:py-16 lg:py-20">
    <div class="pointer-events-none absolute inset-0 -z-10">
        <div class="absolute -left-24 top-0 h-72 w-72 rounded-full opacity-35 blur-3xl dark:opacity-20"
             style="background: radial-gradient(circle at center, var(--portal-primary), transparent 68%);"></div>
        <div class="absolute -right-20 bottom-0 h-80 w-80 rounded-full opacity-30 blur-3xl dark:opacity-15"
             style="background: radial-gradient(circle at center, var(--portal-tertiary, #22d3ee), transparent 70%);"></div>
        <div class="absolute inset-0 opacity-[0.03] dark:opacity-[0.06]"
             style="background-image: linear-gradient(rgb(15 23 42) 1px, transparent 1px), linear-gradient(90deg, rgb(15 23 42) 1px, transparent 1px); background-size: 48px 48px;"></div>
    </div>

    <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-14">
            <aside class="lg:col-span-5">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-slate-500 transition hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200">
                    <span aria-hidden="true">←</span> Voltar ao portal
                </a>
                <p class="mt-6 text-xs font-bold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">{{ $contextLabel }}</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">
                    {{ $title }}
                </h1>
                @if ($subtitle)
                    <p class="mt-4 max-w-md text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        {{ $subtitle }}
                    </p>
                @endif
                <div class="mt-8 hidden rounded-2xl border border-slate-200/80 bg-white/60 p-6 shadow-sm backdrop-blur-md dark:border-slate-700/80 dark:bg-slate-900/50 lg:block">
                    <p class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        {{ $portalTenant?->portalBrandTitle() ?? config('app.name') }}
                    </p>
                    <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                        {{ $portalAuthHint }}
                    </p>
                </div>
            </aside>

            <div class="lg:col-span-7">
                <div
                    class="portal-animate-card rounded-3xl border border-white/70 bg-white/95 p-6 shadow-2xl backdrop-blur-xl dark:border-slate-800 dark:bg-slate-950/90 sm:p-8"
                    data-animate="zoomIn">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </div>
</section>
