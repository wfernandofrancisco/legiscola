@php
    $u = auth()->user();
    $homeRoute = $u?->tenantHomeRouteName() ?? 'app.dashboard';
@endphp
<nav x-data="{ mobileOpen: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route($homeRoute) }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route($homeRoute)" :active="request()->routeIs((string) $homeRoute)">
                        @if($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_ADMIN))
                            {{ __('Painel admin') }}
                        @elseif($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_MANAGER))
                            {{ __('Painel empresa') }}
                        @else
                            {{ __('Início') }}
                        @endif
                    </x-nav-link>
                    @if($u && ($u->hasTenantRole(\App\Models\User::TYPE_TENANT_MANAGER) || $u->hasTenantRole(\App\Models\User::TYPE_TENANT_ADMIN)) && \Illuminate\Support\Facades\Route::has('responsible.orcamento-solicitacoes.index'))
                        <x-nav-link :href="route('responsible.orcamento-solicitacoes.index')" :active="request()->routeIs('responsible.orcamento-solicitacoes.*')">
                            Orçamentos recebidos
                        </x-nav-link>
                    @endif
                    @if($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_USER))
                        <x-nav-link :href="route('app.dashboard')" :active="request()->routeIs('app.dashboard')">
                            Área do morador
                        </x-nav-link>
                        <x-nav-link :href="route('app.inscricoes.index')" :active="request()->routeIs('app.inscricoes.*')">
                            Inscrições
                        </x-nav-link>
                        @if (\Illuminate\Support\Facades\Route::has('app.orcamento-solicitacoes.index'))
                            <x-nav-link :href="route('app.orcamento-solicitacoes.index')" :active="request()->routeIs('app.orcamento-solicitacoes.*')">
                                Meus pedidos
                            </x-nav-link>
                        @endif
                    @endif
                    <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                        {{ __('Log Out') }}
                    </button>
                </form>
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>

                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-700 rounded-md shadow-lg ring-1 ring-black ring-opacity-5" style="display: none;">
                        <div class="py-1">
                            <form method="POST" action="{{ route('tenant.logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                                    {{ __('Log Out') }}
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="mobileOpen = ! mobileOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': mobileOpen, 'inline-flex': ! mobileOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! mobileOpen, 'inline-flex': mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': mobileOpen, 'hidden': ! mobileOpen}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route($homeRoute)" :active="request()->routeIs((string) $homeRoute)">
                @if($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_ADMIN))
                    Painel admin
                @elseif($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_MANAGER))
                    Painel empresa
                @else
                    Início
                @endif
            </x-responsive-nav-link>
            @if($u && ($u->hasTenantRole(\App\Models\User::TYPE_TENANT_MANAGER) || $u->hasTenantRole(\App\Models\User::TYPE_TENANT_ADMIN)) && \Illuminate\Support\Facades\Route::has('responsible.orcamento-solicitacoes.index'))
                <x-responsive-nav-link :href="route('responsible.orcamento-solicitacoes.index')" :active="request()->routeIs('responsible.orcamento-solicitacoes.*')">
                    Orçamentos recebidos
                </x-responsive-nav-link>
            @endif
            @if($u && $u->hasTenantRole(\App\Models\User::TYPE_TENANT_USER))
                <x-responsive-nav-link :href="route('app.dashboard')" :active="request()->routeIs('app.dashboard')">
                    Área do morador
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('app.inscricoes.index')" :active="request()->routeIs('app.inscricoes.*')">
                    Inscrições
                </x-responsive-nav-link>
                @if (\Illuminate\Support\Facades\Route::has('app.orcamento-solicitacoes.index'))
                    <x-responsive-nav-link :href="route('app.orcamento-solicitacoes.index')" :active="request()->routeIs('app.orcamento-solicitacoes.*')">
                        Meus pedidos
                    </x-responsive-nav-link>
                @endif
            @endif
        </div>

        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <form method="POST" action="{{ route('tenant.logout') }}">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">
                        {{ __('Log Out') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
