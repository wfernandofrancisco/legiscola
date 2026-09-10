@php
    /** @var \App\Models\User|null $director */
    $isEdit = $action === 'edit';
    $selectedUfs = $director?->directorUfs->pluck('uf')->all() ?? [];
    $formAction = $isEdit ? route('central.directors.update', $director) : route('central.directors.store');
@endphp

<form method="POST" action="{{ $formAction }}" class="space-y-8">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Dados do diretor</legend>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 pb-6 border-b border-gray-200 dark:border-gray-700">
            <x-form.input name="name" label="Nome" required :value="$director?->name ?? old('name')" />
            <x-form.input name="email" label="E-mail" type="email" required :value="$director?->email ?? old('email')" />
            <x-form.input name="phone" label="Telefone" :value="$director?->phone ?? old('phone')" />

            @if ($isEdit)
                <x-form.select name="status" label="Situação" required
                    :options="['ativo' => 'Ativo', 'inativo' => 'Inativo']"
                    :selected="$director?->status ?? old('status')" />
            @endif
        </div>
    </fieldset>

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Abrangência</legend>
        <p class="mb-6 text-sm text-gray-500 dark:text-gray-400">
            O diretor enxerga todas as câmaras cadastradas nestas UFs. Clientes novos na região aparecem
            automaticamente, sem precisar vincular um a um.
        </p>

        <div class="pb-6 border-b border-gray-200 dark:border-gray-700">
            <x-form.select name="ufs[]" label="UFs de abrangência" multiple required size="10"
                :options="\App\Support\BrazilianStates::options()"
                :selected="old('ufs', $selectedUfs)"
                hint="Segure Ctrl (ou Cmd) para selecionar mais de uma." />
        </div>
    </fieldset>

    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('central.directors.index') }}"
            class="rounded-lg px-4 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800">
            Cancelar
        </a>
        <button type="submit"
            class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">
            {{ $isEdit ? 'Salvar alterações' : 'Criar diretor' }}
        </button>
    </div>
</form>
