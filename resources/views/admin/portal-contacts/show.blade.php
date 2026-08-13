<x-layouts.admin>
    <x-slot name="title">Contato #{{ $contato->id }}</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="mb-4">
        <a href="{{ route('admin.contatos-portal.index') }}"
            class="text-sm font-semibold text-indigo-600 hover:underline dark:text-indigo-400">← Voltar à lista</a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-800 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Mensagem</h2>
                <dl class="mt-4 space-y-2 text-sm">
                    <div><dt class="font-semibold text-gray-500 dark:text-gray-400">Nome</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $contato->name }}</dd></div>
                    <div><dt class="font-semibold text-gray-500 dark:text-gray-400">E-mail</dt>
                        <dd><a class="text-indigo-600 hover:underline dark:text-indigo-400" href="mailto:{{ $contato->email }}">{{ $contato->email }}</a></dd></div>
                    @if ($contato->phone)
                        <div><dt class="font-semibold text-gray-500 dark:text-gray-400">Telefone</dt>
                            <dd class="text-gray-900 dark:text-white">{{ $contato->phone }}</dd></div>
                    @endif
                    <div><dt class="font-semibold text-gray-500 dark:text-gray-400">Recebido em</dt>
                        <dd class="text-gray-900 dark:text-white">{{ $contato->created_at->format('d/m/Y H:i') }}</dd></div>
                </dl>
                <div class="mt-6 rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-800 dark:border-gray-600 dark:bg-gray-900/50 dark:text-gray-200 whitespace-pre-wrap">{{ $contato->message }}</div>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Responder por e-mail</h2>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                    A resposta será enviada para <span class="font-semibold text-gray-800 dark:text-gray-200">{{ $contato->email }}</span> usando o remetente configurado no sistema (MAIL_* no .env).
                </p>
                <form method="post" action="{{ route('admin.contatos-portal.reply', $contato) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <label for="reply_body" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Mensagem</label>
                        <textarea id="reply_body" name="reply_body" rows="10" required
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('reply_body', $contato->reply_body) }}</textarea>
                        @error('reply_body')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <button type="submit"
                        class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                        Enviar resposta
                    </button>
                </form>
            </div>
        </div>

        <aside class="space-y-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 text-sm shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <p class="font-semibold text-gray-900 dark:text-white">Instituição</p>
                <p class="mt-2 text-gray-600 dark:text-gray-400">{{ $tenant->display_name }}</p>
                @if ($tenant->contact_email)
                    <p class="mt-3 text-xs text-gray-500">E-mail administrativo (cópia do portal): {{ $tenant->contact_email }}</p>
                @else
                    <p class="mt-3 text-xs text-amber-700 dark:text-amber-300">Sem contact_email no tenant — o portal não envia cópia automática por e-mail, mas a mensagem fica salva aqui.</p>
                @endif
            </div>
            @if ($contato->replied_at)
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/80 p-5 text-sm dark:border-emerald-900 dark:bg-emerald-950/30">
                    <p class="font-semibold text-emerald-900 dark:text-emerald-100">Respondido em {{ $contato->replied_at->format('d/m/Y H:i') }}</p>
                    @if ($contato->repliedBy)
                        <p class="mt-1 text-xs text-emerald-800 dark:text-emerald-200">Por {{ $contato->repliedBy->name }}</p>
                    @endif
                </div>
            @endif
        </aside>
    </div>
</x-layouts.admin>
