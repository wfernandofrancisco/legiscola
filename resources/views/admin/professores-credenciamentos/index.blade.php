<x-layouts.admin>
    <x-slot name="title">Docentes / Credenciamento</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-page-header title="Docentes / Credenciamento" subtitle="Gerencie os textos e anexos do chamamento público."
        :action-href="route('admin.professores-credenciamentos.create')" action-text="Novo conteúdo" />

    <form method="GET" action="{{ route('admin.professores-credenciamentos.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa" subtitle="Busque por título ou texto."
            :reset-href="request()->has('search') ? route('admin.professores-credenciamentos.index') : null">
            <x-form.input label="Buscar" name="search" value="{{ request('search') }}"
                placeholder="Digite título ou trecho..." />
            <div class="mt-4">
                <button type="submit"
                    class="inline-flex rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
                    Filtrar
                </button>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.professores-credenciamentos.includes._table', ['credenciamentos' => $credenciamentos])
</x-layouts.admin>
