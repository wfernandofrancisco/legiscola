@extends('layouts.portal')

@section('title', 'Tutorial')

@php
    $lbl = 'block text-[9px] font-bold uppercase tracking-[0.14em] text-slate-400 dark:text-slate-500';
    $box = 'mt-1 flex h-8 items-center rounded-lg border border-slate-200 bg-white px-2.5 text-[11px] font-medium text-slate-700 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200';
    $ring = 'ring-4 ring-amber-400/70';

    $field = fn (string $label, string $value = '', string $wrap = '') => '<div class="'.$wrap.'">'
        .'<span class="'.$lbl.'">'.e($label).'</span>'
        .'<span class="'.$box.'">'.e($value).'</span></div>';

    $pill = fn (string $text, bool $active = false) => '<span class="rounded-full px-2 py-1 text-[10px] font-semibold '
        .($active ? 'bg-slate-900 text-white dark:bg-white dark:text-slate-900' : 'text-slate-500 dark:text-slate-400').'">'.e($text).'</span>';

    $btn = fn (string $text, bool $highlight = false) => '<span class="inline-flex items-center justify-center rounded-full px-4 py-2 text-[11px] font-bold text-white shadow-md '
        .($highlight ? $ring : '').'" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">'.e($text).'</span>';

    $steps = [
        ['id' => 'primeiro-acesso', 'n' => '1', 'title' => 'Primeiro acesso', 'desc' => 'Criar o cadastro e confirmar o e-mail para liberar a área do aluno.'],
        ['id' => 'inscricao-turma', 'n' => '2', 'title' => 'Inscrição em curso', 'desc' => 'Encontrar uma turma com inscrições abertas e garantir sua vaga.'],
        ['id' => 'inscricao-evento', 'n' => '3', 'title' => 'Inscrição em evento', 'desc' => 'Reservar sua participação e registrar presença no dia.'],
    ];
@endphp

@section('content')
    <x-portal.page-hero
        eyebrow="Ajuda"
        title="Tutorial do portal"
        subtitle="Um passo a passo com as telas reais do portal: como fazer o primeiro acesso, confirmar o e-mail e se inscrever em cursos e eventos."
    >
        <x-slot name="actions">
            <a href="#primeiro-acesso" class="inline-flex rounded-full bg-white px-8 py-3.5 text-sm font-bold text-slate-900 shadow-xl transition hover:bg-slate-100">Começar pelo passo 1</a>
            <a href="{{ route('portal.acesso.register') }}" class="inline-flex rounded-full border-2 border-white/75 px-8 py-3.5 text-sm font-bold text-white transition hover:bg-white/10">Criar meu cadastro</a>
        </x-slot>
    </x-portal.page-hero>

    {{-- Sumário --}}
    <section class="no-portal-animate border-b border-slate-200/80 bg-gradient-to-b from-slate-50 to-white py-12 dark:border-slate-800 dark:from-slate-950 dark:to-slate-900">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <ol class="grid gap-5 sm:grid-cols-3">
                @foreach ($steps as $step)
                    <li>
                        <a href="#{{ $step['id'] }}"
                           class="portal-animate-card group flex h-full items-start gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg dark:border-slate-700 dark:bg-slate-900/60"
                           data-animate="fadeInUp">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-sm font-black text-white shadow-md"
                                  style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">{{ $step['n'] }}</span>
                            <span class="min-w-0">
                                <span class="block text-base font-bold text-slate-900 dark:text-white">{{ $step['title'] }}</span>
                                <span class="mt-1 block text-sm leading-relaxed text-slate-600 dark:text-slate-400">{{ $step['desc'] }}</span>
                                <span class="mt-3 inline-flex text-xs font-bold" style="color:var(--portal-primary)">Ver o passo a passo →</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- Passo 1 --}}
    <x-portal.section
        id="primeiro-acesso"
        eyebrow="Passo 1"
        title="Primeiro acesso: cadastro e confirmação de e-mail"
        subtitle="O cadastro é feito uma única vez. Depois de enviar o formulário, é obrigatório abrir o e-mail e clicar no link de confirmação — sem isso o acesso não é liberado."
    >
        <ol class="space-y-16">
            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 1.1</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Clique em “Área do aluno”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        No topo do portal, à direita, use o botão <strong class="text-slate-900 dark:text-white">Área do aluno</strong>.
                        No celular, abra o menu (☰) e escolha <strong class="text-slate-900 dark:text-white">Área do aluno — Entrar</strong>.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    url="/" caption="Cabeçalho do portal — o botão destacado leva à tela de acesso do aluno.">
                    <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-3 py-2.5 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex min-w-0 items-center gap-2">
                            <span class="h-7 w-7 shrink-0 rounded-lg" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))"></span>
                            <span class="truncate text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Escola Legislativa</span>
                        </div>
                        <div class="hidden items-center gap-0.5 sm:flex">
                            {!! $pill('Início', true) !!}{!! $pill('Notícias') !!}{!! $pill('Eventos') !!}{!! $pill('Turmas') !!}
                        </div>
                        {!! $btn('Área do aluno', true) !!}
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="lg:order-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 1.2</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Escolha “Criar cadastro”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        A tela de login abre primeiro. Como você ainda não tem conta, clique no link
                        <strong class="text-slate-900 dark:text-white">Criar cadastro</strong>, no rodapé do formulário.
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Já tem cadastro e esqueceu a senha? Use <strong class="text-slate-900 dark:text-white">Esqueci minha senha</strong> nesta mesma tela.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card lg:order-1" data-animate="fadeInLeft"
                    url="/acesso/entrar" caption="Tela de login: o link para criar o cadastro fica no final do card.">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Entrar na área do aluno</p>
                        <div class="mt-3 space-y-2.5">
                            {!! $field('E-mail', 'maria.silva@email.com') !!}
                            {!! $field('Senha', '••••••••') !!}
                        </div>
                        <div class="mt-4">{!! $btn('Entrar') !!}</div>
                        <div class="mt-4 border-t border-slate-100 pt-3 text-center dark:border-slate-800">
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Primeira vez aqui?</span>
                            <span class="ml-1 inline-flex rounded-md px-1.5 py-0.5 text-[11px] font-bold underline {{ $ring }}" style="color:var(--portal-primary)">Criar cadastro</span>
                        </div>
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 1.3</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Preencha os dados e envie</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        São poucos campos: nome completo, CPF, data de nascimento, sexo, cidade, e-mail e senha.
                        Use um <strong class="text-slate-900 dark:text-white">e-mail que você acessa de verdade</strong> — é para lá que vai o link de confirmação.
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>O CPF é usado para identificar o aluno e emitir certificados, então digite sem erros.</li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>A senha precisa ser repetida no campo de confirmação.</li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>Marque o aceite da política de privacidade para concluir.</li>
                    </ul>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    url="/acesso/cadastro" caption="Formulário de primeiro cadastro com o aceite da política de privacidade (LGPD).">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                        <p class="text-sm font-bold text-slate-900 dark:text-white">Primeiro cadastro</p>
                        <div class="mt-3 grid grid-cols-2 gap-2.5">
                            {!! $field('Nome completo', 'Maria Silva de Souza', 'col-span-2') !!}
                            {!! $field('CPF', '123.456.789-00') !!}
                            {!! $field('Data de nascimento', '12/04/1990') !!}
                            {!! $field('Sexo', 'Feminino') !!}
                            {!! $field('Cidade', 'Palmas') !!}
                            {!! $field('E-mail', 'maria.silva@email.com', 'col-span-2') !!}
                            {!! $field('Senha', '••••••••') !!}
                            {!! $field('Confirmar senha', '••••••••') !!}
                        </div>
                        <div class="mt-3 flex items-start gap-2 rounded-lg border border-slate-200 bg-slate-50 p-2.5 dark:border-slate-700 dark:bg-slate-950/60">
                            <span class="mt-0.5 flex h-3.5 w-3.5 shrink-0 items-center justify-center rounded border border-slate-400 text-[8px] font-black text-white" style="background:var(--portal-primary)">✓</span>
                            <span class="text-[10px] leading-relaxed text-slate-600 dark:text-slate-300">Declaro que li e aceito a política de privacidade e o tratamento dos meus dados pessoais.</span>
                        </div>
                        <div class="mt-3">{!! $btn('Enviar cadastro', true) !!}</div>
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="lg:order-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 1.4</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Vá até seu e-mail e confirme</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Este passo é obrigatório. Abra a caixa de entrada do e-mail que você cadastrou e clique no botão
                        <strong class="text-slate-900 dark:text-white">de confirmação</strong> da mensagem. Só depois disso o acesso à área do aluno é liberado.
                    </p>
                    <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-900/50 dark:bg-amber-950/30">
                        <p class="text-sm font-bold text-amber-900 dark:text-amber-100">Não recebeu o e-mail?</p>
                        <ul class="mt-2 space-y-1.5 text-xs leading-relaxed text-amber-900/80 dark:text-amber-200/80">
                            <li>Confira as pastas <strong>Spam</strong>, <strong>Lixo eletrônico</strong> e <strong>Promoções</strong>.</li>
                            <li>Na tela de aviso que aparece após o cadastro, use o botão de <strong>reenviar o e-mail de verificação</strong>.</li>
                            <li>Se digitou o e-mail errado, fale com a equipe pelo <a href="{{ route('portal.contato') }}" class="font-semibold underline underline-offset-2">formulário de contato</a>.</li>
                        </ul>
                    </div>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card lg:order-1" data-animate="fadeInLeft"
                    caption="Exemplo da mensagem que chega no seu e-mail. O botão confirma o endereço e libera o login.">
                    <div class="rounded-xl border border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-center gap-2 border-b border-slate-200 px-3 py-2 dark:border-slate-700">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full text-[9px] font-black text-white" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">EL</span>
                            <div class="min-w-0">
                                <p class="truncate text-[11px] font-bold text-slate-900 dark:text-white">Escola Legislativa</p>
                                <p class="truncate text-[9px] text-slate-500 dark:text-slate-400">Verifique seu endereço de e-mail</p>
                            </div>
                            <span class="ml-auto shrink-0 text-[9px] text-slate-400">agora</span>
                        </div>
                        <div class="p-3.5">
                            <p class="text-[11px] leading-relaxed text-slate-600 dark:text-slate-300">
                                Obrigado por se cadastrar! Antes de começar, confirme seu endereço de e-mail clicando no botão abaixo.
                            </p>
                            <div class="mt-3">{!! $btn('Verificar e-mail', true) !!}</div>
                            <p class="mt-3 text-[9px] leading-relaxed text-slate-400 dark:text-slate-500">
                                Se você não criou esta conta, ignore esta mensagem.
                            </p>
                        </div>
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 1.5</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Entre e conheça a área do aluno</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Com o e-mail confirmado, entre com <strong class="text-slate-900 dark:text-white">e-mail e senha</strong>.
                        No menu lateral ficam seus cursos, certificados, eventos, pesquisas de satisfação e seus dados cadastrais.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    caption="Menu da área do aluno após o login.">
                    <div class="overflow-hidden rounded-xl border border-slate-700 bg-slate-950">
                        <div class="flex items-center gap-2 border-b border-slate-800 px-3 py-2.5">
                            <span class="h-6 w-6 rounded-lg" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))"></span>
                            <span class="text-[11px] font-bold text-white">Área do aluno</span>
                        </div>
                        <div class="grid grid-cols-2 gap-1 p-3">
                            @foreach (['Início', 'Meus cursos', 'Certificados', 'Meus eventos', 'Pesquisas', 'Dados cadastrais'] as $i => $item)
                                <span @class([
                                    'rounded-lg px-2.5 py-2 text-[10px] font-semibold',
                                    'bg-slate-800 text-white' => $i === 0,
                                    'text-slate-400' => $i !== 0,
                                ])>{{ $item }}</span>
                            @endforeach
                        </div>
                    </div>
                </x-portal.tutorial-shot>
            </li>
        </ol>
    </x-portal.section>

    {{-- Passo 2 --}}
    <x-portal.section
        id="inscricao-turma"
        eyebrow="Passo 2"
        title="Como se inscrever em um curso"
        subtitle="As inscrições acontecem nas turmas de cada curso, dentro do período divulgado e enquanto houver vagas."
        class="bg-slate-50/80 dark:bg-slate-900/40"
    >
        <ol class="space-y-16">
            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 2.1</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Abra o menu “Turmas”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Procure os cursos com o selo <strong class="text-slate-900 dark:text-white">Inscrições abertas</strong> e clique no que interessa.
                    </p>
                    <a href="{{ route('portal.cursos.index') }}" class="mt-4 inline-flex text-sm font-bold" style="color:var(--portal-primary)">Ver turmas disponíveis →</a>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    url="/cursos" caption="Lista de turmas: o selo verde indica que a inscrição está aberta.">
                    <div class="space-y-2.5">
                        @foreach ([['Processo Legislativo na Prática', 'Inscrições abertas', true], ['Redação Oficial e Técnica Legislativa', 'Em andamento', false]] as [$titulo, $status, $aberto])
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-bold text-slate-900 dark:text-white">{{ $titulo }}</p>
                                    <p class="mt-0.5 text-[9px] text-slate-500 dark:text-slate-400">40 h · Presencial · Turma 2026/1</p>
                                </div>
                                <span @class([
                                    'shrink-0 rounded-full px-2.5 py-1 text-[9px] font-bold text-white',
                                    'bg-emerald-600 '.$ring => $aberto,
                                    'bg-slate-400' => ! $aberto,
                                ])>{{ $status }}</span>
                            </div>
                        @endforeach
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="lg:order-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 2.2</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Clique em “Inscrever-me nesta turma”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Na página do curso aparecem as turmas com carga horária, período de inscrição e vagas restantes.
                        Se você ainda não entrou na sua conta, o botão mostra <strong class="text-slate-900 dark:text-white">Entrar para se inscrever</strong> —
                        faça o login e volte para confirmar.
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Depois de confirmar, a turma aparece em <strong class="text-slate-900 dark:text-white">Meus cursos</strong> e o card passa a mostrar
                        “Você já está inscrito nesta turma”.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card lg:order-1" data-animate="fadeInLeft"
                    url="/cursos/12" caption="Card da turma na página do curso, com o botão de inscrição.">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-slate-900 dark:text-white">Turma 2026/1 — Noturno</p>
                                <p class="mt-1 text-[9px] text-slate-500 dark:text-slate-400">Início 02/03/2026 · Térm. 30/04/2026</p>
                            </div>
                            <span class="shrink-0 rounded-full px-2.5 py-1 text-[9px] font-bold text-white" style="background:var(--portal-secondary)">18 vagas</span>
                        </div>
                        <p class="mt-3 text-[9px] text-slate-500 dark:text-slate-400">Inscrições: 10/02/2026 — 28/02/2026</p>
                        <div class="mt-3">{!! $btn('Inscrever-me nesta turma', true) !!}</div>
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 2.3</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Acompanhe o curso e pegue o certificado</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Em <strong class="text-slate-900 dark:text-white">Meus cursos</strong> ficam materiais, aulas e avisos da turma.
                        Concluído o curso, o certificado fica disponível em <strong class="text-slate-900 dark:text-white">Certificados</strong>,
                        respeitando o prazo definido pela escola.
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Quando a turma tiver <strong class="text-slate-900 dark:text-white">pesquisa de satisfação obrigatória</strong>,
                        responda em “Pesquisas” para liberar a emissão do certificado.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    caption="Área do aluno: turma inscrita e certificado disponível ao final.">
                    <div class="space-y-2.5">
                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                            <p class="text-[10px] font-bold text-emerald-900 dark:text-emerald-100">Inscrição confirmada</p>
                            <p class="mt-1 text-[9px] text-emerald-800/80 dark:text-emerald-200/70">Processo Legislativo na Prática — Turma 2026/1</p>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3 dark:border-slate-700 dark:bg-slate-900">
                            <div class="min-w-0">
                                <p class="truncate text-[10px] font-bold text-slate-900 dark:text-white">Certificado de conclusão</p>
                                <p class="mt-0.5 text-[9px] text-slate-500 dark:text-slate-400">Disponível até 30/06/2026</p>
                            </div>
                            <span class="shrink-0 rounded-full border border-slate-300 px-2.5 py-1 text-[9px] font-bold text-slate-700 dark:border-slate-600 dark:text-slate-200">Baixar PDF</span>
                        </div>
                    </div>
                </x-portal.tutorial-shot>
            </li>
        </ol>
    </x-portal.section>

    {{-- Passo 3 --}}
    <x-portal.section
        id="inscricao-evento"
        eyebrow="Passo 3"
        title="Como se inscrever em um evento"
        subtitle="Palestras, seminários e audiências abrem inscrição online por um período determinado — e alguns pedem registro de presença no local."
    >
        <ol class="space-y-16">
            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 3.1</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Abra o menu “Eventos”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Escolha o evento pela data e clique para ver os detalhes. A
                        <a href="{{ route('portal.agenda.index') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Agenda</a>
                        também mostra tudo o que está programado.
                    </p>
                    <a href="{{ route('portal.eventos.index') }}" class="mt-4 inline-flex text-sm font-bold" style="color:var(--portal-primary)">Ver eventos →</a>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    url="/eventos" caption="Lista de eventos do portal.">
                    <div class="space-y-2.5">
                        @foreach ([['Seminário de Orçamento Público', '18/03/2026 · 14h', true], ['Audiência Pública — Plano Diretor', '25/03/2026 · 09h', false]] as [$titulo, $quando, $destaque])
                            <div @class([
                                'flex items-center gap-3 rounded-xl border bg-white p-3 dark:bg-slate-900',
                                'border-slate-200 dark:border-slate-700 '.$ring => $destaque,
                                'border-slate-200 dark:border-slate-700' => ! $destaque,
                            ])>
                                <span class="flex h-10 w-10 shrink-0 flex-col items-center justify-center rounded-lg text-white" style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                                    <span class="text-[11px] font-black leading-none">{{ \Illuminate\Support\Str::before($quando, '/') }}</span>
                                    <span class="text-[7px] font-bold uppercase">Mar</span>
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-[11px] font-bold text-slate-900 dark:text-white">{{ $titulo }}</p>
                                    <p class="mt-0.5 text-[9px] text-slate-500 dark:text-slate-400">{{ $quando }} · Plenário</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div class="lg:order-2">
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 3.2</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">Clique em “Confirmar minha inscrição”</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Na página do evento, o bloco <strong class="text-slate-900 dark:text-white">Inscrição online</strong> mostra o botão de confirmação.
                        Para usá-lo é necessário estar logado como aluno, com o e-mail já confirmado (passo 1).
                    </p>
                    <ul class="mt-4 space-y-2 text-sm text-slate-600 dark:text-slate-300">
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>O botão só aparece dentro do período de inscrição divulgado.</li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>Eventos com vagas limitadas encerram quando lotam.</li>
                        <li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full" style="background:var(--portal-primary)"></span>Depois de confirmar, a página passa a mostrar “Você já está inscrito neste evento”.</li>
                    </ul>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card lg:order-1" data-animate="fadeInLeft"
                    url="/eventos/34" caption="Bloco de inscrição online na página do evento.">
                    <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                        <p class="text-[11px] font-bold text-slate-900 dark:text-white">Seminário de Orçamento Público</p>
                        <p class="mt-1 text-[9px] text-slate-500 dark:text-slate-400">18/03/2026 · 14h às 18h · Plenário</p>
                        <p class="mt-2 text-[9px] text-slate-500 dark:text-slate-400">Limite de 120 inscrições — 87 confirmadas até o momento.</p>
                        <div class="mt-3 rounded-lg border border-slate-200 p-3 dark:border-slate-700">
                            <p class="text-[10px] font-bold text-slate-900 dark:text-white">Inscrição online</p>
                            <div class="mt-2.5">{!! $btn('Confirmar minha inscrição', true) !!}</div>
                        </div>
                    </div>
                </x-portal.tutorial-shot>
            </li>

            <li class="grid gap-8 lg:grid-cols-2 lg:items-center">
                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.24em]" style="color:var(--portal-primary)">Etapa 3.3</p>
                    <h3 class="mt-2 text-xl font-bold text-slate-900 dark:text-white">No dia do evento: registre sua presença</h3>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Alguns eventos fazem a chamada por localização. Abra <strong class="text-slate-900 dark:text-white">Meus eventos</strong>
                        na área do aluno e toque em <strong class="text-slate-900 dark:text-white">Registrar presença</strong> — pelo celular,
                        no local do evento e dentro do horário liberado, permitindo o acesso à localização quando o navegador pedir.
                    </p>
                    <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                        Com a presença registrada, o certificado do evento fica disponível em <strong class="text-slate-900 dark:text-white">Certificados</strong>.
                        Para conferir a autenticidade de qualquer certificado, use a página
                        <a href="{{ route('portal.certificados.validar') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Validar certificado</a>.
                    </p>
                </div>
                <x-portal.tutorial-shot class="portal-animate-card" data-animate="fadeInRight"
                    caption="Meus eventos: botão de presença liberado durante a janela da chamada.">
                    <div class="rounded-xl border border-slate-700 bg-slate-950 p-4">
                        <p class="text-[11px] font-bold text-white">Seminário de Orçamento Público</p>
                        <p class="mt-1 text-[9px] text-slate-400">Chamada: 18/03/2026 13:45 até 18/03/2026 14:30</p>
                        <div class="mt-3 flex items-center gap-2">
                            <span class="rounded-full bg-cyan-500/15 px-2 py-1 text-[9px] font-bold text-cyan-300">Inscrito</span>
                            <span class="inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-3.5 py-2 text-[10px] font-bold text-white {{ $ring }}">Registrar presença</span>
                        </div>
                        <p class="mt-3 text-[9px] leading-relaxed text-slate-500">O navegador vai pedir permissão para usar sua localização.</p>
                    </div>
                </x-portal.tutorial-shot>
            </li>
        </ol>
    </x-portal.section>

    {{-- Ajuda --}}
    <section class="no-portal-animate border-t border-slate-200 bg-slate-50 py-16 dark:border-slate-800 dark:bg-slate-900/40">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Ficou alguma dúvida?</h2>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-relaxed text-slate-600 dark:text-slate-300">
                A equipe da escola ajuda com cadastro, inscrições e certificados. Fale com a gente pelos canais oficiais.
            </p>
            <div class="mt-8 flex flex-wrap justify-center gap-4">
                <a href="{{ route('portal.contato') }}" class="inline-flex rounded-full px-8 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                   style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">Falar com a equipe</a>
                <a href="{{ route('portal.acesso.register') }}" class="inline-flex rounded-full border border-slate-300 px-8 py-3 text-sm font-bold text-slate-800 transition hover:bg-white dark:border-slate-600 dark:text-slate-100 dark:hover:bg-slate-800">Criar meu cadastro</a>
            </div>
        </div>
    </section>
@endsection
