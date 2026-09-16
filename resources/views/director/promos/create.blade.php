<x-layouts.director>
    <x-page-header title="Novo aviso" subtitle="Popup de promoção ou novidade para o admin da câmara, com capa e texto."
        :action-href="route('diretor.promos.index')" action-text="Voltar" />

    @include('director.promos.includes._form', ['promo' => null, 'itens' => $itens, 'camaras' => $camaras])
</x-layouts.director>
