<x-layouts.admin>
    <x-slot name="title">Editar Conteúdo - Sobre a Escola</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Conteúdo - Sobre a Escola"
        subtitle="Atualize informações institucionais e eixos temáticos." />
    @include('admin.sobre-escola.includes._form', ['action' => 'edit', 'item' => $item])
</x-layouts.admin>
