<x-layouts.admin>
    <x-slot name="title">Nova pesquisa</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar pesquisa de satisfação" subtitle="Defina a descrição e as perguntas (campo livre ou opções)." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.satisfaction-surveys.includes._form', ['action' => 'create', 'survey' => null])
    </div>
</x-layouts.admin>
