<x-layouts.admin>
    <x-slot name="title">Editar Credenciamento Docente</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Credenciamento Docente" subtitle="Atualize texto e anexos do chamamento." />
    @include('admin.professores-credenciamentos.includes._form', ['action' => 'edit', 'credenciamento' => $credenciamento])
</x-layouts.admin>
