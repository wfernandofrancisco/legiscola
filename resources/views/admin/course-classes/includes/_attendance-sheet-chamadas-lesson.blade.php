{{-- Chamada ligada a uma aula (todas as turmas com aulas cadastradas) --}}
<div class="mb-8 space-y-6 rounded-xl border border-slate-200/80 bg-slate-50/30 p-4 dark:border-slate-700 dark:bg-slate-900/20 sm:p-6">
    <h2 class="text-base font-bold text-gray-900 dark:text-white">Selecionar aula</h2>
    <p class="text-xs text-gray-600 dark:text-gray-400">
        Escolha a aula para lançar ou revisar a chamada. Em aulas <strong>online</strong>, o aluno também pode confirmar presença no portal.
    </p>

    <div class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-5 dark:border-indigo-900 dark:bg-indigo-950/25">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Abrir aula</h3>
        <form method="GET" action="{{ route('admin.turmas.ficha-presenca', $turma) }}" class="mt-4 flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="chamadas" />
            <div>
                <label for="lesson_select" class="mb-1 block text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-gray-300">
                    Aula
                </label>
                <select id="lesson_select" name="lesson"
                    class="min-w-[18rem] rounded-lg border border-gray-300 bg-white p-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">
                    @foreach ($lessonSheetLessons as $lessonOption)
                        <option value="{{ $lessonOption->id }}" @selected(optional($lessonActiveLesson)->id === $lessonOption->id)>
                            {{ $lessonOption->title }}
                            @if ($lessonOption->date)
                                — {{ $lessonOption->date->format('d/m/Y') }}
                            @endif
                            @if ($lessonOption->is_online)
                                (online)
                            @endif
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Abrir
            </button>
        </form>
    </div>

    <div>
        <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Atalhos</p>
        <div class="overflow-x-auto">
            <div class="inline-flex min-w-full flex-wrap gap-2 rounded-xl border border-gray-200 bg-gray-50 p-2 dark:border-gray-600 dark:bg-gray-900/50">
                @foreach ($lessonSheetLessons as $lessonRow)
                    @php
                        $isActiveLessonTab = optional($lessonActiveLesson)->id === $lessonRow->id;
                        $tabMeta = $lessonSheetMeta[$lessonRow->id] ?? null;
                        $canManageTab = ! empty($authStaffCanOverrideAttendance) || ! $tabMeta ||
                            ((int) ($tabMeta['recorded_by_user_id'] ?? 0) === (int) auth()->id());
                    @endphp
                    <div class="inline-flex items-center gap-1 rounded-lg border px-2 py-1
                        {{ $isActiveLessonTab
                            ? 'border-indigo-300 bg-indigo-50 dark:border-indigo-500 dark:bg-indigo-900/30'
                            : 'border-gray-200 bg-white dark:border-gray-600 dark:bg-gray-800' }}">
                        <a href="{{ route('admin.turmas.ficha-presenca', [
                            'turma' => $turma,
                            'date' => $lessonRow->date?->format('Y-m-d') ?? $date,
                            'tab' => 'chamadas',
                            'lesson' => $lessonRow->id,
                        ]) }}"
                            class="inline-flex max-w-[16rem] flex-col rounded-md px-2 py-1 text-left text-sm font-semibold transition
                            {{ $isActiveLessonTab
                                ? 'bg-indigo-600 text-white'
                                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' }}">
                            <span class="truncate">{{ $lessonRow->title }}</span>
                            <span class="text-xs font-normal opacity-80">{{ $lessonRow->date?->format('d/m/Y') ?? '—' }}</span>
                        </a>
                        @if ($canManageTab)
                            <form method="POST" action="{{ route('admin.turmas.ficha-presenca.destroy', $turma) }}"
                                class="inline"
                                onsubmit="return confirm('Excluir todos os registros de presença desta aula?');">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="lesson_id" value="{{ $lessonRow->id }}">
                                <button type="submit"
                                    class="inline-flex rounded-md border border-rose-300 px-2 py-1 text-xs font-semibold text-rose-600 hover:bg-rose-50 dark:border-rose-700 dark:text-rose-300 dark:hover:bg-rose-900/30">
                                    Excluir
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @if ($lessonActiveLesson)
        <div class="flex flex-wrap items-end justify-between gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">
                    {{ $lessonActiveLesson->title }}
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    {{ $lessonActiveLesson->date?->translatedFormat('l, d/m/Y') ?? '—' }}
                    @if ($lessonActiveLesson->is_online)
                        <span class="ml-2 rounded bg-sky-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-sky-800 dark:bg-sky-900/50 dark:text-sky-200">Online</span>
                    @else
                        <span class="ml-2 rounded bg-stone-100 px-1.5 py-0.5 text-[10px] font-bold uppercase text-stone-700 dark:bg-stone-800 dark:text-stone-200">Presencial</span>
                    @endif
                </p>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-300">
                    @if ($lessonHasAttendance)
                        Chamada já salva — ajuste e salve de novo se precisar.
                        @if (! empty($lessonActiveMeta['recorded_by_name']))
                            Último lançamento: {{ $lessonActiveMeta['recorded_by_name'] }}.
                        @endif
                    @else
                        Nenhum registro ainda — marque presença/falta e salve (ou aguarde confirmações do aluno em aulas online).
                    @endif
                </p>
                @if ($lessonHasAttendance && ! $lessonCanManage)
                    <p class="mt-1 text-xs font-semibold text-amber-600 dark:text-amber-400">
                        Somente quem lançou pode editar; gestores do tenant podem sempre ajustar.
                    </p>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.turmas.ficha-presenca.print', ['turma' => $turma, 'lesson' => $lessonActiveLesson->id, 'mode' => 'blank']) }}" target="_blank"
                    class="inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-500 dark:bg-slate-700 dark:text-white dark:hover:bg-slate-600">
                    PDF em branco
                </a>
                <a href="{{ route('admin.turmas.ficha-presenca.print', ['turma' => $turma, 'lesson' => $lessonActiveLesson->id, 'mode' => 'filled']) }}" target="_blank"
                    class="inline-flex rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-900 dark:bg-slate-600 dark:hover:bg-slate-500">
                    PDF preenchido
                </a>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.turmas.ficha-presenca.store', $turma) }}">
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
                    @forelse ($enrollments as $enrollment)
                        @php
                            $isPresent = array_key_exists($enrollment->student_id, $lessonMap)
                                ? (bool) $lessonMap[$enrollment->student_id]
                                : false;
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
                                        class="h-10 w-10 shrink-0 rounded-full object-cover ring-1 ring-gray-200 dark:ring-gray-600" />
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-gray-900 dark:text-white">{{ $displayName }}</p>
                                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">{{ $student?->email ?? $user?->email ?? '' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-gray-600 dark:text-gray-300">
                                {{ ucfirst($enrollment->status) }}
                            </td>
                            <td class="px-6 py-3 text-center">
                                <input type="checkbox" name="present_students[]" value="{{ $enrollment->student_id }}"
                                    @checked($isPresent)
                                    @disabled(! $lessonActiveLesson || ($lessonHasAttendance && ! $lessonCanManage))
                                    class="h-5 w-5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
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

        <div class="mt-4 flex flex-wrap justify-end gap-3">
            @if (! $lessonActiveLesson)
                <button type="button"
                    class="inline-flex cursor-not-allowed rounded-lg bg-gray-400 px-4 py-2 text-sm font-semibold text-white">
                    Selecione uma aula
                </button>
            @elseif ($lessonHasAttendance && ! $lessonCanManage)
                <button type="button"
                    class="inline-flex cursor-not-allowed rounded-lg bg-gray-400 px-4 py-2 text-sm font-semibold text-white">
                    Edição bloqueada
                </button>
            @else
                <button type="submit"
                    class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    {{ $lessonHasAttendance ? 'Salvar alterações da chamada' : 'Salvar chamada desta aula' }}
                </button>
            @endif
        </div>
    </form>
</div>
