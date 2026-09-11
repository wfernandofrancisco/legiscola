<x-layouts.director>
    <x-page-header title="Novo aviso" subtitle="Banner de promoção ou novidade para o admin da câmara."
        :action-href="route('diretor.promos.index')" action-text="Voltar" />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('director.promos.includes._form', ['promo' => null, 'itens' => $itens, 'camaras' => $camaras])
    </div>
</x-layouts.director>
