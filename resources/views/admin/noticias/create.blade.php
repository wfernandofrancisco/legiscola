<x-layouts.admin>
    <x-slot name="title">Nova noticia</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-subpage-header title="Nova noticia" subtitle="Crie uma publicacao para usuarios do tenant." />

    @include('admin.noticias.includes._form', ['action' => 'create'])
</x-layouts.admin>
