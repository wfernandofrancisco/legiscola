<x-layouts.admin>
    <x-slot name="title">Novo Credenciamento Docente</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Novo Credenciamento Docente" subtitle="Cadastre texto e anexos para o portal público." />
    @include('admin.professores-credenciamentos.includes._form', ['action' => 'create', 'credenciamento' => null])
</x-layouts.admin>
