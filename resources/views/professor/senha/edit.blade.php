<x-layouts.professor>
    <x-slot name="title">Trocar senha</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="max-w-xl">
        <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Nova senha</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Informe a senha atual e escolha uma nova senha forte.</p>

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

            <form method="POST" action="{{ route('professor.senha.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <x-form.input name="current_password" label="Senha atual" type="password" :required="true" autocomplete="current-password" />
                <x-form.input name="password" label="Nova senha" type="password" :required="true" autocomplete="new-password" />
                <x-form.input name="password_confirmation" label="Confirmar nova senha" type="password" :required="true" autocomplete="new-password" />

                <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                        Alterar senha
                    </button>
                    <a href="{{ route('professor.perfil.edit') }}"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
                        Voltar ao perfil
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.professor>
