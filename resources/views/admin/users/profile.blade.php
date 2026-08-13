<x-layouts.admin>
    <x-slot name="title">Meu Perfil</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="max-w-2xl space-y-6">
        {{-- Formulário de dados pessoais --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Dados Pessoais</h2>

            <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Erros --}}
                @if ($errors->has('name') || $errors->has('email') || $errors->has('phone'))
                    <div
                        class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                            @foreach ($errors->keys() as $field)
                                @if (in_array($field, ['name', 'email', 'phone']))
                                    @error($field)
                                        <li>{{ $message }}</li>
                                    @enderror
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <x-form.input name="name" label="Nome completo" :required="true"
                            :value="auth()->user()->name" />
                    </div>
                    <div class="sm:col-span-2">
                        <x-form.input name="email" label="E-mail" type="email" :required="true"
                            :value="auth()->user()->email" />
                    </div>
                    <x-form.input name="phone" label="Telefone" :value="auth()->user()->phone" data-mask="phone" />
                </div>

                <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Salvar Alterações
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>

        {{-- Formulário de troca de senha --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Alterar Senha</h2>

            <form method="POST" action="{{ route('admin.profile.change-password') }}" class="space-y-4">
                @csrf
                @method('PUT')

                {{-- Erros --}}
                @if ($errors->has('current_password') || $errors->has('password') || $errors->has('password_confirmation'))
                    <div
                        class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                            @foreach ($errors->keys() as $field)
                                @if (in_array($field, ['current_password', 'password', 'password_confirmation']))
                                    @error($field)
                                        <li>{{ $message }}</li>
                                    @enderror
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="space-y-4">
                    <x-form.input name="current_password" label="Senha atual" type="password" :required="true" />
                    <x-form.input name="password" label="Nova senha" type="password" :required="true" />
                    <x-form.input name="password_confirmation" label="Confirmar nova senha" type="password"
                        :required="true" />
                </div>

                <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        Alterar Senha
                    </button>
                    <a href="{{ route('admin.dashboard') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin>
