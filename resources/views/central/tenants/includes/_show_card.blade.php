<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Dados principais --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="bg-gray-50 dark:bg-gray-700 rounded-xl p-5">
            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Dados do
                Tenant</h3>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Nome</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $tenant->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Slug</dt>
                    <dd class="font-mono text-gray-700 dark:text-gray-300">{{ $tenant->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Domínio</dt>
                    <dd class="text-gray-700 dark:text-gray-300">{{ $tenant->domain ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400 mb-0.5">Status</dt>
                    <dd>
                        @if ($tenant->status === 'ativo')
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Ativo
                            </span>
                        @elseif($tenant->status === 'inativo')
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Inativo
                            </span>
                        @elseif($tenant->status === 'suspenso')
                            <span
                                class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-400">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Suspenso
                            </span>
                        @endif
                    </dd>
                </div>
                @if ($tenant->trial_ends_at)
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Fim do Teste</dt>
                        <dd class="text-gray-700 dark:text-gray-300">{{ $tenant->trial_ends_at->format('d/m/Y H:i') }}
                        </dd>
                    </div>
                @endif
                @if ($tenant->subscription_expires_at)
                    <div>
                        <dt class="text-xs text-gray-400 mb-0.5">Expiração</dt>
                        <dd class="text-gray-700 dark:text-gray-300">
                            {{ $tenant->subscription_expires_at->format('d/m/Y H:i') }}</dd>
                    </div>
                @endif
            </dl>
            @if ($tenant->description)
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <dt class="text-xs text-gray-400 mb-1">Descrição</dt>
                    <dd class="text-sm text-gray-700 dark:text-gray-300">{{ $tenant->description }}</dd>
                </div>
            @endif
        </div>

        {{-- Dados jurídicos --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Dados cadastrais</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div class="col-span-2">
                    <dt class="text-xs text-gray-400">Razão social</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $tenant->razao_social ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">CNPJ</dt>
                    <dd class="font-mono text-gray-700 dark:text-gray-300">{{ $tenant->cnpj ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-gray-400">Status cadastro</dt>
                    <dd class="capitalize">{{ $tenant->cadastro_status ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Usuários --}}
        @if ($tenant->users->count() > 0)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                    Usuários ({{ $tenant->users->count() }})
                </h3>
                <div class="space-y-3">
                    @foreach ($tenant->users->take(5) as $user)
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900 flex items-center justify-center text-sm font-bold text-purple-600 dark:text-purple-400">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->name }}
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                @if ($user->status === 'ativo')
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-400">
                                        Ativo
                                    </span>
                                @elseif($user->status === 'pendente')
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-yellow-100 dark:bg-yellow-900 text-yellow-700 dark:text-yellow-400">
                                        Pendente
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                        Inativo
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                    @if ($tenant->users->count() > 5)
                        <p class="text-xs text-gray-500 dark:text-gray-400 text-center">
                            +{{ $tenant->users->count() - 5 }} usuários adicionais
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Estatísticas --}}
    <div class="space-y-4">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Estatísticas</h3>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>

                            <p class="text-sm text-gray-500 dark:text-gray-400">Orçamentos</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ $tenant->users->where('status', 'ativo')->count() }}</p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Usuários Ativos</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500">Total: {{ $tenant->users->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900 flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            </p>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Orçamentos</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ações rápidas --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ações Rápidas</h3>
            <div class="space-y-2">
                @if ($tenant->status !== 'ativo')
                    <form method="POST" action="{{ route('central.tenants.activate', $tenant) }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-3 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 rounded-lg transition">
                            Ativar Tenant
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('central.tenants.deactivate', $tenant) }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-3 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 rounded-lg transition">
                            Desativar Tenant
                        </button>
                    </form>
                @endif

                @if ($tenant->status !== 'suspenso')
                    <form method="POST" action="{{ route('central.tenants.suspend', $tenant) }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                            Suspender Tenant
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
