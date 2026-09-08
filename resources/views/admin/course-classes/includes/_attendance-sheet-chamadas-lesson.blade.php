{{-- Chamada ligada a uma aula (hub da turma · aba Chamadas) --}}
@php
    $activeLessonId = (int) optional($lessonActiveLesson)->id;
    $enrollmentCount = ($attendanceEnrollments ?? $enrollments ?? collect())->count();
@endphp

<div class="mb-8 space-y-5 rounded-xl border border-slate-200/80 bg-slate-50/30 p-4 dark:border-slate-700 dark:bg-slate-900/20 sm:p-6">
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-200" role="alert" aria-live="polite">
            <p class="font-semibold">Não foi possível salvar a chamada</p>
            <ul class="mt-2 list-inside list-disc space-y-1 text-xs">
                @foreach ($errors->all() as $errorMessage)
                    <li>{{ $errorMessage }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($lessonActiveLesson)
        <div id="chamada-aberta"
             class="scroll-mt-28 rounded-xl border-2 border-indigo-400 bg-indigo-50/90 p-4 shadow-sm dark:border-indigo-500 dark:bg-indigo-950/40"
             role="status"
             aria-live="polite">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-indigo-700 dark:text-indigo-300">Chamada aberta agora</p>
                    <p class="mt-1 text-pretty text-lg font-bold text-slate-900 dark:text-white">{{ $lessonActiveLesson->title }}</p>
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">
                        {{ $lessonActiveLesson->date?->translatedFormat('l, d/m/Y') ?? 'Sem data' }}
                        ·
                        {{ $lessonActiveLesson->is_online ? 'Online' : 'Presencial' }}
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    @if ($lessonHasAttendance)
                        <span class="inline-flex items-center rounded-full bg-emerald-600 px-3 py-1 text-xs font-bold text-white">
                            Já lançada
                            @if ($enrollmentCount > 0)
                                · {{ (int) ($lessonAttendanceFlags[$activeLessonId]['present'] ?? 0) }}/{{ $enrollmentCount }} presentes
                            @endif
                        </span>
                        <p class="max-w-xs text-right text-xs text-slate-600 dark:text-slate-300">
                            Pode revisar e salvar de novo.
                            @if (! empty($lessonActiveMeta['recorded_by_name']))
                                Último lançamento: {{ $lessonActiveMeta['recorded_by_name'] }}.
                            @endif
                        </p>
                    @else
                        <span class="inline-flex items-center rounded-full bg-amber-500 px-3 py-1 text-xs font-bold text-white">
                            Ainda sem chamada
                        </span>
                        <p class="max-w-xs text-right text-xs text-slate-600 dark:text-slate-300">
                            Marque presença/falta abaixo e clique em salvar.
                        </p>
                    @endif
                </div>
            </div>
            @if ($lessonHasAttendance && ! $lessonCanManage)
                <p class="mt-3 rounded-lg border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-100">
                    Edição bloqueada: somente quem lançou (ou gestor do tenant) pode alterar esta chamada.
                </p>
            @endif
        </div>
    @endif

    <div>
        @php
            $lessonsTotal = $lessonSheetLessons->count();
            $lessonsSaved = collect($lessonAttendanceFlags ?? [])->filter(fn ($f) => ! empty($f['has']))->count();
            $lessonsPending = max(0, $lessonsTotal - $lessonsSaved);
        @endphp
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h2 class="text-base font-bold text-gray-900 dark:text-white">Escolher aula</h2>
                <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                    {{ $lessonsTotal }} aula(s) · {{ $lessonsSaved }} com chamada · {{ $lessonsPending }} pendente(s)
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.turmas.show', $turma) }}#chamada-aberta" class="mt-4 flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-600 dark:bg-slate-800 sm:flex-row sm:items-end">
            <input type="hidden" name="tab" value="chamadas" />
            <div class="min-w-0 flex-1">
                <label for="lesson_select_chamadas" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600 dark:text-slate-300">
                    Aula
                </label>
                <select id="lesson_select_chamadas" name="lesson"
                    class="w-full rounded-lg border border-slate-300 bg-white p-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:border-slate-600 dark:bg-slate-900 dark:text-white">
                    @foreach ($lessonSheetLessons as $lessonOption)
                        @php
                            $optId = (int) $lessonOption->id;
                            $optSaved = ! empty(($lessonAttendanceFlags[$optId]['has'] ?? false));
                            $optPresent = (int) ($lessonAttendanceFlags[$optId]['present'] ?? 0);
                        @endphp
                        <option value="{{ $lessonOption->id }}" @selected($activeLessonId === $optId)>
                            {{ $lessonOption->date?->format('d/m/Y') ?? 's/ data' }}
                            — {{ $lessonOption->title }}
                            @if ($lessonOption->is_online) (online) @endif
                            · {{ $optSaved ? 'salva ('.$optPresent.'/'.$enrollmentCount.')' : 'sem chamada' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-[opacity,transform] duration-150 hover:opacity-95 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500">
                Abrir chamada
            </button>
        </form>

        <details class="mt-3 rounded-xl border border-slate-200 bg-white dark:border-slate-600 dark:bg-slate-800" @if($lessonsTotal <= 8) open @endif>
            <summary class="cursor-pointer list-none px-4 py-3 text-sm font-semibold text-slate-800 marker:content-none dark:text-slate-100 [&::-webkit-details-marker]:hidden">
                <span class="inline-flex items-center gap-2">
                    Ver status de todas as aulas
                    <span class="text-xs font-normal text-slate-500">(lista compacta)</span>
                </span>
            </summary>
            <div class="max-h-64 overflow-y-auto border-t border-slate-200 dark:border-slate-600">
                <table class="w-full text-left text-sm">
                    <thead class="sticky top-0 z-[1] bg-slate-50 text-[11px] uppercase tracking-wide text-slate-500 dark:bg-slate-900 dark:text-slate-400">
                        <tr>
                            <th class="px-4 py-2 font-semibold">Aula</th>
                            <th class="px-4 py-2 font-semibold">Data</th>
                            <th class="px-4 py-2 font-semibold">Status</th>
                            <th class="px-4 py-2 text-right font-semibold">Ação</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                        @foreach ($lessonSheetLessons as $lessonRow)
                            @php
                                $lid = (int) $lessonRow->id;
                                $isActiveLessonTab = $activeLessonId === $lid;
                                $flag = $lessonAttendanceFlags[$lid] ?? ['has' => false, 'present' => 0, 'total' => 0];
                                $hasSaved = (bool) ($flag['has'] ?? false);
                                $tabMeta = $lessonSheetMeta[$lid] ?? null;
                                $canManageTab = ! empty($authStaffCanOverrideAttendance) || ! $hasSaved ||
                                    ((int) ($tabMeta['recorded_by_user_id'] ?? 0) === (int) auth()->id());
                            @endphp
                            <tr @class([
                                'bg-indigo-50/80 dark:bg-indigo-950/30' => $isActiveLessonTab,
                                'hover:bg-slate-50 dark:hover:bg-slate-900/40' => ! $isActiveLessonTab,
                            ])>
                                <td class="max-w-[14rem] truncate px-4 py-2 font-medium text-slate-900 dark:text-white">
                                    {{ $lessonRow->title }}
                                    @if ($lessonRow->is_online)
                                        <span class="text-[10px] font-normal text-sky-600 dark:text-sky-300">online</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 tabular-nums text-slate-600 dark:text-slate-300">
                                    {{ $lessonRow->date?->format('d/m/Y') ?? '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    @if ($isActiveLessonTab)
                                        <span class="inline-flex rounded-full bg-indigo-600 px-2 py-0.5 text-[11px] font-bold text-white">Aberta</span>
                                    @elseif ($hasSaved)
                                        <span class="inline-flex rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                                            Salva · {{ (int) $flag['present'] }}/{{ $enrollmentCount }}
                                        </span>
                                    @else
                                        <span class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-900 dark:bg-amber-900/40 dark:text-amber-100">Sem chamada</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-2">
                                        @if (! $isActiveLessonTab)
                                            <a href="{{ route('admin.turmas.show', [
                                                    'turma' => $turma,
                                                    'date' => $lessonRow->date?->format('Y-m-d') ?? $date,
                                                    'tab' => 'chamadas',
                                                    'lesson' => $lessonRow->id,
                                                ]) }}#chamada-aberta"
                                               class="text-xs font-semibold text-indigo-600 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:text-indigo-300">
                                                Abrir
                                            </a>
                                        @else
                                            <span class="text-xs font-semibold text-slate-400">Em foco</span>
                                        @endif
                                        @if ($hasSaved && $canManageTab)
                                            <form method="POST" action="{{ route('admin.turmas.ficha-presenca.destroy', $turma) }}"
                                                  class="inline"
                                                  onsubmit="return confirm('Excluir todos os registros de presença desta aula?');">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="lesson_id" value="{{ $lessonRow->id }}">
                                                <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline dark:text-rose-300">
                                                    Excluir
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </details>
    </div>

    @if ($lessonActiveLesson)
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Lista de presença · {{ $lessonActiveLesson->title }}</p>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    {{ $enrollmentCount }} aluno(s) elegíveis (inscrito / cursando / concluído / baixa presença).
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.turmas.ficha-presenca.print', ['turma' => $turma, 'lesson' => $lessonActiveLesson->id, 'mode' => 'blank']) }}" target="_blank"
                    class="inline-flex min-h-10 items-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-[background-color,transform] duration-150 hover:bg-slate-50 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:border-slate-500 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">
                    PDF em branco
                </a>
                <a href="{{ route('admin.turmas.ficha-presenca.print', ['turma' => $turma, 'lesson' => $lessonActiveLesson->id, 'mode' => 'filled']) }}" target="_blank"
                    class="inline-flex min-h-10 items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white transition-[opacity,transform] duration-150 hover:opacity-95 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 dark:bg-slate-600">
                    PDF preenchido
                </a>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.turmas.ficha-presenca.store', $turma) }}" id="form-chamada-aula">
        @csrf
        <input type="hidden" name="lesson_id" value="{{ $lessonActiveLesson?->id }}">
        <input type="hidden" name="date" value="{{ $date }}" />

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Aluno</th>
                        <th class="px-6 py-3 text-left font-semibold text-gray-700 dark:text-gray-300">Matrícula</th>
                        <th class="px-6 py-3 text-center font-semibold text-gray-700 dark:text-gray-300">Presente</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @php
                        $lessonMap = $lessonAttendanceByStudent->toArray();
                    @endphp
                    @forelse (($attendanceEnrollments ?? $enrollments) as $enrollment)
                        @php
                            $hasRecord = array_key_exists($enrollment->student_id, $lessonMap);
                            $isPresent = $hasRecord ? (bool) $lessonMap[$enrollment->student_id] : false;
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            @php
                                $student = $enrollment->student;
                                $user = $student?->user;
                                $displayName = $user?->name ?? $student?->email ?? $user?->email ?? '—';
                                $photoSrc = null;
                                if ($student?->photo_path) {
                                    $photoSrc = asset('storage/'.$student->photo_path);
                                } elseif ($user?->avatar) {
                                    $av = (string) $user->avatar;
                                    $photoSrc = str_starts_with($av, 'http://') || str_starts_with($av, 'https://')
                                        ? $av
                                        : asset('storage/'.$av);
                                }
                                $initial = mb_strtoupper(mb_substr($displayName !== '—' ? $displayName : '?', 0, 1));
                            @endphp
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $photoSrc ?? 'https://placehold.co/40x40/e5e7eb/6b7280?text='.rawurlencode($initial) }}"
                                        alt=""
                                        width="40"
                                        height="40"
                                        loading="lazy"
                                        class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-600" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $displayName }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $student?->email ?? $user?->email ?? '' }}</p>
                                        @if ($lessonHasAttendance)
                                            <p class="mt-0.5 text-[11px] {{ $hasRecord ? ($isPresent ? 'text-emerald-600' : 'text-rose-600') : 'text-slate-400' }}">
                                                @if ($hasRecord)
                                                    {{ $isPresent ? 'Registrado: presente' : 'Registrado: falta' }}
                                                @else
                                                    Sem registro nesta chamada
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                {{ ucfirst($enrollment->status) }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                <label class="inline-flex min-h-11 min-w-11 cursor-pointer items-center justify-center">
                                    <span class="sr-only">Presente: {{ $displayName }}</span>
                                    <input type="checkbox" name="present_students[]" value="{{ $enrollment->student_id }}"
                                        @checked($isPresent)
                                        @disabled(! $lessonActiveLesson || ($lessonHasAttendance && ! $lessonCanManage))
                                        class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                </label>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-gray-500">
                                Nenhum aluno inscrito/cursando/concluído nesta turma.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-500 dark:text-slate-400" aria-live="polite">
                @if (! $lessonActiveLesson)
                    Selecione uma aula acima para lançar a chamada.
                @elseif ($lessonHasAttendance && ! $lessonCanManage)
                    Formulário bloqueado para edição.
                @elseif ($lessonHasAttendance)
                    Alterações sobrescrevem a chamada já salva.
                @else
                    Nenhum aluno marcado = todos faltam ao salvar.
                @endif
            </p>
            @if (! $lessonActiveLesson)
                <button type="button"
                    class="inline-flex cursor-not-allowed rounded-lg bg-gray-400 px-4 py-2 text-sm font-semibold text-white" disabled>
                    Selecione uma aula
                </button>
            @elseif ($lessonHasAttendance && ! $lessonCanManage)
                <button type="button"
                    class="inline-flex cursor-not-allowed rounded-lg bg-gray-400 px-4 py-2 text-sm font-semibold text-white" disabled>
                    Edição bloqueada
                </button>
            @else
                <button type="submit"
                    class="inline-flex min-h-11 items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition-[opacity,transform] duration-150 hover:opacity-95 active:scale-[0.97] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500">
                    {{ $lessonHasAttendance ? 'Salvar alterações da chamada' : 'Salvar chamada desta aula' }}
                </button>
            @endif
        </div>
    </form>
</div>

<script>
    (function () {
        var target = document.getElementById('chamada-aberta');
        if (!target || !window.location.hash) {
            return;
        }
        if (window.location.hash === '#chamada-aberta') {
            window.requestAnimationFrame(function () {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        }
    })();
</script>
