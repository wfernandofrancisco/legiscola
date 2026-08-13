<div class="flex flex-col gap-1">
    <a href="{{ route('home') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Início</a>
    <a href="{{ route('portal.noticias.index') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Notícias</a>
    <a href="{{ route('portal.eventos.index') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Eventos</a>
    <a href="{{ route('portal.agenda.index') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Agenda</a>
    <a href="{{ route('portal.cursos.index') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Turmas</a>
    <a href="{{ route('portal.professores.index') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Equipe</a>
    <a href="{{ route('portal.cursos.historico') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Histórico de turmas</a>
    <a href="{{ route('portal.sobre') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Institucional</a>
    <a href="{{ route('portal.contato') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Contato</a>
    <a href="{{ route('portal.certificados.validar') }}" class="portal-nav-mobile-a rounded-lg px-3 py-2 font-medium text-slate-800 hover:bg-slate-100 dark:text-slate-100 dark:hover:bg-slate-800">Validar certificado</a>
    <hr class="my-2 border-slate-200 dark:border-slate-700"/>
    <a href="{{ route('portal.acesso.docente.login') }}" class="rounded-lg border-2 px-3 py-2 font-semibold text-center text-slate-800 dark:border-slate-500 dark:text-slate-100"
       style="border-color:color-mix(in srgb,var(--portal-primary,#3b82f6),transparent 65%)">Área do docente — Entrar</a>
    <a href="{{ route('portal.acesso.login') }}" class="rounded-lg px-3 py-2 font-semibold text-white shadow"
       style="background:linear-gradient(135deg,var(--portal-primary,#3b82f6),var(--portal-secondary,#1e40af))">Área do aluno — Entrar</a>
</div>
