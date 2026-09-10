<x-layouts.director>
    <x-page-header :title="$item->titulo" subtitle="Editar item do catálogo" />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('director.catalogo.includes._form', ['item' => $item])
    </div>
</x-layouts.director>
