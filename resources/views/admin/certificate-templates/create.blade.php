<x-layouts.admin>
    <x-slot name="title">Novo Template</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Template" subtitle="Cadastre o template de certificado." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.certificate-templates.includes._form', ['action' => 'create', 'certificateTemplate' => null])
    </div>
</x-layouts.admin>
