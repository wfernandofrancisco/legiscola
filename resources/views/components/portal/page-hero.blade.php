{{--
  Cores / fundos: não alterar os estilos abaixo — só estrutura (props + slot).
--}}
@props([
    'title' => '',
    'subtitle' => null,
    'eyebrow' => null,
    /** Ignorados aqui — mantidos para compatibilidade com páginas que passam align/narrow */
    'align' => null,
    'narrow' => null,
])

<section {{ $attributes->class(['relative overflow-hidden py-16 sm:py-20 ']) }}>
    <div class="absolute inset-0 -z-10" style="background:linear-gradient(135deg,var(--portal-secondary),color-mix(in srgb,var(--portal-primary) 65%,black))"></div>
    <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_rgba(255,255,255,.12)_0%,transparent_58%)]"></div>
    <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8 animate__animated animate__fadeInUp ">
        <div class="mx-auto mb-8 h-px max-w-md rounded-full opacity-70"
             style="background: linear-gradient(90deg, transparent, rgba(255,255,255,.55), transparent);"></div>

        @if (filled($eyebrow))
            <p class="inline-flex items-center justify-center gap-2 text-[11px] font-bold uppercase tracking-[0.28em] text-white/80">
                <span class="h-2 w-2 rounded-full bg-white/90 shadow-sm ring-2 ring-white/25"></span>
                {{ $eyebrow }}
            </p>
        @endif

        <h2 class="mt-4 text-3xl font-black tracking-tight text-white drop-shadow-sm sm:text-4xl lg:text-[2.5rem] lg:leading-[1.15]">{{ $title }}</h2>
        @if (filled($subtitle))
            <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-white/88 sm:text-lg">{{ $subtitle }}</p>
        @endif
        <div class="mt-10 flex flex-wrap justify-center gap-4">
            @isset($actions)
                {{ $actions }}
            @else
                <a href="{{ route('portal.cursos.index') }}" class="inline-flex rounded-full bg-white px-8 py-3.5 text-sm font-bold text-slate-900 shadow-xl transition hover:bg-slate-100">Explorar turmas</a>
                <a href="{{ route('portal.sobre') }}" class="inline-flex rounded-full border-2 border-white/75 px-8 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Conheça a escola</a>
            @endisset
        </div>
    </div>
</section>
