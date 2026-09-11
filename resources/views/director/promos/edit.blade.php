<x-layouts.director>
    <x-page-header title="Editar aviso" subtitle="Salvar de novo faz o aviso reaparecer para quem já tinha fechado."
        :action-href="route('diretor.promos.index')" action-text="Voltar" />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('director.promos.includes._form', ['promo' => $promo, 'itens' => $itens, 'camaras' => $camaras])
    </div>
</x-layouts.director>
