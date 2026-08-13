@extends('layouts.portal')

@section('title', 'Início')

@section('content')
    @php($pt = $portalTenant ?? $tenant)
    @php($sobreSubtitle = (($portalAdminSettings?->nome_camara ?? '') !== '' ? 'Diálogo institucional com a '.$portalAdminSettings->nome_camara.' e com '.($pt?->nome_fantasia ?? $pt?->display_name ?? 'Escola Legislativa') : 'Perfil, valores e governança de '.($pt?->nome_fantasia ?? $pt?->display_name ?? 'Escola Legislativa')).': formação alinhada ao mandato e à transparência na gestão acadêmica.')
    @php($pfPf = $portalPlatform ?? config('portal.platform', []))
    @php($pfLogoPath = $pfPf['logo_path'] ?? null)
    @php($portalShowsLegiscolaLogo = $pfLogoPath && is_string($pfLogoPath) && file_exists(public_path($pfLogoPath)))
    @php($heroEyebrow = $portalShowsLegiscolaLogo ? (($portalAdminSettings?->nome_camara) ?: ($pt?->portalChamberBrandLine())) : ($pt?->portalBrandTitle()))
    <?php
        $heroCapaUrl = null;
        $settingsHero = isset($portalAdminSettings) ? $portalAdminSettings : null;
        $capaPath = $settingsHero?->foto_capa_path;
        if ($capaPath && is_string($capaPath)) {
            $capaFull = storage_path('app/public/'.ltrim($capaPath, '/'));
            if (is_file($capaFull)) {
                $heroCapaUrl = asset('storage/'.$capaPath);
            }
        }
        $heroPhoto = $heroCapaUrl !== null;
    ?>
    {{-- Hero: com foto (tenant_admin_settings.foto_capa_path) — véu escuro + texto claro; sem foto — gradientes Tailwind --}}
    <section class="relative isolate overflow-hidden pb-24 pt-8 sm:pb-32 sm:pt-12 lg:pb-36">
        <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
            @if($heroPhoto)
                <div
                    class="absolute inset-0 opacity-[0.98] dark:opacity-[0.88]"
                    style="background-image: url('{{ $heroCapaUrl }}'); background-size: cover; background-position: center; background-repeat: no-repeat;"
                    aria-hidden="true"></div>
            @else
                <div class="absolute inset-0 bg-gradient-to-br from-slate-100 via-sky-50 to-indigo-100 dark:from-slate-950 dark:via-slate-900 dark:to-indigo-950" aria-hidden="true"></div>
                <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/10 via-transparent to-cyan-500/15 dark:from-blue-500/15 dark:via-transparent dark:to-cyan-400/10" aria-hidden="true"></div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-200/60 via-transparent to-white/80 dark:from-slate-950/80 dark:via-transparent dark:to-slate-900/40" aria-hidden="true"></div>
            @endif
            <div @class([
                'absolute inset-0 bg-gradient-to-b',
                'from-slate-950/82 via-slate-900/55 to-slate-800/40 dark:from-black/78 dark:via-slate-950/68 dark:to-slate-900/55' => $heroPhoto,
                'from-white/88 via-slate-50/90 to-slate-100/85 dark:from-slate-950/92 dark:via-slate-950/90 dark:to-slate-900/88' => ! $heroPhoto,
            ])></div>
            <div
                class="absolute inset-0 opacity-[0.35] dark:opacity-[0.25]"
                style="background-image: linear-gradient(to bottom,
                    color-mix(in srgb, var(--portal-primary, #60a5fa) 9%, transparent) 0%,
                    transparent 35%),
                    linear-gradient(to top,
                    color-mix(in srgb, var(--portal-secondary, #1e40af) 11%, transparent) 0%,
                    transparent 40%);"></div>
            <div class="absolute -left-[22%] top-[-8%] h-[min(420px,88vw)] w-[min(420px,92vw)] rounded-full bg-[radial-gradient(circle_at_center,var(--portal-primary)_0%,transparent_70%)] opacity-20 blur-[100px] dark:opacity-[0.09] lg:opacity-25 lg:dark:opacity-12"></div>
            <div class="absolute bottom-[-12%] left-[35%] h-[min(280px,60vw)] w-[min(280px,62vw)] rounded-full bg-[radial-gradient(circle_at_center,var(--portal-tertiary)_0%,transparent_70%)] opacity-18 blur-[90px] dark:opacity-[0.07]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(90deg,transparent_0%,rgba(15,23,42,0.02)_48%,transparent_100%)] bg-[length:64px_100%] dark:bg-[linear-gradient(90deg,transparent_0%,rgba(255,255,255,0.02)_48%,transparent_100%)]"></div>
        </div>

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center gap-2">
                <span @class([
                    'rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-widest shadow-sm backdrop-blur',
                    'border border-white/25 bg-white/15 text-white/95' => $heroPhoto,
                    'border border-slate-200/80 bg-white/80 text-slate-600 dark:border-slate-700 dark:bg-slate-900/60 dark:text-slate-300' => ! $heroPhoto,
                ])>Portal oficial</span>
                <span class="rounded-full px-3 py-1 text-[11px] font-bold uppercase tracking-widest text-white shadow-md" style="background:linear-gradient(135deg,var(--portal-secondary),var(--portal-primary))">Escola Legislativa</span>
            </div>

            <div class="mt-10 lg:grid lg:grid-cols-12 lg:items-center lg:gap-12 xl:gap-16">
                <div class="lg:col-span-6">
                    <p @class([
                        'text-sm font-semibold uppercase tracking-[0.2em]',
                        'text-white/85' => $heroPhoto,
                        'text-slate-500 dark:text-slate-400' => ! $heroPhoto,
                    ])>{{ $heroEyebrow }}</p>
                    <h1 @class([
                        'mt-4 text-[2.35rem] font-black leading-[1.08] tracking-tight sm:text-5xl lg:text-[3.25rem]',
                        'text-white [text-shadow:0_0_0.5px_rgba(0,0,0,0.55),0_0_1px_rgba(0,0,0,0.4),0_0_3px_rgba(0,0,0,0.22),0_2px_26px_rgba(0,0,0,0.4)]' => $heroPhoto,
                        'text-slate-900 dark:text-white' => ! $heroPhoto,
                    ])>
                        Formação que fortalece o
                        <span @class([
                            'bg-clip-text text-blue-500',
                            '[filter:drop-shadow(0_0_1px_rgba(255,255,255,0.35))_drop-shadow(0_0_3px_rgba(0,0,0,0.12))]' => $heroPhoto,
                            'drop-shadow-sm' => ! $heroPhoto,
                        ]) >legislativo</span>
                        com rigor acadêmico.
                    </h1>
                    <p @class([
                        'mt-7 max-w-xl text-lg leading-relaxed',
                        'text-white [text-shadow:0_1px_14px_rgba(0,0,0,0.35)]' => $heroPhoto,
                        'text-slate-600 dark:text-slate-300' => ! $heroPhoto,
                    ])>
                        Cursos, eventos e conteúdos que integram tecnologia pedagógica, transparência e proximidade com a comunidade parlamentar municipal.
                    </p>

                    <div class="mt-10 flex flex-wrap gap-4">
                        <a href="{{ route('portal.cursos.index') }}"
                            class="inline-flex items-center justify-center rounded-full px-8 py-3.5 text-sm font-bold text-white shadow-xl shadow-slate-900/15 ring-4 ring-transparent transition hover:-translate-y-0.5 hover:shadow-2xl"
                           style="background:linear-gradient(135deg, var(--portal-primary), var(--portal-secondary));--tw-ring-color:color-mix(in srgb,var(--portal-primary) 35%,transparent)">
                            Turmas e ofertas
                        </a>
                        <a href="{{ route('portal.contato') }}" @class([
                            'inline-flex items-center justify-center rounded-full px-7 py-3.5 text-sm font-bold backdrop-blur transition',
                            'border border-white/35 bg-white/15 text-white hover:bg-white/25' => $heroPhoto,
                            'border border-slate-300/90 bg-white/70 text-slate-800 hover:bg-white dark:border-slate-600 dark:bg-slate-900/50 dark:text-white dark:hover:bg-slate-900' => ! $heroPhoto,
                        ])>
                            Fale conosco
                        </a>
                    </div>

                    <dl @class([
                        'mt-14 grid max-w-xl grid-cols-3 gap-6 border-t pt-10',
                        'border-white/20' => $heroPhoto,
                        'border-slate-200/80 dark:border-slate-800' => ! $heroPhoto,
                    ])>
                        <div class="relative pl-4">
                            <span class="absolute left-0 top-1 h-10 w-1 rounded-full" style="background: linear-gradient(to bottom, var(--portal-primary), var(--portal-tertiary))"></span>
                            <dt @class([
                                'text-[11px] font-bold uppercase tracking-wider',
                                'text-white drop-shadow-[0_1px_8px_rgba(0,0,0,0.4)]' => $heroPhoto,
                            ]) @if (! $heroPhoto) style="color:var(--portal-primary)" @endif>Transparência</dt>
                            <dd @class([
                                'mt-1 text-sm font-medium',
                                'text-white/88 [text-shadow:0_1px_10px_rgba(0,0,0,0.35)]' => $heroPhoto,
                                'text-slate-800 dark:text-slate-200' => ! $heroPhoto,
                            ])>Comunicação clara e aberta</dd>
                        </div>
                        <div class="relative pl-4">
                            <span class="absolute left-0 top-1 h-10 w-1 rounded-full" style="background: linear-gradient(to bottom, var(--portal-secondary), var(--portal-primary))"></span>
                            <dt @class([
                                'text-[11px] font-bold uppercase tracking-wider',
                                'text-white drop-shadow-[0_1px_8px_rgba(0,0,0,0.4)]' => $heroPhoto,
                            ]) @if (! $heroPhoto) style="color:var(--portal-secondary)" @endif>Digital</dt>
                            <dd @class([
                                'mt-1 text-sm font-medium',
                                'text-white/88 [text-shadow:0_1px_10px_rgba(0,0,0,0.35)]' => $heroPhoto,
                                'text-slate-800 dark:text-slate-200' => ! $heroPhoto,
                            ])>Área do aluno integrada</dd>
                        </div>
                        <div class="relative pl-4">
                            <span class="absolute left-0 top-1 h-10 w-1 rounded-full" style="background: linear-gradient(to bottom, var(--portal-tertiary), var(--portal-secondary))"></span>
                            <dt @class([
                                'text-[11px] font-bold uppercase tracking-wider',
                                'text-white drop-shadow-[0_1px_8px_rgba(0,0,0,0.4)]' => $heroPhoto,
                            ]) @if (! $heroPhoto) style="color:var(--portal-tertiary)" @endif>Excelência</dt>
                            <dd @class([
                                'mt-1 text-sm font-medium',
                                'text-white/88 [text-shadow:0_1px_10px_rgba(0,0,0,0.35)]' => $heroPhoto,
                                'text-slate-800 dark:text-slate-200' => ! $heroPhoto,
                            ])>Corpo docente credenciado</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative mx-auto mt-16 w-full max-w-lg lg:col-span-6 lg:mx-0 lg:mt-0 lg:max-w-none">
                    {{-- Brasão isolado — sem estar «colado» ao bloco escuro / CTA --}}
                    <figure class="relative overflow-hidden rounded-[1.85rem] border border-slate-200/85 bg-white/95 p-8 shadow-xl ring-1 shadow-slate-900/10 ring-slate-200/70 backdrop-blur-md dark:border-slate-700/80 dark:bg-slate-950/70 dark:ring-white/10">
                        <figcaption class="sr-only">{{ $portalAdminSettings?->nome_camara ?? $heroEyebrow }}</figcaption>
                        <div class="aspect-[4/3] overflow-hidden rounded-2xl bg-gradient-to-br from-slate-100 via-white to-slate-50 ring-1 ring-slate-200/60 dark:from-slate-800 dark:via-slate-900 dark:to-slate-950 dark:ring-slate-700/80">
                            @if(!empty($portalAdminSettings?->logo_prefeitura_path))
                                <img src="{{ asset('storage/'.$portalAdminSettings->logo_prefeitura_path) }}" alt=""
                                     class="h-full w-full object-contain p-8 sm:p-10" loading="lazy"/>
                            @else
                                <div class="flex h-full w-full items-center justify-center p-12">
                                    <span class="text-6xl font-black tracking-tighter text-slate-300 dark:text-slate-700">{{ $pt?->portalBrandInitials() }}</span>
                                </div>
                            @endif
                        </div>
                    </figure>

                    {{-- Blocos separados por baixo: mensagem institucional + acesso ao aluno --}}
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200/75 bg-white/90 p-5 shadow-lg shadow-slate-900/5 dark:border-white/10 dark:bg-slate-900/65">
                            <div class="mb-2 flex items-center gap-2">
                                <span class="flex h-6 w-6 items-center justify-center rounded-full text-white shadow-sm" style="background:var(--portal-primary)">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21"/></svg>
                                </span>
                                <p class="text-[11px] font-bold uppercase tracking-widest" style="color:var(--portal-primary)">Formação institucional</p>
                            </div>
                            <p class="text-sm font-semibold leading-snug text-slate-700 dark:text-slate-100">Conteúdo alinhado à realidade parlamentar municipal.</p>
                        </div>
                        <div class="flex flex-col justify-between rounded-2xl border border-slate-200/85 bg-white p-5 shadow-lg dark:border-slate-700 dark:bg-slate-950/80">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Acesso restrito</p>
                            </div>
                            <a href="{{ route('portal.acesso.login') }}"
                               class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl px-4 py-3 text-xs font-bold text-white shadow-inner transition hover:opacity-90 active:scale-[0.98]"
                               style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                                Área do aluno
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Métricas — faixa escura institucional (prioriza tinta do tema) --}}
    <section class="relative isolate overflow-hidden border-y border-black/25 py-16 text-white animate__animated animate__backInLeft"
             style="background:linear-gradient(145deg,color-mix(in srgb,var(--portal-secondary,#0f2942) 88%,black),rgb(10,22,43) 55%,rgb(8,17,38))">
        <div aria-hidden="true" class="pointer-events-none absolute inset-0 opacity-40 dark:opacity-50">
            <div class="absolute inset-0" style="background:radial-gradient(ellipse 80% 50% at 15% -20%,color-mix(in srgb,var(--portal-primary,#2563eb) 45%,transparent),transparent);"></div>
            <div class="absolute inset-0 bg-[linear-gradient(90deg,transparent,rgba(255,255,255,.04)_52%,transparent)] bg-[length:56px_100%] opacity-75"></div>
        </div>
        <div class="relative mx-auto grid max-w-7xl grid-cols-2 gap-10 px-4 sm:gap-14 sm:px-6 lg:grid-cols-4 lg:px-8"
             x-data="{ showStats: false }"
             x-init="setTimeout(() => showStats = true, 150)">
            @foreach ([
                ['label' => 'Alunos', 'v' => $stats['alunos']],
                ['label' => 'Cursos ativos', 'v' => $stats['cursos']],
                ['label' => 'Turmas em gestão', 'v' => $stats['turmas_ativas']],
                ['label' => 'Eventos futuros', 'v' => $stats['eventos_futuros']],
            ] as $item)
                <div class="text-center transition duration-700 ease-out"
                     :class="showStats ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-4'">
                    <p class="text-4xl font-black tabular-nums tracking-tight text-white sm:text-5xl"
                       style="text-shadow:0 2px 14px rgba(0,0,0,.35);">{{ number_format($item['v'], 0, ',', '.') }}</p>
                    <p class="mt-3 text-[13px] font-semibold uppercase tracking-[0.12em]"
                       style="color:color-mix(in srgb,var(--portal-primary,#7dd3fc) 92%,transparent)">{{ $item['label'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    @if($noticias->isNotEmpty())
        <section class="relative isolate overflow-hidden border-b border-slate-200/50 py-16 dark:border-slate-800 animate__animated animate__bounceIn">
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 dark:hidden"
                 style="background:linear-gradient(180deg,color-mix(in srgb,var(--portal-primary,#2563eb) 5%,#f8fafc) 0%,color-mix(in srgb,var(--portal-secondary,#1e40af) 4%,rgb(248,250,252)) 52%,rgb(243,246,249) 100%)"></div>
            <div aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 hidden dark:block"
                 style="background:linear-gradient(180deg,color-mix(in srgb,var(--portal-secondary,#172554) 28%,rgb(15,23,42)) 0%,rgb(2,6,23) 95%)"></div>
            <div class="relative mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                @php($destaque = $noticias->first())
                @php($demais = $noticias->slice(1, 5))
                <header class="relative mb-12 max-w-4xl">
                    <div class="mb-6 h-px max-w-xl rounded-full opacity-80"
                         style="background: linear-gradient(90deg, var(--portal-primary, #3b82f6), color-mix(in srgb, var(--portal-tertiary, #34d399) 80%, transparent), transparent);"></div>
                    <p class="inline-flex items-center gap-2.5 text-[11px] font-bold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                        <span class="h-2 w-2 shrink-0 rounded-full shadow-sm ring-2 ring-white dark:ring-slate-900" style="background: linear-gradient(135deg, var(--portal-primary), var(--portal-tertiary))"></span>
                        <span class="text-slate-700 dark:text-slate-200">Editorial</span>
                    </p>
                    <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                        <div>
                            <h2 class="text-3xl font-black tracking-tight text-slate-900 sm:text-4xl dark:text-white">Em destaque</h2>
                            <p class="mt-4 max-w-2xl border-l-2 pl-5 text-base leading-relaxed text-slate-600 sm:text-lg dark:text-slate-300"
                               style="border-color: color-mix(in srgb, var(--portal-primary, #3b82f6) 45%, transparent);">
                                Curadoria das matérias mais lidas e do que impacta o mandato — com contexto, datas e acesso à matéria completa.
                            </p>
                        </div>
                        <a href="{{ route('portal.noticias.index') }}" class="shrink-0 rounded-full border border-slate-200/90 bg-white/90 px-5 py-2.5 text-sm font-bold shadow-sm transition hover:border-slate-300 hover:shadow dark:border-slate-600 dark:bg-slate-900/60 dark:hover:border-slate-500" style="color:var(--portal-primary)">Ver todas →</a>
                    </div>
                </header>
                <div class="mt-10 grid gap-8 lg:grid-cols-12">
                    <article class="group relative overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900/50 lg:col-span-7">
                        @if($destaque->foto_capa)
                            <div class="aspect-[16/9] bg-slate-100 dark:bg-slate-800">
                                <img src="{{ $destaque->foto_capa_url }}" alt="" class="h-full w-full object-cover transition duration-700 group-hover:scale-[1.03]" loading="lazy"/>
                            </div>
                        @else
                            <div class="flex aspect-[16/9] items-center justify-center bg-gradient-to-br from-slate-800 to-slate-950 dark:from-slate-900 dark:to-black">
                                <span class="text-5xl font-black text-white/20">{{ $pt?->portalBrandInitials() }}</span>
                            </div>
                        @endif
                        <div class="p-8 sm:p-10">
                            <time class="text-xs font-bold uppercase tracking-widest text-slate-500">{{ $destaque->publicar_em?->format('d/m/Y') }}</time>
                            <h3 class="mt-4 text-2xl font-extrabold leading-snug text-slate-900 sm:text-3xl dark:text-white">
                                <a class="hover:underline" href="{{ route('portal.noticias.show', ['slug' => $destaque->slug]) }}">{{ $destaque->titulo }}</a>
                            </h3>
                            @if($destaque->subtitulo)
                                <p class="mt-4 line-clamp-3 text-slate-600 dark:text-slate-300">{{ $destaque->subtitulo }}</p>
                            @endif
                            <a href="{{ route('portal.noticias.show', ['slug' => $destaque->slug]) }}" class="mt-8 inline-flex items-center gap-2 text-sm font-bold" style="color:var(--portal-primary)">
                                Ler matéria completa
                                <span aria-hidden="true">→</span>
                            </a>
                        </div>
                    </article>
                    <div class="flex flex-col gap-4 lg:col-span-5">
                        @foreach($demais as $noticia)
                            <a href="{{ route('portal.noticias.show', ['slug' => $noticia->slug]) }}"
                               class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-slate-600">
                                <time class="text-[10px] font-bold uppercase tracking-wider text-slate-500">{{ $noticia->publicar_em?->format('d/m/Y') }}</time>
                                <p class="mt-2 font-semibold leading-snug text-slate-900 group-hover:underline dark:text-white">{{ $noticia->titulo }}</p>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @else
        <x-portal.section title="Notícias" eyebrow="Comunicação" subtitle="Boletins, avisos e matérias que traduzem o mandato em linguagem clara — com transparência de fonte e atualização contínua." data-animate="fadeInLeft">
            <p class="text-slate-500">Nenhuma notícia publicada ainda.</p>
        </x-portal.section>
    @endif

    <x-portal.section
        id="eventos"
        eyebrow="Agenda institucional"
        title="Próximos encontros"
        subtitle="Sessões abertas à comunidade, reuniões técnicas e atividades da escola — com data, horário e local para você planejar a participação."
        class="animate__animated animate__bounceIn"
    >
        <div class="grid gap-5 md:grid-cols-2">
            @forelse($eventos as $evento)
                <article class="portal-animate-card group flex gap-5 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-sm transition hover:border-slate-300 hover:shadow-lg dark:border-slate-800 dark:bg-slate-900/45">
                    <div class="flex h-16 w-16 shrink-0 flex-col items-center justify-center rounded-2xl text-white shadow-inner"
                         style="background:linear-gradient(155deg,var(--portal-secondary),var(--portal-primary))">
                        <span class="text-xl font-black">{{ $evento->date_time?->format('d') }}</span>
                        <span class="text-[10px] font-semibold">{{ $evento->date_time?->format('m/Y') }}</span>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-semibold text-slate-500">{{ $evento->date_time?->format('d/m/Y H:i') }}</p>
                        <h3 class="mt-1 text-lg font-bold text-slate-900 dark:text-white">
                            <a class="hover:underline" href="{{ route('portal.eventos.show', ['evento' => $evento->id]) }}">{{ $evento->title }}</a>
                        </h3>
                        @if($evento->city)
                            <p class="mt-2 flex items-center gap-1 text-sm text-slate-500">
                                <svg class="h-4 w-4 shrink-0 opacity-60" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $evento->city }}{{ $evento->state ? ' — '.$evento->state : '' }}
                            </p>
                        @endif
                    </div>
                </article>
            @empty
                <p class="col-span-full text-slate-500">Nenhum evento programado.</p>
            @endforelse
        </div>
        <div class="mt-10 flex flex-wrap justify-center gap-3">
            <a href="{{ route('portal.agenda.index') }}" class="inline-flex rounded-full px-6 py-2.5 text-sm font-bold text-white shadow-md transition hover:opacity-95"
               style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">Calendário mensal</a>
            <a href="{{ route('portal.eventos.index') }}" class="inline-flex rounded-full border border-slate-300 px-6 py-2.5 text-sm font-bold hover:bg-white dark:border-slate-600 dark:hover:bg-slate-900">Lista de eventos</a>
        </div>
    </x-portal.section>

    <x-portal.section
        eyebrow="Oferta formativa"
        title="Turmas em destaque"
        subtitle="Inscrições abertas, turmas em andamento e atalhos para o histórico — organizado para assessores, gabinetes e servidores que precisam de previsibilidade."
        class="animate__animated animate__fadeInLeftBig"
    >
        <div class="grid gap-10 lg:grid-cols-2 ">
            <div>
                <h3 class="text-xs font-bold uppercase tracking-[0.2em]" style="color:var(--portal-primary)">Inscrições</h3>
                <div class="mt-5 space-y-4">
                    @forelse($turmasInscricao as $turma)
                        @include('portal.partials.course-class-card', ['turma' => $turma, 'tone' => 'primary'])
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">Nenhuma turma com inscrição no momento.</p>
                    @endforelse
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold uppercase tracking-[0.2em]" style="color:var(--portal-tertiary)">Em andamento</h3>
                <div class="mt-5 space-y-4">
                    @forelse($turmasAndamento as $turma)
                        @include('portal.partials.course-class-card', ['turma' => $turma, 'tone' => 'tertiary'])
                    @empty
                        <p class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-sm text-slate-500 dark:border-slate-700 dark:bg-slate-900/40">Nenhuma turma em execução listada.</p>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="mt-12 flex flex-wrap justify-center gap-4">
            <a href="{{ route('portal.cursos.index') }}" class="rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">Ver todas as turmas</a>
            <a href="{{ route('portal.cursos.historico') }}" class="rounded-full border border-slate-300 px-8 py-3 text-sm font-bold dark:border-slate-600">Histórico de turmas</a>
            <a href="{{ route('portal.acesso.register') }}" class="rounded-full border border-slate-800/10 bg-slate-900 px-8 py-3 text-sm font-bold text-white dark:border-white/10">Primeiro cadastro</a>
        </div>
    </x-portal.section>

    <section class="relative overflow-hidden py-16 sm:py-20 animate__animated animate__fadeInUp">
        <div class="absolute inset-0 -z-10" style="background:linear-gradient(135deg,var(--portal-secondary),color-mix(in srgb,var(--portal-primary) 65%,black))"></div>
        <div class="absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top,_rgba(255,255,255,.12)_0%,transparent_58%)]"></div>
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <p class="inline-flex items-center gap-2 text-[11px] font-bold uppercase tracking-[0.28em] text-white/75">
                <span class="h-2 w-2 rounded-full bg-white/90 shadow-sm ring-2 ring-white/30"></span>
                Próximo passo
            </p>
            <h2 class="mt-4 text-3xl font-black tracking-tight text-white drop-shadow-sm sm:text-4xl lg:text-[2.5rem]">Pronto para iniciar sua jornada na escola?</h2>
            <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-white/88">Consulte turmas com vaga, acompanhe notícias e centralize materiais institucionais — sem perder o fio da formação no legislativo.</p>
            <div class="mt-10 flex flex-wrap justify-center gap-4">
                <a href="{{ route('portal.cursos.index') }}" class="inline-flex rounded-full bg-white px-8 py-3.5 text-sm font-bold text-slate-900 shadow-xl transition hover:bg-slate-100">Explorar turmas</a>
                <a href="{{ route('portal.sobre') }}" class="inline-flex rounded-full border-2 border-white/75 px-8 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Conheça a escola</a>
            </div>
        </div>
    </section>

    <x-portal.section
        id="sobre"
        eyebrow="A escola"
        title="Institucional — mandato"
        :subtitle="$sobreSubtitle"
     
         class="animate__animated animate__fadeInLeftBig"
    >
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
            <div class="relative rounded-3xl border border-slate-200 bg-slate-50 p-10 dark:border-slate-800 dark:bg-slate-900/50">
                <div class="absolute -left-px top-10 h-24 w-1 rounded-full" style="background:linear-gradient(180deg,var(--portal-primary),var(--portal-tertiary))"></div>
                <p class="text-lg leading-relaxed text-slate-700 dark:text-slate-300">
                    {{ $sobreSnippet ?? 'Conheça a missão da nossa Escola Legislativa e como promovemos a formação continuada voltada ao exercício democrático.' }}
                </p>
                <a href="{{ route('portal.sobre') }}" class="mt-8 inline-flex items-center gap-2 text-sm font-bold" style="color:var(--portal-primary)">Perfil institucional completo <span aria-hidden="true">→</span></a>
            </div>
            <ol class="space-y-8 border-l border-slate-200 pl-10 dark:border-slate-700">
                @foreach([
                    ['title' => 'Planejamento acadêmico', 'body' => 'Currículos e turmas estruturados com governança e previsibilidade.'],
                    ['title' => 'Modalidades ágeis', 'body' => 'Infraestrutura digital para aproximar quem está na câmara e na comunidade.'],
                    ['title' => 'Credenciamento docente', 'body' => 'Processos documentados para corpo técnico e parceiros externos.'],
                ] as $step)
                    <li class="relative">
                        <span class="absolute -left-[46px] top-2 flex h-[18px] w-[18px] items-center justify-center rounded-full shadow ring-4 ring-white dark:ring-slate-950"
                              style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-tertiary))"></span>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</p>
                        <p class="mt-2 text-sm text-slate-600 dark:text-slate-400">{{ $step['body'] }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </x-portal.section>

    <x-portal.section
        id="docentes"
        eyebrow="Quem ensina"
        title="Corpo docente — credenciamento"
        subtitle="Professores, mesas técnicas e parceiros acadêmicos alinhados às demandas do processo legislativo e às resoluções em vigor."
       class="animate__animated animate__fadeInBottomLeft"
    >
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($professoresDestaque as $professor)
                <div class="group rounded-2xl border border-slate-200 bg-white p-6 text-center shadow-sm transition hover:-translate-y-1 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40">
                    <div class="mx-auto mb-4 h-24 w-24 shrink-0 rounded-full p-[3px] shadow-md transition duration-300 group-hover:shadow-lg" style="background: linear-gradient(135deg, var(--portal-primary), var(--portal-tertiary))">
                        <div class="h-full w-full overflow-hidden rounded-full bg-white dark:bg-slate-900">
                        @if(!empty($professor->photo_path))
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($professor->photo_path) }}" alt="" class="h-full w-full object-cover" loading="lazy"/>
                        @else
                            <div class="flex h-full items-center justify-center text-2xl font-bold text-slate-400">{{ mb_strtoupper(mb_substr($professor->full_name, 0, 1)) }}</div>
                        @endif
                        </div>
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $professor->full_name }}</h3>
                    @if($professor->specialities)
                        <p class="mt-2 text-xs text-slate-500">{{ $professor->specialities }}</p>
                    @endif
                </div>
            @empty
                <p class="col-span-full text-center text-slate-500">Corpo docente em atualização.</p>
            @endforelse
        </div>
        <div class="mt-10 text-center">
            <a href="{{ route('portal.professores.index') }}" class="text-sm font-bold text-slate-700 underline decoration-slate-300 underline-offset-4 hover:text-slate-900 dark:text-slate-200">Credenciamentos e professores</a>
        </div>
    </x-portal.section>

    <section class="pb-20 pt-8 animate__animated animate__fadeInLeftBig">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200/90 bg-white px-8 py-12 text-center shadow-lg dark:border-slate-800 dark:bg-slate-900/55 sm:px-14">
                <p class="inline-flex items-center justify-center gap-2 text-[11px] font-bold uppercase tracking-[0.28em] text-slate-500 dark:text-slate-400">
                    <span class="h-2 w-2 rounded-full shadow-sm ring-2 ring-slate-100 dark:ring-slate-800" style="background: linear-gradient(135deg, var(--portal-primary), var(--portal-tertiary))"></span>
                    Rede & colaboração
                </p>
                <h2 class="mt-4 text-2xl font-black tracking-tight text-slate-900 sm:text-3xl dark:text-white">Parcerias institucionais</h2>
                <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-slate-600 dark:text-slate-400">Destaque para entidades e programas alinhados ao mandato democrático-coletivo. Proponha projetos de formação, pesquisa aplicada ou eventos conjuntos — a equipe retorna pelos canais oficiais.</p>
                <a href="{{ route('portal.contato') }}" class="mt-8 inline-flex rounded-full border border-slate-300 px-6 py-2.5 text-sm font-bold transition hover:bg-slate-50 dark:border-slate-600 dark:hover:bg-slate-800">Proposta de parceria</a>
            </div>
        </div>
    </section>
@endsection
