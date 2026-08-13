<x-layouts.central>
    <x-slot name="title">Permissões</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    @php
        $activeFilterCount = collect([request('search')])
            ->filter(fn($value) => filled($value))
            ->count();

        $headerItems = [
            ['title' => 'permissões', 'value' => $permissions->total(), 'color' => 'emerald'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];

        $resetFilterUrl = route('central.permissions.index');
    @endphp

    <x-page-header title="Permissões" subtitle="Gerenciar permissões do sistema" :items="$headerItems" :action-href="route('central.permissions.create')"
        action-text="Nova Permissão" />

    {{-- Filtros com busca automática --}}
    <form id="permission-filter-form" method="GET" action="{{ route('central.permissions.index') }}" class="mb-6">
        {{-- Preserva ordenação ao filtrar --}}
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}">

        <x-filter-panel title="Pesquisa" subtitle="Encontre permissões por nome." :reset-href="request()->has('search') ? $resetFilterUrl : null">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <x-form.input label="Buscar permissão" name="search" value="{{ request('search') }}"
                        autocomplete="off"
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' />
                </div>
            </div>
        </x-filter-panel>
    </form>

    {{-- Tabela (alvo do AJAX) --}}
    <div id="permissions-table-wrapper">
        @include('central.permissions.includes._table', compact('permissions'))
    </div>

    @push('scripts')
        <script src="{{ asset('js/central/permissions.js') }}"></script>
    @endpush
</x-layouts.central>
