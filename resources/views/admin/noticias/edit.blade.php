<x-layouts.admin>
    <x-slot name="title">Editar noticia</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-subpage-header title="Editar noticia" subtitle="Atualize dados e galeria da noticia." />

    @include('admin.noticias.includes._form', ['action' => 'edit', 'noticia' => $noticia])
</x-layouts.admin>
