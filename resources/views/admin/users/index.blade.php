<x-layouts.admin>
    <x-slot name="title">Usuários</x-slot>

    {{-- Breadcrumb --}}
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('status'), request('user_type')])
            ->filter(fn($value) => filled($value))
            ->count();

        $headerItems = [
            ['title' => 'usuários', 'value' => $users->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];

        $resetFilterUrl = request()->sort_by
            ? route('admin.users.index', ['sort_by' => request('sort_by'), 'sort_dir' => request('sort_dir')])
            : route('admin.users.index');
    @endphp

    @php
        $actionHref = null;
        $actionText = null;
        if (auth()->user()->can('create', \App\Models\User::class)) {
            $actionHref = route('admin.users.create');
            $actionText = 'Novo Usuário';
        }
    @endphp

    <x-page-header title="Usuários" subtitle="Gerencie os usuários do seu tenant." :items="$headerItems"
        :action-href="$actionHref" :action-text="$actionText" />

    <!-- Filtros -->
    <form id="user-filter-form" method="GET" action="{{ route('admin.users.index') }}" class="mb-6">
        {{-- Preserva ordenação ao filtrar --}}
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_dir" value="{{ request('sort_dir', 'asc') }}">

        <x-filter-panel title="Pesquisa e filtros" subtitle="Encontre usuários por nome ou email."
            :reset-href="request()->hasAny(['search', 'status', 'user_type']) ? $resetFilterUrl : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-6">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar usuário" name="search" value="{{ request('search') }}"
                        autocomplete="off" placeholder="Nome ou email..."
                        icon='<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>' />
                </div>

                <div class="lg:col-span-1">
                    <x-form.select label="Status" name="status" placeholder="Todos os status" :options="[
                        'ativo' => 'Ativo',
                        'inativo' => 'Inativo',
                        'pendente' => 'Pendente',
                    ]"
                        :selected="request('status')" />
                </div>

                <div class="lg:col-span-1">
                    <x-form.select label="Tipo" name="user_type" placeholder="Todos os tipos" :options="[
                        'tenant_admin' => 'Administrador',
                        'tenant_manager' => 'Gerente',
                        'tenant_responsible' => 'Docente',
                        'tenant_user' => 'Aluno',
                    ]"
                        :selected="request('user_type')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    <!-- Tabela -->
    <div id="users-table-wrapper">
        @include('admin.users.includes._table', compact('users'))
    </div>

    @push('scripts')
        <script src="{{ asset('js/admin/users.js') }}"></script>
    @endpush
</x-layouts.admin>
