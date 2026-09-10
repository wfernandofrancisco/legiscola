<x-layouts.director>
    <x-page-header title="Novo item do catálogo"
        subtitle="Crie o curso ou a palestra aqui; as aulas são montadas na próxima tela." />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('director.catalogo.includes._form', ['item' => null])
    </div>
</x-layouts.director>
