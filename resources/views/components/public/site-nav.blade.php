@props([
    'productId' => 'produto',
])

<header {{ $attributes->merge(['class' => 'sticky top-0 z-[60] border-b border-sky-200/80 bg-white/95 shadow-sm backdrop-blur-md']) }}>
    <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6 lg:px-8">
        <a href="{{ url('/') }}" class="group flex shrink-0 items-center gap-3 rounded-lg outline-none ring-offset-2 focus-visible:ring-2 focus-visible:ring-sky-500">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" width="160" height="44" class="h-9 w-auto object-contain transition duration-300 group-hover:opacity-90 sm:h-10"/>
            @else
                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-sm font-bold text-white shadow-md">{{ strtoupper(substr(config('app.name'), 0, 2)) }}</span>
                <span class="hidden font-display text-lg font-semibold text-slate-900 sm:inline">{{ config('app.name') }}</span>
            @endif
        </a>

        <nav class="hidden items-center gap-0.5 md:flex" aria-label="Principal">
            @foreach ([['#'. $productId, 'Produto'], ['#jornada', 'Jornada'], ['#modulos', 'Módulos'], ['#planos', 'Planos'], ['#faq', 'FAQ']] as [$href, $label])
                <a href="{{ $href }}" class="rounded-full px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:bg-sky-100 hover:text-sky-900">{{ $label }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="{{ route('tenant.login') }}"
               class="hidden rounded-full border border-sky-300 bg-white px-3.5 py-2 text-sm font-semibold text-sky-900 shadow-sm transition hover:bg-sky-50 sm:inline-flex">
                Área do cliente
            </a>
            <a href="{{ route('central.login') }}"
               class="inline-flex rounded-full bg-sky-600 px-3.5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-500 focus-visible:ring-offset-2">
                Central
            </a>
            <button type="button"
                    id="public-nav-toggle"
                    class="inline-flex rounded-xl border border-sky-200 bg-white p-2 text-slate-800 shadow-sm transition hover:bg-sky-50 md:hidden"
                    aria-expanded="false"
                    aria-controls="public-nav-panel">
                <span class="sr-only">Abrir menu</span>
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
        </div>
    </div>
    <div id="public-nav-panel" class="hidden border-t border-sky-100 bg-white px-4 py-4 md:hidden">
        <nav class="flex flex-col gap-0.5 text-sm font-medium text-slate-900" aria-label="Mobile">
            <a href="#{{ $productId }}" class="rounded-xl px-3 py-3 hover:bg-sky-50">Produto</a>
            <a href="#jornada" class="rounded-xl px-3 py-3 hover:bg-sky-50">Jornada</a>
            <a href="#modulos" class="rounded-xl px-3 py-3 hover:bg-sky-50">Módulos</a>
            <a href="#planos" class="rounded-xl px-3 py-3 hover:bg-sky-50">Planos</a>
            <a href="#faq" class="rounded-xl px-3 py-3 hover:bg-sky-50">FAQ</a>
            <a href="{{ route('tenant.login') }}" class="mt-2 rounded-xl border border-sky-200 bg-sky-50 px-3 py-3 text-center font-semibold text-sky-950">Área do cliente</a>
        </nav>
    </div>
</header>
