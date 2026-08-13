<x-layouts.central>
    <x-slot name="title">Tenants</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb />

    @php
        $activeFilterCount = collect([request('search'), request('status')])
            ->filter(fn($value) => filled($value))
            ->count();

        $headerItems = [
            ['title' => 'tenants', 'value' => $tenants->total(), 'color' => 'emerald'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];

        $resetFilterUrl = request()->sort_by
            ? route('central.tenants.index', ['sort_by' => request('sort_by'), 'sort_dir' => request('sort_dir')])
            : route('central.tenants.index');
    @endphp

    <x-page-header title="Tenants" subtitle="Gerencie tenants e mantenha a organização do sistema." :items="$headerItems"
        :action-href="route('central.tenants.create')" action-text="Novo Tenant" />

    {{-- Filtros com busca automática --}}
    <form id="tenant-filter-form" method="GET" action="{{ route('central.tenants.index') }}" class="mb-6">
        {{-- Preserva ordenação ao filtrar --}}
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}">

        <x-filter-panel title="Pesquisa e filtros" subtitle="Encontre tenants por nome, slug ou domínio."
            :reset-href="request()->hasAny(['search', 'status']) ? $resetFilterUrl : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar tenant" name="search" value="{{ request('search') }}"
                        autocomplete="off" placeholder="Nome, slug ou domínio..."
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' />
                </div>

                <div class="lg:col-span-2">
                    <x-form.select label="Status" name="status" placeholder="Todos os status" :options="[
                        'ativo' => 'Ativo',
                        'inativo' => 'Inativo',
                        'suspenso' => 'Suspenso',
                    ]"
                        :selected="request('status')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    {{-- Tabela (alvo do AJAX) --}}
    <div id="tenants-table-wrapper">
        @include('central.tenants.includes._table')
    </div>



    {{-- Modal de visualização do tenant (AJAX - carrega show?embedded=1) --}}
    <div id="tenant-show-modal" style="display:none;"
        class="fixed inset-0 z-50 bg-black/50 flex items-center justify-center p-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl w-full max-w-3xl max-h-[85vh] flex flex-col">
            <div
                class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-gray-700 shrink-0">
                <h2 class="font-bold text-gray-900 dark:text-white">Detalhes do Tenant</h2>
                <button onclick="closeTenantModal()"
                    class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 rounded-lg transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="tenant-show-content" class="flex-1 overflow-y-auto p-6">
                <div class="flex items-center justify-center h-32">
                    <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin">
                    </div>
                </div>
            </div>
            <div
                class="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700 shrink-0">
                <button onclick="closeTenantModal()"
                    class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">
                    Fechar
                </button>
                <a id="tenant-show-full-link" href="#"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    Abrir página completa
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/central/tenants.js') }}"></script>
    @endpush
</x-layouts.central>
