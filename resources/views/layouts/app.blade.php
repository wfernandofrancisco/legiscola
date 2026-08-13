<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Legiscola')</title>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Meta tags -->
    <meta name="description" content="Plataforma SaaS de gestão integrada">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @stack('styles')
</head>

<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="text-2xl font-bold text-blue-600">
                    🚀 Legiscola
                </div>

                @if (auth()->check())
                    <div class="flex items-center space-x-4">
                        <!-- Navegação baseada no role -->
                        @if (auth()->user()->hasTenantRole(\App\Models\User::TYPE_TENANT_ADMIN) || auth()->user()->hasRole('admin'))
                            <!-- Navegação Admin -->
                            <a href="{{ route('admin.dashboard') }}"
                                class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                🏢 Admin
                            </a>
                        @elseif(auth()->user()->hasTenantRole(\App\Models\User::TYPE_TENANT_MANAGER) || auth()->user()->hasRole('manager'))
                            <a href="{{ route('professor.dashboard') }}"
                                class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                Painel Professor
                            </a>
                            <a href="{{ route('app.dashboard') }}"
                                class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                Painel Aluno
                            </a>
                        @else
                            <!-- Navegação Usuário Comum -->
                            <a href="{{ route('app.dashboard') }}"
                                class="text-gray-700 hover:text-blue-600 font-medium transition-colors">
                                📊 Meu Painel
                            </a>
                        @endif

                        <span class="text-gray-700">
                            👤 {{ auth()->user()->name }}
                        </span>
                        <form action="{{ route('tenant.logout') }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="text-red-600 hover:text-red-700 font-semibold">
                                Sair
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Flash Messages -->
        @if ($errors->any())
            <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-red-800 font-semibold">Erros encontrados:</p>
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

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <p>&copy; {{ date('Y') }} DesenvolveCity. Todos os direitos reservados.</p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
