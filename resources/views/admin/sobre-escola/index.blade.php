<x-layouts.admin>
    <x-slot name="title">Sobre a Escola</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Sobre a Escola" subtitle="Gerencie os textos institucionais e eixos temáticos."
        :action-href="$firstItem ? route('admin.sobre-escola.edit', $firstItem) : route('admin.sobre-escola.create')"
        :action-text="$firstItem ? 'Editar registro' : 'Novo registro'" />

    @include('admin.sobre-escola.includes._table', ['items' => $items])
</x-layouts.admin>
