<x-layouts.central>
    <x-slot name="title">Dashboard Geral</x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        @php
        $cards = [
            ['label' => 'Total de Clientes', 'value' => $stats['total_tenants'], 'color' => 'indigo'],
            ['label' => 'Clientes Ativos', 'value' => $stats['active_tenants'], 'color' => 'green'],
            ['label' => 'Cadastros Pendentes', 'value' => $stats['pending_cadastro'], 'color' => 'yellow'],
            ['label' => 'Total de Usuários', 'value' => $stats['total_users'], 'color' => 'blue'],
            ['label' => 'Total de Orçamentos', 'value' => $stats['total_budgets'], 'color' => 'purple'],
        ];
        @endphp

        @foreach($cards as $card)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $card['label'] }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-1">{{ $card['value'] }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800 dark:text-gray-200">Clientes recentes</h3>
            <a href="{{ route('central.tenants.index') }}"
               class="text-sm text-indigo-600 hover:underline">Ver todos</a>
        </div>
        <div class="divide-y divide-gray-100 dark:divide-gray-700">
            @forelse($recent_tenants as $tenant)
            <div class="flex items-center justify-between px-6 py-4">
                <div>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $tenant->display_name }}</p>
                    <p class="text-sm text-gray-500">{{ $tenant->cnpj ?? '—' }} · {{ $tenant->cidade ?? '—' }}/{{ $tenant->estado ?? '—' }}</p>
                </div>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $tenant->cadastro_status === 'ativo' ? 'bg-green-100 text-green-800' :
                       ($tenant->cadastro_status === 'pendente' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                    {{ ucfirst($tenant->cadastro_status ?? '—') }}
                </span>
            </div>
            @empty
            <p class="px-6 py-8 text-center text-gray-400">Nenhum cliente cadastrado ainda.</p>
            @endforelse
        </div>
    </div>
</x-layouts.central>
