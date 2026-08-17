<x-layouts.admin>
    <x-slot name="title">Editar pesquisa</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar pesquisa de satisfação" subtitle="Atualize título, descrição e perguntas." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.satisfaction-surveys.includes._form', ['action' => 'edit', 'survey' => $survey])
    </div>
</x-layouts.admin>
