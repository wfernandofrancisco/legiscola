<x-layouts.central>
    <x-slot name="title">Roles / Papéis</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    @php
        $activeFilterCount = collect([request('search')])
            ->filter(fn($value) => filled($value))
            ->count();

        $headerItems = [
            ['title' => 'roles', 'value' => $roles->total(), 'color' => 'emerald'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];

        $resetFilterUrl = route('central.roles.index');
    @endphp

    <x-page-header title="Roles / Papéis" subtitle="Gerenciar roles e permissões de acesso do sistema" :items="$headerItems"
        :action-href="route('central.roles.create')" action-text="Novo Role" />

    {{-- Filtros com busca automática --}}
    <form id="role-filter-form" method="GET" action="{{ route('central.roles.index') }}" class="mb-6">
        {{-- Preserva ordenação ao filtrar --}}
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}">

        <x-filter-panel title="Pesquisa" subtitle="Encontre roles por nome." :reset-href="request()->has('search') ? $resetFilterUrl : null">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <x-form.input label="Buscar role" name="search" value="{{ request('search') }}" autocomplete="off"
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' />
                </div>
            </div>
        </x-filter-panel>
    </form>

    {{-- Tabela (alvo do AJAX) --}}
    <div id="roles-table-wrapper">
        @include('central.roles.includes._table', compact('roles'))
    </div>

    @push('scripts')
        <script src="{{ asset('js/central/roles.js') }}"></script>
    @endpush
</x-layouts.central>
