@php
    $latestCertificateHashByStudent = $latestCertificateHashByStudent ?? [];
    $activeEventCertificateTemplate = $activeEventCertificateTemplate ?? null;
@endphp

<div class="mt-10 w-full rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
    <div class="flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:items-start lg:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Inscrições online</h2>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Presença, certificados e inclusão manual de quem chegou sem se inscrever pelo portal.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button"
                onclick="document.getElementById('add-event-participant-modal')?.showModal()"
                class="inline-flex shrink-0 items-center justify-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Adicionar participante
            </button>
            <a href="{{ route('admin.eventos.triagem-pdf', $event) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-indigo-300 bg-indigo-50 px-4 py-2 text-sm font-medium text-indigo-800 hover:bg-indigo-100 dark:border-indigo-700 dark:bg-indigo-950/40 dark:text-indigo-200 dark:hover:bg-indigo-900/50">
                Relatório triagem (PDF)
            </a>
            <a href="{{ route('admin.eventos.inscritos-pdf', $event) }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Lista inscritos (PDF)
            </a>
        </div>
    </div>

    @if ($event->enrollments->isNotEmpty())
        <div class="mt-6 flex flex-wrap items-center gap-3">
            <form method="POST" action="{{ route('admin.eventos.inscricao.todos-presentes', $event) }}"
                onsubmit="return confirm('Marcar todos os inscritos como presentes?');">
                @csrf
                <button type="submit"
                    class="inline-flex rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                    Marcar todos presentes
                </button>
            </form>
        </div>
    @endif

    @if ($event->enrollments->isEmpty())
        <p class="mt-6 text-sm text-gray-600 dark:text-gray-300">Nenhuma inscrição registrada ainda.</p>
    @else
        <div class="mt-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Aluno</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">E-mail</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Matrícula</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Inscrito em</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-700 dark:text-gray-300">Presença</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-700 dark:text-gray-300">Certificado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($event->enrollments as $row)
                        @php
                            $stu = $row->student;
                            $u = $stu?->user;
                            $sid = $stu ? (int) $stu->id : 0;
                            $latestHash = $sid > 0 ? ($latestCertificateHashByStudent[$sid] ?? null) : null;
                            $isPresente = in_array($row->presente, [true, 1, '1', 'true', 'on'], true);
                        @endphp
                        <tr>
                            <td class="px-4 py-2 text-gray-900 dark:text-white">{{ $u?->name ?? $stu?->email ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $u?->email ?? $stu?->email ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $stu?->enrollment_number ?? '—' }}</td>
                            <td class="px-4 py-2 text-gray-600 dark:text-gray-300">{{ $row->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2">
                                <form method="POST" action="{{ route('admin.eventos.inscricao.update', ['evento' => $event, 'event_enrollment' => $row]) }}"
                                    class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="presente"
                                        class="rounded-lg border border-gray-300 bg-white px-2 py-1 text-xs dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100">
                                        <option value="1" @selected($row->presente)>Presente</option>
                                        <option value="0" @selected(! $row->presente)>Ausente</option>
                                    </select>
                                    <button type="submit"
                                        class="rounded-lg bg-indigo-600 px-2 py-1 text-xs font-semibold text-white hover:bg-indigo-700">
                                        Salvar
                                    </button>
                                </form>
                                @if ($row->checkin_em)
                                    <p class="mt-1 text-[10px] text-emerald-700 dark:text-emerald-400">
                                        GPS {{ $row->checkin_em->format('d/m H:i') }}
                                    </p>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-right">
                                @if ($isPresente && $latestHash)
                                    <a href="{{ route('certificados.download', $latestHash) }}" target="_blank" rel="noopener noreferrer"
                                        class="inline-flex rounded-lg bg-emerald-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-emerald-700">
                                        Imprimir certificado
                                    </a>
                                @elseif (! $isPresente)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Só após marcar presença</span>
                                @elseif ($isPresente && $activeEventCertificateTemplate && ! $latestHash)
                                    <button type="submit" form="issue-event-cert-{{ $row->id }}" formtarget="_blank"
                                        class="inline-flex rounded-lg bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-700">
                                        Emitir certificado
                                    </button>
                                @elseif ($isPresente && ! $activeEventCertificateTemplate)
                                    <span class="text-xs text-gray-500 dark:text-gray-400" title="Cadastre um template ativo com tipo de emissão «evento».">Sem template ativo</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($activeEventCertificateTemplate)
            @foreach ($event->enrollments as $row)
                @php
                    $stu = $row->student;
                    $u = $stu?->user;
                    $sid = $stu ? (int) $stu->id : 0;
                    $latestHash = $sid > 0 ? ($latestCertificateHashByStudent[$sid] ?? null) : null;
                    $isPresente = in_array($row->presente, [true, 1, '1', 'true', 'on'], true);
                @endphp
                @if ($isPresente && ! $latestHash)
                    <form method="POST" action="{{ route('admin.escola.certificados.issue') }}"
                        id="issue-event-cert-{{ $row->id }}" class="hidden">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $stu?->id }}">
                        <input type="hidden" name="event_id" value="{{ $event->id }}">
                        <input type="hidden" name="certificate_template_id" value="{{ $activeEventCertificateTemplate->id }}">
                        <input type="hidden" name="snapshot[student_name]" value="{{ $u?->name ?? $stu?->email ?? 'Aluno' }}">
                        <input type="hidden" name="snapshot[course_name]" value="{{ $event->title }}">
                        <input type="hidden" name="snapshot[evento_nome]" value="{{ $event->title }}">
                        <input type="hidden" name="snapshot[palestrante_nome]" value="{{ $event->palestrante_nome }}">
                        <input type="hidden" name="snapshot[event_id]" value="{{ $event->id }}">
                        <input type="hidden" name="snapshot[workload_hours]" value="0">
                        <input type="hidden" name="redirect_to_download" value="1">
                    </form>
                @endif
            @endforeach
        @endif
    @endif
</div>

<dialog id="add-event-participant-modal"
    class="fixed left-1/2 top-1/2 z-[80] m-0 w-[calc(100%-1.5rem)] max-w-xl -translate-x-1/2 -translate-y-1/2 rounded-xl border-0 bg-white p-0 shadow-2xl dark:bg-gray-800 backdrop:bg-black/60">
    <form method="POST" action="{{ route('admin.eventos.inscricao.store', $event) }}"
        class="max-h-[90vh] overflow-y-auto">
        @csrf
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-700">
            <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-indigo-600 dark:text-indigo-300">Lista de presença</p>
            <h3 class="mt-1 text-lg font-semibold text-gray-900 dark:text-white">Adicionar participante</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Cria o cadastro de aluno (usuário de acesso) se ainda não existir e inscreve nesta edição.
            </p>
        </div>

        <div class="space-y-4 px-6 py-5">
            @if ($errors->eventParticipant->isNotEmpty())
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-800/60 dark:bg-red-950/40 dark:text-red-200">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->eventParticipant->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <x-form.input id="participant_name" name="name" label="Nome completo" :required="true" :value="old('name')" autocomplete="name" />
                <x-form.input id="participant_cpf" name="cpf" label="CPF" data-mask="cpf" :required="true" :value="old('cpf')" inputmode="numeric" />
                <x-form.date id="participant_birth_date" name="birth_date" label="Data de nascimento" :required="true" :value="old('birth_date')" />
                <x-form.select id="participant_sexo" name="sexo" label="Sexo" :required="true" :selected="old('sexo')" :options="[
                    'masculino' => 'Masculino',
                    'feminino' => 'Feminino',
                    'outro' => 'Outro',
                    'nao_informado' => 'Não informado',
                ]" />
                <x-form.input id="participant_email" name="email" label="E-mail" type="email" :required="true" :value="old('email')" autocomplete="email" />
                <x-form.input id="participant_cidade" name="cidade" label="Cidade" :required="true" :value="old('cidade')" />
                <x-form.input id="participant_password" name="password" label="Senha (opcional)" type="password" autocomplete="new-password"
                    hint="Se ficar em branco, o aluno recebe e-mail para definir a senha." />
                <x-form.input id="participant_password_confirmation" name="password_confirmation" label="Confirmar senha" type="password" autocomplete="new-password" />
            </div>

            <label class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200">
                <input type="checkbox" name="presente" value="1" class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" @checked(old('presente'))>
                <span>
                    <span class="font-semibold">Já está presente</span>
                    <span class="mt-0.5 block text-xs text-gray-500 dark:text-gray-400">Marque se a pessoa chegou ao evento e a presença deve constar agora.</span>
                </span>
            </label>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-2 border-t border-gray-100 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/40">
            <button type="button" onclick="document.getElementById('add-event-participant-modal')?.close()"
                class="inline-flex rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700">
                Cancelar
            </button>
            <button type="submit"
                class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Inscrever no evento
            </button>
        </div>
    </form>
</dialog>
@if ($errors->eventParticipant->isNotEmpty())
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('add-event-participant-modal')?.showModal();
        });
    </script>
@endif

