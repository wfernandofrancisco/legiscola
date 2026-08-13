<form method="POST"
    action="{{ $action === 'edit' ? route('central.roles.update', $role) : route('central.roles.store') }}"
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
        {{-- Fieldset 1: Dados do Role --}}
        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Dados do Role</legend>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Nome do Role <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name', $role->name ?? '') }}"
                        class="w-full rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="ex: admin, moderator, user" required>
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Descrição
                    </label>
                    <input type="text" id="description" name="description"
                        value="{{ old('description', $role->description ?? '') }}"
                        class="w-full rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Descrição breve do role">
                </div>

                <div class="sm:col-span-2">
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Tipo <span class="text-red-500">*</span>
                    </label>
                    <select id="type" name="type"
                        class="w-full rounded-lg bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 px-4 py-2 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" required>
                        <option value="central" {{ (old('type', $role->type ?? 'tenant') === 'central') ? 'selected' : '' }}>Central</option>
                        <option value="tenant" {{ (old('type', $role->type ?? 'tenant') === 'tenant') ? 'selected' : '' }}>Tenant</option>
                    </select>
                </div>
        </fieldset>

        {{-- Fieldset 2: Permissions --}}
        <fieldset>
            <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-5">Permissions</legend>
            <div class="pb-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($permissions as $permission)
                        <label
                            class="flex items-start gap-3 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-700 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-600 transition has-checked:border-indigo-500 has-checked:bg-indigo-50 has-checked:dark:bg-indigo-900">
                            <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                @checked(isset($rolePermissions) && in_array($permission->id, $rolePermissions))
                                class="mt-1 w-4 h-4 text-indigo-600 rounded focus:ring-indigo-500">

                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ $permission->name }}
                                </div>
                                @if ($permission->description)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                        {{ $permission->description }}
                                    </div>
                                @endif
                            </div>
                        </label>
                    @empty
                        <div class="col-span-3 text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400 text-sm">
                                Nenhuma permission encontrada. <a href="{{ route('central.permissions.create') }}"
                                    class="text-indigo-600 hover:underline">Criar uma permission</a>
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </fieldset>

        {{-- Actions --}}
        <div class="pt-6 border-t border-gray-200 dark:border-gray-700">
            {{-- Legenda de campos obrigatórios --}}
            <div class="mb-6 text-xs text-gray-600 dark:text-gray-400 flex items-center gap-2">
                <span class="text-red-500 font-bold">*</span>
                <span>Indica campo obrigatório</span>
            </div>
            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 active:bg-indigo-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar
                </button>
                <a href="{{ $action === 'create' ? route('central.roles.index') : route('central.roles.show', $role) }}"
                    class="rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    Cancelar
                </a>
            </div>
        </div>
    </div>
</form>
