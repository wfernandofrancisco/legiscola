@php
    $user = $user ?? null;
    $action = $action ?? 'create';
    $userTypes = $userTypes ?? \App\Enums\UserType::options();
    $statuses = $statuses ?? \App\Enums\UserStatus::options();
@endphp

<form method="POST"
    action="{{ $action === 'create' ? route('admin.users.store') : route('admin.users.update', $user) }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8 overflow-hidden">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    {{-- Erros --}}
    @if ($errors->any())
        <div
            class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                    clip-rule="evenodd" />
            </svg>
            <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-4">

        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Dados Pessoais</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="sm:col-span-2">
                    <x-form.input name="name" label="Nome completo" :required="true" :value="$user?->name ?? old('name')" />
                </div>
                
                    <x-form.input name="email" label="E-mail" type="email" :required="true" :value="$user?->email ?? old('email')" />
                
                <x-form.input name="phone" label="Telefone" :value="$user?->phone ?? old('phone')" data-mask="phone" />
                <x-form.select name="user_type" label="Tipo de Usuário" :required="true" :selected="$user?->user_type ?? old('user_type', 'tenant_user')" :options="$userTypes" />
                <x-form.select name="status" label="Situação" :required="true" :selected="$user?->status ?? old('status', 'ativo')" :options="$statuses" />
            </div>
        </fieldset>

        @if ($action === 'create')
            <fieldset>
                <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Informações de Acesso</legend>
                <div class="pb-6 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zm-11-1a1 1 0 11-2 0 1 1 0 012 0z" clip-rule="evenodd" />
                        </svg>
                        <div>
                            <p class="font-medium text-blue-900 dark:text-blue-100 mb-1">Senha Temporária</p>
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                Uma senha temporária será gerada e enviada para o e-mail do usuário. O usuário poderá alterá-la após fazer login.
                            </p>
                        </div>
                    </div>
                </div>
            </fieldset>
        @endif

        {{-- Estatísticas (apenas no edit) --}}
        @if ($action === 'edit' && $user)
            <fieldset>
                <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Estatísticas</legend>
                <div class="pb-6">
                    <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                        <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <dt class="text-gray-600 dark:text-gray-400 font-medium">Data de Cadastro:</dt>
                                <dd class="text-gray-900 dark:text-white">{{ $user->created_at->format('d/m/Y') }}</dd>
                            </div>
                            <div class="flex items-center gap-2">
                                <dt class="text-gray-600 dark:text-gray-400 font-medium">Status do E-mail:</dt>
                                <dd class="text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center gap-1">
                                        {{ $user->email_verified_at ? 'Verificado' : 'Pendente' }}
                                        @if($user->email_verified_at)
                                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                            </svg>
                                        @endif
                                    </span>
                                </dd>
                            </div>
                            <div class="flex items-center gap-2">
                                <dt class="text-gray-600 dark:text-gray-400 font-medium">Status da Conta:</dt>
                                <dd class="text-gray-900 dark:text-white">
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium
                                        {{ $user->status === 'ativo' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                                           ($user->status === 'inativo' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                                            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300') }}">
                                        {{ $user->status_label }}
                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </fieldset>
        @endif

        {{-- Ações --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            {{-- Legenda de campos obrigatórios --}}
            <div class="mb-6 text-xs text-gray-500 dark:text-gray-500 flex items-center gap-1.5">
                <svg class="w-3 h-3 text-gray-400 " fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="font-medium">Campos marcados com</span>
                <span class="text-red-600 dark:text-red-500 font-semibold">*</span>
                <span>são obrigatórios</span>
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 active:bg-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $action === 'create' ? 'Criar Usuário' : 'Salvar Alterações' }}
                </button>
                <a href="{{ route('admin.users.index') }}"
                    class="rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </a>
            </div>
        </div>

    </div>
</form>
