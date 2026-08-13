<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Painel Central - Legiscola')</title>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Meta tags -->
    <meta name="description" content="Painel Central de Administrativo">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>

<body class="bg-gray-100">
    <!-- Sidebar Navigation -->
    <div class="flex h-screen">
        <!-- Sidebar -->
        <nav class="w-64 bg-slate-900 text-white shadow-lg overflow-y-auto">
            <div class="p-6 border-b border-slate-700">
                <h1 class="text-2xl font-bold">🔐 Central</h1>
                <p class="text-xs text-slate-400 mt-2">Super Admin</p>
            </div>

            <ul class="p-4 space-y-2">
                <li>
                    <a href="{{ route('central.dashboard') }}"
                        class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('central.dashboard', 'bg-blue-600')">
                        📊 Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('central.roles.index') }}"
                        class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('central.roles*', 'bg-blue-600')">
                        👥 Roles
                    </a>
                </li>
                <li>
                    <a href="{{ route('central.permissions.index') }}"
                        class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('central.permissions*', 'bg-blue-600')">
                        🔑 Permissions
                    </a>
                </li>
                <li>
                    <a href="{{ route('central.tenants.index') }}"
                        class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('central.tenants*', 'bg-blue-600')">
                        🏢 Clientes
                    </a>
                </li>

                <li class="border-t border-slate-700 my-4 pt-4">
                    <form action="{{ route('central.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2 rounded-lg hover:bg-red-600 transition text-red-400 hover:text-white">
                            🚪 Sair
                        </button>
                    </form>
                </li>
            </ul>
        </nav>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <nav class="bg-white shadow-sm border-b border-gray-200">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">@yield('page-title', 'Painel Central')</h1>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-700 text-sm">
                            👤 {{ auth()->user()->name }} (Super Admin)
                        </span>
                    </div>
                </div>
            </nav>

            <!-- Content Area -->
            <main class="flex-1 overflow-auto bg-gray-50 p-6">
                <!-- Flash Messages -->
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800 font-semibold">⚠️ Erros encontrados:</p>
                        <ul class="text-red-700 text-sm mt-2">
                            @foreach ($errors->all() as $error)
                                <li>• {{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('success'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-green-800 font-semibold">✅ {{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <p class="text-red-800 font-semibold">❌ {{ session('error') }}</p>
                    </div>
                @endif

                @if (session('info'))
                    <div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <p class="text-blue-800 font-semibold">ℹ️ {{ session('info') }}</p>
                    </div>
                @endif

                <!-- Page Content -->
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
