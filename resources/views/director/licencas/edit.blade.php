<x-layouts.director>
    <x-page-header :title="$licenca->catalogItem->titulo"
        :subtitle="'Licença de ' . $licenca->tenant->display_name" />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('director.licencas.includes._form', ['licenca' => $licenca])
    </div>

    @include('director.licencas.includes._select2-assets')
</x-layouts.director>
