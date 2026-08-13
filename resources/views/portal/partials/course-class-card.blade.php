@props(['turma', 'tone' => 'primary'])
@php
    $badgeGradient = match ($tone) {
        'tertiary' => 'linear-gradient(135deg, var(--portal-tertiary), var(--portal-secondary))',
        'secondary' => 'linear-gradient(135deg, var(--portal-secondary), var(--portal-tertiary))',
        default => 'linear-gradient(135deg, var(--portal-primary), var(--portal-secondary))',
    };
    $course = $turma->course;
    $href = $course ? route('portal.cursos.show', ['curso' => $course->id]) : '#';
    $labels = [
        'inscricao' => ['Inscrições abertas', 'Aberto'],
        'em_andamento' => ['Em andamento', 'Ativo'],
        'cadastrado' => ['Cadastrada', 'Planejado'],
        'concluido' => ['Concluída', 'Encerrado'],
        'cancelado' => ['Cancelada', 'Indisponível'],
    ];
    $meta = $labels[$turma->status ?? ''] ?? ['Turma', '—'];
    $matriculas = (int) ($turma->matriculas_count ?? 0);
    $vagas = $turma->max_seats !== null ? max(0, (int) $turma->max_seats - $matriculas) : null;
    $docentes = $turma->relationLoaded('teachers') && $turma->teachers->isNotEmpty()
        ? $turma->teachers->pluck('full_name')->filter()->implode(', ')
        : null;
    $coordenacao = $course?->admin?->name ?? null;
@endphp
<a href="{{ $href }}" class="portal-animate-card group flex gap-4 rounded-2xl border border-slate-200/80 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-slate-300 hover:shadow-xl dark:border-slate-800 dark:bg-slate-900/40">
    <div class="h-28 w-36 shrink-0 overflow-hidden rounded-xl bg-gradient-to-br from-slate-100 via-slate-50 to-white dark:from-slate-800 dark:via-slate-900 dark:to-slate-800">
        <div class="flex h-full items-center justify-center text-3xl font-black" style="color: var(--portal-primary, #1d4ed8)">
            {{ $course ? mb_substr($course->name, 0, 1) : '·' }}
        </div>
    </div>
    <div class="min-w-0 flex-1">
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex rounded-full px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-white shadow-sm"
                  style="background: {{ $badgeGradient }}">{{ $meta[1] }}</span>
            @if($turma->enrollment_end)
                <span class="text-xs text-slate-500 dark:text-slate-400">{{ $turma->enrollment_end->format('d/m/Y') }}</span>
            @endif
        </div>
        <h3 class="mt-2 font-bold leading-snug text-slate-900 group-hover:underline dark:text-white">{{ $course?->name ?? 'Curso' }} — {{ $turma->name }}</h3>
        <dl class="mt-3 flex flex-wrap gap-x-6 gap-y-1 text-xs text-slate-600 dark:text-slate-400">
            <div><dt class="inline font-semibold text-slate-500">Status:</dt> <dd class="inline">{{ $meta[0] }}</dd></div>
            @if($vagas !== null)
                <div><dt class="inline font-semibold text-slate-500">Vagas:</dt> <dd class="inline">{{ $vagas }} livre{{ $vagas === 1 ? '' : 's' }}</dd></div>
            @endif
            @if($course?->workload_hours)
                <div><dt class="inline font-semibold text-slate-500">CH:</dt> <dd class="inline">{{ $course->workload_hours }}h</dd></div>
            @endif
            @if($docentes)
                <div class="basis-full"><dt class="inline font-semibold text-slate-500">Docente(s):</dt> <dd class="inline">{{ $docentes }}</dd></div>
            @endif
            @if($coordenacao)
                <div class="basis-full"><dt class="inline font-semibold text-slate-500">Coordenação:</dt> <dd class="inline">{{ $coordenacao }}</dd></div>
            @endif
        </dl>
    </div>
</a>
