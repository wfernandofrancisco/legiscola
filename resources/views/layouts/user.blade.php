@extends('layouts.app')

@section('sidebar')
    <!-- Sidebar Navigation - Usuário Comum -->
    <nav class="w-64 bg-blue-900 text-white shadow-lg overflow-y-auto">
        <div class="p-6 border-b border-blue-700">
            <h1 class="text-2xl font-bold">👤 Meu Painel</h1>
            <p class="text-xs text-blue-300 mt-2">{{ auth()->user()->tenant->name ?? 'Tenant' }}</p>
        </div>

        <ul class="p-4 space-y-2">
            <li>
                <a href="{{ route('app.dashboard') }}"
                    class="block px-4 py-2 rounded-lg hover:bg-blue-800 transition @active('app.dashboard', 'bg-blue-600')">
                    📊 Dashboard
                </a>
            </li>
            <li>
                <a href="#"
                    class="block px-4 py-2 rounded-lg hover:bg-blue-800 transition text-blue-400 cursor-not-allowed">
                    💰 Meus Orçamentos (Em breve)
                </a>
            </li>
            <li>
                <a href="#"
                    class="block px-4 py-2 rounded-lg hover:bg-blue-800 transition text-blue-400 cursor-not-allowed">
                    👤 Meu Perfil (Em breve)
                </a>
            </li>
            <li>
                <a href="#"
                    class="block px-4 py-2 rounded-lg hover:bg-blue-800 transition text-blue-400 cursor-not-allowed">
                    📊 Relatórios (Em breve)
                </a>
            </li>
        </ul>
    </nav>
@endsection

@section('header')
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'Meu Painel')</h1>
                <p class="text-sm text-gray-600">@yield('subtitle', 'Bem-vindo de volta')</p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 hover:text-red-800">Sair</button>
                </form>
            </div>
        </div>
    </div>
@endsection