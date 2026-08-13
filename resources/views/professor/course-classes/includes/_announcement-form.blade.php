@php
    $defaultRef = $defaultReferenceDate ?? null;
@endphp

<div class="mb-6 rounded-xl border border-indigo-200 bg-indigo-50/40 p-5 shadow-sm dark:border-indigo-900 dark:bg-indigo-950/30">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Enviar aviso à turma</h3>
    <p class="mt-1 text-xs text-gray-600 dark:text-gray-400">
        E-mail usa a fila do Laravel e as variáveis <code class="rounded bg-white/80 px-1 dark:bg-gray-800">MAIL_*</code> do seu <code class="rounded bg-white/80 px-1 dark:bg-gray-800">.env</code>.
        SMS está em modo <strong>simulado</strong> (registro em log), ideal para testes — troque o binding por um gateway (ex.: Twilio, Zenvia) quando for para produção.
    </p>
    <p class="mt-2 text-xs text-amber-800 dark:text-amber-200/90">
        LGPD: envie apenas para quem tenha relação com a turma e contato cadastrado para esse fim. O destinatário pode solicitar correção ou exclusão dos dados conforme a política da instituição.
    </p>

    <form method="POST" action="{{ route('professor.turmas.avisos.store', $turma) }}" class="mt-4 space-y-4">
        @csrf
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label for="announcement_reference_date" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Data de referência (opcional)</label>
                <input id="announcement_reference_date" name="reference_date" type="date"
                    value="{{ old('reference_date', $defaultRef) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
                @error('reference_date')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="announcement_subject" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Assunto do e-mail</label>
                <input id="announcement_subject" name="subject" type="text" maxlength="190"
                    value="{{ old('subject') }}"
                    placeholder="Obrigatório se marcar e-mail"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white" />
                @error('subject')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
        <div>
            <label for="announcement_body" class="mb-1 block text-xs font-semibold text-gray-700 dark:text-gray-300">Mensagem</label>
            <textarea id="announcement_body" name="body" rows="5" required
                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <fieldset>
            <legend class="mb-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Canais</legend>
            <div class="flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                    <input type="checkbox" name="channels[]" value="email" class="rounded border-gray-300"
                        {{ in_array('email', old('channels', []), true) ? 'checked' : '' }} />
                    E-mail
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-800 dark:text-gray-200">
                    <input type="checkbox" name="channels[]" value="sms" class="rounded border-gray-300"
                        {{ in_array('sms', old('channels', []), true) ? 'checked' : '' }} />
                    SMS (simulado / log)
                </label>
            </div>
            @error('channels')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </fieldset>
        <label class="flex items-start gap-2 text-xs text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="consent_acknowledged" value="1" class="mt-0.5 rounded border-gray-300"
                {{ old('consent_acknowledged') ? 'checked' : '' }} required />
            <span>Confirmo que este envio respeita a finalidade educativa, os contatos cadastrados e a LGPD.</span>
        </label>
        @error('consent_acknowledged')
            <p class="text-xs text-red-600">{{ $message }}</p>
        @enderror
        <div>
            <button type="submit"
                class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                Registrar aviso e disparar
            </button>
        </div>
    </form>
</div>
