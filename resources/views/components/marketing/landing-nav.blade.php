@props([
    'productId' => 'produto',
])

<header {{ $attributes->merge(['class' => 'sticky top-0 z-50 border-b border-stone-200/90 bg-stone-50/90 backdrop-blur-xl dark:border-stone-800/90 dark:bg-stone-950/90']) }}>
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="group flex shrink-0 items-center gap-3 rounded-lg outline-none focus-visible:ring-2 focus-visible:ring-amber-500/60 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-stone-950">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" width="160" height="44" class="h-9 w-auto object-contain transition duration-300 group-hover:opacity-90 sm:h-10"/>
            @else
                <span class="flex h-10 w-10 items-center justify-center rounded-xl border border-stone-300 bg-white text-sm font-bold tracking-tight text-stone-800 shadow-sm dark:border-stone-600 dark:bg-stone-900 dark:text-stone-100">{{ strtoupper(substr(config('app.name'), 0, 2)) }}</span>
                <span class="hidden text-lg font-semibold tracking-tight text-stone-900 sm:inline dark:text-stone-100">{{ config('app.name') }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-1 md:flex" aria-label="Principal">
            @foreach ([['#'. $productId, 'Produto'], ['#jornada', 'Jornada'], ['#modulos', 'Módulos'], ['#planos', 'Planos'], ['#faq', 'FAQ']] as [$href, $label])
                <a href="{{ $href }}" class="rounded-full px-4 py-2 text-sm font-medium text-stone-600 transition hover:bg-white hover:text-stone-900 hover:shadow-sm dark:text-stone-400 dark:hover:bg-stone-900 dark:hover:text-stone-100">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.login') }}"
               class="hidden rounded-full border border-stone-300 bg-white px-4 py-2 text-sm font-semibold text-stone-800 shadow-sm transition hover:border-stone-400 hover:bg-stone-50 sm:inline-flex dark:border-stone-600 dark:bg-stone-900 dark:text-stone-100 dark:hover:border-stone-500 dark:hover:bg-stone-800">
                Área do cliente
            </a>
            <a href="{{ route('central.login') }}"
               class="inline-flex rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-md transition hover:bg-stone-800 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 dark:bg-amber-500 dark:text-stone-950 dark:hover:bg-amber-400 dark:focus-visible:ring-amber-400 dark:focus-visible:ring-offset-stone-950">
                Central
            </a>
            <button type="button"
                    id="marketing-nav-toggle"
                    class="inline-flex rounded-xl border border-stone-200 bg-white p-2 text-stone-700 shadow-sm transition hover:bg-stone-50 md:hidden dark:border-stone-700 dark:bg-stone-900 dark:text-stone-200 dark:hover:bg-stone-800"
                    aria-expanded="false"
                    aria-controls="marketing-nav-panel">
                <span class="sr-only">Abrir menu</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
        </div>
    </div>
    <div id="marketing-nav-panel" class="hidden border-t border-stone-200 bg-stone-50 px-4 py-4 dark:border-stone-800 dark:bg-stone-950 md:hidden">
        <nav class="flex flex-col gap-0.5 text-sm font-medium" aria-label="Mobile">
            <a href="#{{ $productId }}" class="rounded-xl px-3 py-3 text-stone-800 hover:bg-white dark:text-stone-200 dark:hover:bg-stone-900">Produto</a>
            <a href="#jornada" class="rounded-xl px-3 py-3 text-stone-800 hover:bg-white dark:text-stone-200 dark:hover:bg-stone-900">Jornada</a>
            <a href="#modulos" class="rounded-xl px-3 py-3 text-stone-800 hover:bg-white dark:text-stone-200 dark:hover:bg-stone-900">Módulos</a>
            <a href="#planos" class="rounded-xl px-3 py-3 text-stone-800 hover:bg-white dark:text-stone-200 dark:hover:bg-stone-900">Planos</a>
            <a href="#faq" class="rounded-xl px-3 py-3 text-stone-800 hover:bg-white dark:text-stone-200 dark:hover:bg-stone-900">FAQ</a>
            <a href="{{ route('tenant.login') }}" class="mt-2 rounded-xl border border-stone-300 bg-white px-3 py-3 text-center font-semibold text-stone-900 dark:border-stone-600 dark:bg-stone-900 dark:text-stone-100">Área do cliente</a>
        </nav>
    </div>
</header>
