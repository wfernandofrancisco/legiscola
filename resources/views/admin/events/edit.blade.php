<x-layouts.admin>
    <x-slot name="title">Editar Evento</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Evento" subtitle="Evento de um dia: vagas no evento. Várias sessões: vagas em cada palestra." />
    @include('admin.events.includes._catalog-content', ['event' => $event])
    @include('admin.events.includes._form', ['action' => 'edit', 'event' => $event])
    @include('admin.events.includes._enrollments', ['event' => $event])
</x-layouts.admin>
