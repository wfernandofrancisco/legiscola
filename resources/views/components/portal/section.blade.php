@props([
    'title' => '',
    'subtitle' => null,
    'id' => null,
    'eyebrow' => null,
])

<section
    {{ $attributes->class(['portal-section relative scroll-mt-28 py-16 sm:py-24 ']) }}
    @if($id) id="{{ $id }}" @endif
>
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if($title !== '')
            <header class="portal-section-heading relative mb-12 max-w-4xl">
                <div class="absolute -left-3 top-1 hidden h-[4.5rem] w-1 rounded-full sm:block lg:-left-5"
                     style="background: linear-gradient(180deg, var(--portal-primary, #3b82f6), var(--portal-secondary, #1e40af), color-mix(in srgb, var(--portal-tertiary, #34d399) 70%, transparent));"
                     aria-hidden="true"></div>

                <div class="mb-8 h-px max-w-xl rounded-full opacity-80"
                     style="background: linear-gradient(90deg, var(--portal-primary, #3b82f6), color-mix(in srgb, var(--portal-tertiary, #34d399) 80%, transparent), transparent);"></div>

                @if (filled($eyebrow))
                    <p class="inline-flex items-center gap-2.5 text-[11px] font-bold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                        <span class="h-2 w-2 shrink-0 rounded-full shadow-sm ring-2 ring-white dark:ring-slate-900" style="background: linear-gradient(135deg, var(--portal-primary), var(--portal-tertiary))"></span>
                        <span class="text-slate-700 dark:text-slate-200">{{ $eyebrow }}</span>
                    </p>
                @endif

                <h2 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl lg:text-[2.65rem] lg:leading-[1.12] dark:text-white">
                    {{ $title }}
                </h2>

                @if ($subtitle)
                    <p class="mt-5 max-w-2xl border-l-2 border-slate-200 pl-5 text-base leading-relaxed text-slate-600 sm:text-lg dark:border-slate-600 dark:text-slate-300"
                       style="border-color: color-mix(in srgb, var(--portal-primary, #3b82f6) 45%, transparent);">
                        {{ $subtitle }}
                    </p>
                @endif
            </header>
        @endif
        <div class="portal-section-body">
            {{ $slot }}
        </div>
    </div>
</section>
