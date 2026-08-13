@extends('layouts.public')

@section('title', config('app.name').' — Escola Legislativa no padrão que o cidadão espera')

@section('content')
    <x-public.site-nav product-id="produto"/>

    {{-- Hero estilo gov-tech claro (referência visual: 1Doc) --}}
    <section id="produto" class="bg-card-header pub-soft-grid relative scroll-mt-20 overflow-hidden border-b border-sky-200/60 ">
        <div class="relative mx-auto max-w-6xl px-4 pb-16 pt-12 sm:px-6 sm:pb-20 sm:pt-16 lg:px-8 lg:pb-24 lg:pt-20 ">
            <div class="grid items-center gap-12 lg:grid-cols-12 lg:gap-14">
                <div class="lg:col-span-6">
                    <p class="pub-animate pub-d1 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[0.18em] text-sky-700">
                        <span class="h-1 w-8 rounded-full bg-emerald-400" aria-hidden="true"></span>
                        Plataforma · multi-tenant · Brasil
                    </p>
                    <h1 class="pub-animate pub-d2 font-display mt-5 text-4xl font-semibold leading-[1.1] tracking-tight text-white sm:text-5xl lg:text-[3.05rem]">
                        Educação legislativa com presença digital séria — por município, sem ruído.
                    </h1>
                    <p class="pub-animate pub-d3 mt-6 max-w-xl text-lg leading-relaxed text-gray-300">
                        O {{ config('app.name') }} organiza o que o público vê fora (portal) e o que a escola opera por dentro (aluno, docente, administração, certificação e privacidade), com isolamento real entre clientes.
                    </p>
                    <div class="pub-animate pub-d4 mt-10 flex flex-wrap items-center gap-3">
                        <a href="#modulos"
                           class="inline-flex items-center justify-center rounded-full bg-sky-600 px-7 py-3.5 text-sm font-semibold text-white shadow-md shadow-sky-600/25 transition hover:bg-sky-700">
                            Ver módulos
                        </a>
                        <a href="{{ route('tenant.login') }}"
                           class="inline-flex items-center justify-center rounded-full border-2 border-sky-600 bg-white px-7 py-3.5 text-sm font-semibold text-sky-800 transition hover:bg-sky-50">
                            Já sou cliente — entrar
                        </a>
                    </div>
                    <dl class="pub-animate pub-d5 mt-12 grid max-w-lg grid-cols-2 gap-6 border-t border-sky-200/80 pt-10 sm:grid-cols-3">
                        <div class="rounded-xl bg-white/80 p-4 shadow-sm ring-1 ring-sky-100">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Portal</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">Institucional completo</dd>
                        </div>
                        <div class="rounded-xl bg-white/80 p-4 shadow-sm ring-1 ring-sky-100">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-sky-600">Dados</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">Por tenant (subdomínio)</dd>
                        </div>
                        <div class="col-span-2 rounded-xl bg-white/80 p-4 shadow-sm ring-1 ring-emerald-100 sm:col-span-1">
                            <dt class="text-[11px] font-bold uppercase tracking-wider text-emerald-700">LGPD</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">Termo central + aceites</dd>
                        </div>
                    </dl>
                </div>

                <div class="relative lg:col-span-6">
                    <div class="rounded-3xl border border-sky-200 bg-white p-3 shadow-xl shadow-sky-200/50 ring-1 ring-slate-100">
                        <img src="{{ asset('img/marketing/img.png') }}"
                             width="800"
                             height="640"
                             alt=""
                             class="w-full rounded-2xl"
                             role="presentation"/>
                    </div>
                 
                </div>
            </div>
        </div>
    </section>

    <section id="jornada" class="scroll-mt-20 border-b border-sky-100 bg-white py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="max-w-2xl">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Da divulgação à certificação, sem atalhos soltos.</h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">Cada etapa abaixo existe no produto como fluxo ou tela — não é slide de marketing vazio.</p>
            </div>
            <figure class="mt-10 overflow-hidden rounded-2xl border border-sky-100 shadow-md">
                <img src="{{ asset('img/marketing/banner-civic.svg') }}" width="1200" height="400" alt="" class="h-40 w-full object-cover object-center sm:h-48" role="presentation"/>
            </figure>
            <ol class="mt-12 grid gap-8 sm:grid-cols-2 lg:grid-cols-4 lg:gap-0 lg:divide-x lg:divide-sky-100">
                @foreach ([
                    ['01', 'Cidadão', 'Encontra notícias, cursos, eventos e canais oficiais no portal do município.'],
                    ['02', 'Participação', 'Cadastro e área do aluno com trilha clara de turmas, materiais e avaliações.'],
                    ['03', 'Operação', 'Docentes e gestão pedagógica no painel certo, com contexto do tenant.'],
                    ['04', 'Prova', 'Certificado emitido e consulta pública por código — confiança auditável.'],
                ] as [$n, $t, $d])
                    <li class="relative flex flex-col lg:px-8 lg:first:pl-0">
                        <span class="text-3xl font-bold tabular-nums text-sky-600">{{ $n }}</span>
                        <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $t }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    <section id="modulos" class="scroll-mt-20 border-y border-sky-100 bg-sky-50/80 py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-sky-700">Módulos</p>
                <h2 class="font-display mt-3 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">O que o sistema cobre, de ponta a ponta</h2>
            </div>

            <div class="mt-16 space-y-20 sm:space-y-24">
                @php
                    $blocks = [
                        ['title' => 'Portal público', 'body' => 'Home, notícias, agenda, eventos, cursos, corpo docente, institucional e contato — com a identidade visual do tenant e navegação pensada para mobile.', 'tag' => 'Presença'],
                        ['title' => 'Aluno e docente', 'body' => 'Fluxos separados para quem estuda e quem ministra: login, turmas, aulas, comunicação e ferramentas pedagógicas sem misturar permissões.', 'tag' => 'Operação'],
                        ['title' => 'Administração e certificação', 'body' => 'Gestão de conteúdos e usuários, registro de mensagens de contato com resposta por e-mail, emissão e validação pública de certificados.', 'tag' => 'Governança'],
                        ['title' => 'Privacidade e segurança', 'body' => 'Termo global editável na central, aceite no cadastro e após novas versões, aviso de cookies alinhado à LGPD e Turnstile nos fluxos sensíveis.', 'tag' => 'Conformidade'],
                    ];
                @endphp
                @foreach ($blocks as $i => $b)
                    <div class="grid items-center gap-10 lg:grid-cols-2 lg:gap-16">
                        <div class="@if($i % 2 === 1) lg:order-2 @endif">
                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-[11px] font-bold uppercase tracking-wider text-emerald-800">{{ $b['tag'] }}</span>
                            <h3 class="font-display mt-4 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">{{ $b['title'] }}</h3>
                            <p class="mt-4 text-base leading-relaxed text-slate-600">{{ $b['body'] }}</p>
                        </div>
                        <div class="@if($i % 2 === 1) lg:order-1 @endif">
                            <div class="overflow-hidden rounded-2xl border border-sky-200 bg-white p-8 shadow-lg">
                                <div class="space-y-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-sky-600 text-xs font-bold text-white">{{ $i + 1 }}</div>
                                        <div class="h-2 flex-1 rounded-full bg-sky-100"></div>
                                    </div>
                                    <div class="space-y-2 pl-1">
                                        <div class="h-2 w-[92%] max-w-full rounded-full bg-sky-100"></div>
                                        <div class="h-2 w-[76%] max-w-full rounded-full bg-slate-100"></div>
                                        <div class="h-2 w-[58%] max-w-full rounded-full bg-slate-100"></div>
                                    </div>
                                    <div class="rounded-xl border border-dashed border-sky-200 bg-sky-50 p-4 text-xs leading-relaxed text-slate-700">
                                        Pré-visualização ilustrativa da área de {{ strtolower($b['title']) }} — substitua por capturas reais quando for divulgar ao mercado.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section id="beneficios" class="scroll-mt-20 border-y border-sky-200 bg-gradient-to-b from-sky-100 to-sky-50 py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Por que prefeituras e Câmaras trocam planilha por plataforma</h2>
                <p class="mt-4 text-base leading-relaxed text-slate-600">Menos retrabalho, mais rastreio e uma vitrine que o cidadão entende na primeira visita.</p>
            </div>
            <ul class="mt-12 grid gap-6 sm:grid-cols-3">
                @foreach ([
                    ['Menos ruído operacional', 'Um endereço oficial por município. O cidadão sabe onde ler e onde falar com a escola.'],
                    ['Responsabilidade compartilhada', 'Contatos registrados, respostas por canal institucional e histórico para auditoria interna.'],
                    ['Confiança no papel', 'Certificado consultável no mesmo ecossistema que divulga o curso — coerência que importa em comissões e tribunais de contas.'],
                ] as [$ht, $bd])
                    <li class="rounded-2xl border border-sky-100 bg-white p-6 text-left shadow-md transition hover:shadow-lg">
                        <h3 class="text-base font-semibold text-slate-900">{{ $ht }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $bd }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    <section id="planos" class="scroll-mt-20 bg-white py-16 sm:py-24">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-sky-700">Comercial</p>
                <h2 class="font-display mt-3 text-3xl font-semibold tracking-tight text-slate-900 sm:text-4xl">Proposta sob medida para o porte da instituição</h2>
                <p class="mt-4 text-slate-600">Sem preço fictício na página: o orçamento amarra escopo (número de módulos ativos, volume de usuários, SLA e integrações).</p>
            </div>
            <div class="mt-14 grid gap-6 lg:grid-cols-2">
                <div class="flex flex-col rounded-2xl border border-sky-200 bg-sky-50/50 p-8 lg:p-10">
                    <h3 class="font-display text-xl font-semibold text-slate-900">Município único</h3>
                    <p class="mt-2 text-sm text-slate-600">Implantação enxuta, marca da Câmara, equipe treinada e go-live com checklist de conteúdo.</p>
                    <ul class="mt-8 flex-1 space-y-3 text-sm text-slate-700">
                        <li class="flex gap-2"><span class="mt-0.5 text-emerald-500" aria-hidden="true">●</span> Portal + áreas logadas conforme contrato</li>
                        <li class="flex gap-2"><span class="mt-0.5 text-emerald-500" aria-hidden="true">●</span> Suporte em horário comercial acordado</li>
                        <li class="flex gap-2"><span class="mt-0.5 text-emerald-500" aria-hidden="true">●</span> Atualizações da plataforma incluídas</li>
                    </ul>
                    <a href="{{ route('central.login') }}" class="mt-10 inline-flex w-full items-center justify-center rounded-full border border-sky-300 bg-white py-3.5 text-sm font-semibold text-sky-900 transition hover:bg-sky-50">Pedir proposta</a>
                </div>
                <div class="relative flex flex-col overflow-hidden rounded-2xl border-2 border-sky-500 bg-gradient-to-br from-emerald-50 via-white to-sky-50 p-8 shadow-lg lg:p-10">
                    <h3 class="font-display text-xl font-semibold text-slate-900">Rede, consórcio ou estado</h3>
                    <p class="mt-2 text-sm text-slate-700">Multi-tenant com governança central, onboarding em lote e relatórios para quem financia o programa.</p>
                    <ul class="mt-8 flex-1 space-y-3 text-sm text-slate-800">
                        <li class="flex gap-2"><span class="mt-0.5 font-bold text-sky-700" aria-hidden="true">✓</span> Painel central para vários municípios</li>
                        <li class="flex gap-2"><span class="mt-0.5 font-bold text-sky-700" aria-hidden="true">✓</span> Padronização de termos LGPD e políticas</li>
                        <li class="flex gap-2"><span class="mt-0.5 font-bold text-sky-700" aria-hidden="true">✓</span> Roadmap e integrações avaliadas em projeto</li>
                    </ul>
                    <a href="{{ route('central.login') }}" class="mt-10 inline-flex w-full items-center justify-center rounded-full bg-sky-600 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-sky-700">Falar com quem implementa</a>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="scroll-mt-20 border-t border-sky-100 bg-slate-50 py-16 sm:py-20">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-12 lg:grid-cols-12 lg:gap-10">
                <div class="lg:col-span-4">
                    <h2 class="font-display text-3xl font-semibold tracking-tight text-slate-900">Perguntas que costumam fechar reunião</h2>
                    <p class="mt-4 text-sm leading-relaxed text-slate-600">Respostas objetivas. Se precisar de anexo técnico (arquitetura, RPO, DPIA), isso entra na proposta — não numa landing genérica.</p>
                </div>
                <div class="space-y-3 lg:col-span-8">
                    @foreach ([
                        ['Cada Câmara tem o seu endereço?', 'Sim. O acesso público típico é por subdomínio dedicado ao município, com dados isolados dos demais clientes.'],
                        ['Certificado sem login?', 'A consulta pública por código é pensada para o cidadão verificar autenticidade sem criar conta.'],
                        ['LGPD é “caixa de texto” ou processo?', 'Há termo global versionado na central, aceite em cadastro e fluxo de re-aceite quando você publicar nova versão, além de aviso de cookies e proteção anti-bot configurável.'],
                    ] as [$q, $a])
                        <details class="group rounded-xl border border-sky-200 bg-white open:shadow-md">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left text-sm font-semibold text-slate-900 outline-none focus-visible:ring-2 focus-visible:ring-sky-500 [&::-webkit-details-marker]:hidden">
                                {{ $q }}
                                <svg class="h-4 w-4 shrink-0 text-sky-500 transition group-open:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/></svg>
                            </summary>
                            <p class="border-t border-sky-100 px-5 pb-4 pt-3 text-sm leading-relaxed text-slate-700">{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-sky-100 bg-white py-14">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col items-start justify-between gap-8 rounded-2xl border border-sky-200 bg-gradient-to-r from-sky-50 to-emerald-50 p-8 sm:flex-row sm:items-center sm:p-10">
                <div class="flex min-w-0 flex-1 flex-col gap-4 sm:flex-row sm:items-center">
                    <img src="{{ asset('img/marketing/hero-illustration.svg') }}" width="160" height="128" alt="" class="hidden h-24 w-auto shrink-0 rounded-lg border border-sky-200 bg-white object-cover object-top shadow-sm sm:block" role="presentation"/>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-sky-700">Próximo passo</p>
                        <p class="font-display mt-2 text-2xl font-semibold tracking-tight text-slate-900">Mostre o produto para quem decide orçamento.</p>
                        <p class="mt-2 max-w-xl text-sm text-slate-600">Use esta página como gancho; a demonstração é onde o {{ config('app.name') }} deixa de ser promessa e vira tela compartilhada.</p>
                    </div>
                </div>
                <div class="flex w-full shrink-0 flex-col gap-3 sm:w-auto sm:flex-row">
                    <a href="#modulos" class="inline-flex items-center justify-center rounded-full border border-sky-300 bg-white px-6 py-3 text-sm font-semibold text-sky-900 transition hover:bg-sky-50">Rever módulos</a>
                    <a href="{{ route('central.login') }}" class="inline-flex items-center justify-center rounded-full bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">Área central</a>
                </div>
            </div>
        </div>
    </section>

    <footer class="border-t border-sky-900 bg-slate-900 py-14 text-slate-300">
        <div class="mx-auto grid max-w-6xl gap-10 px-4 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            <div class="sm:col-span-2 lg:col-span-2">
                @if(file_exists(public_path('img/logo.png')))
                    <div class="inline-flex rounded-lg bg-white/10 p-2 ring-1 ring-white/20">
                        <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto max-w-[200px] object-contain object-left" width="160" height="40"/>
                    </div>
                @else
                    <p class="font-display text-lg font-semibold text-white">{{ config('app.name') }}</p>
                @endif
                <p class="mt-4 max-w-sm text-sm leading-relaxed text-slate-400">Plataforma para Escolas Legislativas — portal, gestão acadêmica, certificação e privacidade com seriedade de software público.</p>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-sky-400">Navegação</p>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="#produto" class="text-slate-300 hover:text-white">Início / produto</a></li>
                    <li><a href="#jornada" class="text-slate-300 hover:text-white">Jornada</a></li>
                    <li><a href="#modulos" class="text-slate-300 hover:text-white">Módulos</a></li>
                    <li><a href="#faq" class="text-slate-300 hover:text-white">FAQ</a></li>
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-sky-400">Acesso</p>
                <ul class="mt-4 space-y-2 text-sm">
                    <li><a href="{{ route('tenant.login') }}" class="text-slate-300 hover:text-white">Cliente — entrar</a></li>
                    <li><a href="{{ route('central.login') }}" class="text-slate-300 hover:text-white">Central</a></li>
                </ul>
                <p class="mt-6 text-xs text-slate-500">© {{ date('Y') }} {{ config('app.name') }}</p>
            </div>
        </div>
    </footer>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('public-nav-toggle');
    var panel = document.getElementById('public-nav-panel');
    if (!btn || !panel) return;
    btn.addEventListener('click', function () {
        var open = panel.classList.toggle('hidden') === false;
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    panel.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            panel.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        });
    });
});
</script>
@endpush
