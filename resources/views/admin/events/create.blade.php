<x-layouts.admin>
    <x-slot name="title">Novo Evento</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Evento" subtitle="Evento de um dia: vagas no evento. Várias sessões: vagas em cada palestra." />
    @include('admin.events.includes._form', ['action' => 'create', 'event' => null])
</x-layouts.admin>
