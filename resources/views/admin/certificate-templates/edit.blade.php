<x-layouts.admin>
    <x-slot name="title">Editar Template</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Template" subtitle="Atualize os dados do template." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.certificate-templates.includes._form', ['action' => 'edit', 'certificateTemplate' => $certificateTemplate])
    </div>
</x-layouts.admin>
