<x-layouts.admin>
    <x-slot name="title">Eventos</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Eventos" subtitle="Gerencie eventos e palestras." :action-href="route('admin.eventos.create')" action-text="Novo Evento" />

    <form method="GET" action="{{ route('admin.eventos.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa" subtitle="Busque por título."
            :reset-href="request()->has('search') ? route('admin.eventos.index') : null">
            <x-form.input label="Buscar evento" name="search" value="{{ request('search') }}" />
        </x-filter-panel>
    </form>

    @include('admin.events.includes._table', compact('events'))
</x-layouts.admin>
