<x-layouts.director>
    <x-page-header title="Editar aviso" subtitle="Capa, título e texto entram no popup do admin. Salvar de novo faz o aviso reaparecer para quem já tinha fechado."
        :action-href="route('diretor.promos.index')" action-text="Voltar" />

    @include('director.promos.includes._form', ['promo' => $promo, 'itens' => $itens, 'camaras' => $camaras])
</x-layouts.director>
