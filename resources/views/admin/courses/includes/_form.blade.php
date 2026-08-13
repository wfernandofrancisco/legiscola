@php
    $course = $course ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST" action="{{ $action === 'edit' ? route('admin.cursos.update', $course) : route('admin.cursos.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Novo Curso</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Nome" :required="true" :value="$course?->name ?? old('name')" />
            <x-form.input name="workload_hours" label="Carga Horária (h)" type="number" :required="true"
                :value="$course?->workload_hours ?? old('workload_hours')" />
            <x-form.select name="status" label="Status" :required="true" :selected="$course?->status ?? old('status', 'rascunho')" :options="[
                'rascunho' => 'Rascunho',
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
                'arquivado' => 'Arquivado',
            ]" />
            <div class="md:col-span-2">
                <x-form.input name="description" label="Descrição" :value="$course?->description ?? old('description')" />
            </div>
        </div>
    </fieldset>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Curso' }}
        </button>
        <a href="{{ route('admin.cursos.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>
