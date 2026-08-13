<x-layouts.admin>
    <x-slot name="title">Noticias</x-slot>

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $headerItems = [
            ['title' => 'total', 'value' => $noticias->total(), 'color' => 'indigo'],
            ['title' => 'ativas', 'value' => $noticias->getCollection()->where('ativo', true)->count(), 'color' => 'emerald'],
            ['title' => 'destaque', 'value' => $noticias->getCollection()->where('is_destaque', true)->count(), 'color' => 'amber'],
        ];

        $resetFilterUrl = route('admin.noticias.index');
    @endphp

    <x-page-header title="Noticias"
        subtitle="Publique e gerencie comunicados da sua empresa."
        :items="$headerItems"
        action-href="{{ route('admin.noticias.create') }}"
        action-text="Nova noticia" />

    <form id="noticia-filter-form" method="GET" action="{{ route('admin.noticias.index') }}" class="mb-6">
        <x-filter-panel title="Filtros" subtitle="Busque por titulo, subtitulo ou tags."
            :reset-href="request()->has('search') || request()->has('ativo') ? $resetFilterUrl : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                <x-form.input label="Buscar" name="search" value="{{ request('search') }}"
                    autocomplete="off" placeholder="Titulo, subtitulo ou tag..." />

                <x-form.select
                    label="Status"
                    name="ativo"
                    :selected="(string) request('ativo', '')"
                    :options="[
                        '' => 'Todos',
                        '1' => 'Ativo',
                        '0' => 'Inativo',
                    ]" />
            </div>
        </x-filter-panel>
    </form>

    @include('admin.noticias.includes._table', compact('noticias'))

    @push('scripts')
        <script>
            let noticiaSearchTimeout;
            document.getElementById('noticia-filter-form').addEventListener('input', function(e) {
                if (e.target.name === 'search') {
                    clearTimeout(noticiaSearchTimeout);
                    noticiaSearchTimeout = setTimeout(() => {
                        this.submit();
                    }, 500);
                }
            });
        </script>
    @endpush
</x-layouts.admin>
