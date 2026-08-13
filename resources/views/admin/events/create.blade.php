<x-layouts.admin>
    <x-slot name="title">Novo Evento</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Evento" subtitle="Defina os dados do evento." />
    @include('admin.events.includes._form', ['action' => 'create', 'event' => null])
</x-layouts.admin>
