@php
    $routeIsPatterns = fn (array $patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
@endphp

@foreach([
    ['label' => 'Início', 'href' => route('home'), 'active' => request()->routeIs('home') && url()->current() === url('/')],
    ['label' => 'Notícias', 'href' => route('portal.noticias.index'), 'active' => $routeIsPatterns(['portal.noticias.*'])],
    ['label' => 'Eventos', 'href' => route('portal.eventos.index'), 'active' => $routeIsPatterns(['portal.eventos.*'])],
    ['label' => 'Agenda', 'href' => route('portal.agenda.index'), 'active' => request()->routeIs('portal.agenda.index')],
    ['label' => 'Turmas', 'href' => route('portal.cursos.index'), 'active' => $routeIsPatterns(['portal.cursos.*'])],
    ['label' => 'Equipe', 'href' => route('portal.professores.index'), 'active' => $routeIsPatterns(['portal.professores.*'])],
    ['label' => 'Institucional', 'href' => route('portal.sobre'), 'active' => request()->routeIs('portal.sobre')],
    ['label' => 'Tutorial', 'href' => route('portal.tutorial'), 'active' => request()->routeIs('portal.tutorial')],
    ['label' => 'Contato', 'href' => route('portal.contato'), 'active' => request()->routeIs('portal.contato', 'portal.contato.store')],
    ['label' => 'Validar certificado', 'href' => route('portal.certificados.validar'), 'active' => $routeIsPatterns(['portal.certificados.validar', 'portal.certificados.validar.consultar'])],
] as $item)
    <a href="{{ $item['href'] }}"
       @class([
           'portal-nav-desk-link rounded-full px-3 py-2 text-sm font-semibold tracking-tight transition-colors',
           'bg-slate-900 text-white shadow-sm dark:bg-white dark:text-slate-900' => $item['active'],
           'text-slate-800 hover:bg-black/[0.05] dark:text-slate-100 dark:hover:bg-slate-800/90' => ! $item['active'],
           'is-active' => $item['active'],
       ])>{{ $item['label'] }}</a>
@endforeach
