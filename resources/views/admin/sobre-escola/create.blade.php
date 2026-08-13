<x-layouts.admin>
    <x-slot name="title">Novo Conteúdo - Sobre a Escola</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Novo Conteúdo - Sobre a Escola"
        subtitle="Cadastre informações institucionais e eixos temáticos." />
    @include('admin.sobre-escola.includes._form', ['action' => 'create', 'item' => null])
</x-layouts.admin>
