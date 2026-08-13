<form method="POST"
    action="{{ $action === 'edit' ? route('central.permissions.update', $permission) : route('central.permissions.store') }}"
    class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 px-4 py-3 mb-6">
            <svg class="h-4 w-4 shrink-0 text-red-600 dark:text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16zM8.28 7.22a.75.75 0 0 0-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 1 0 1.06 1.06L10 11.06l1.72 1.72a.75.75 0 1 0 1.06-1.06L11.06 10l1.72-1.72a.75.75 0 0 0-1.06-1.06L10 8.94 8.28 7.22Z"
                    clip-rule="evenodd" />
            </svg>
            <div class="flex-1 text-xs text-red-600 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="space-y-6">
        {{-- Fieldset 1: Dados da Permission --}}
        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Dados da Permission</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome da Permission <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name"
                        value="{{ old('name', $permission->name ?? '') }}"
                        class="w-full rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent font-mono text-sm"
                        placeholder="ex: central.empresas.create, admin.usuarios.edit, responsavel.empresa.delete"
                        required title="Use o padrão: area.recurso.acao (ex: central.empresas.create)">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Utilize o padrão: <code
                            class="bg-gray-100 dark:bg-gray-900 px-2 py-1 rounded">area.recurso.acao</code>
                    </p>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Descrição
                    </label>
                    <textarea id="description" name="description" rows="3"
                        class="w-full rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent resize-none"
                        placeholder="Descrição breve sobre o que essa permission faz">{{ old('description', $permission->description ?? '') }}</textarea>
                </div>
            </div>
        </fieldset>

        {{-- Actions --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            <div class="mb-6 text-xs text-gray-500 dark:text-gray-400">* Indica campo obrigatório</div>
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Permission' }}
                </button>

                <a href="{{ route('central.permissions.index') }}"
                    class="text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 px-4 py-2.5 rounded-lg transition">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
