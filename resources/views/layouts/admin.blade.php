@extends('layouts.app')

@section('sidebar')
    <!-- Sidebar Navigation -->
    <nav class="w-64 bg-slate-900 text-white shadow-lg overflow-y-auto">
        <div class="p-6 border-b border-slate-700">
            <h1 class="text-2xl font-bold">🏢 Admin</h1>
            <p class="text-xs text-slate-400 mt-2">{{ auth()->user()->tenant->name ?? 'Tenant' }}</p>
        </div>

        <ul class="p-4 space-y-2">
            <li>
                <a href="{{ route('admin.dashboard') }}"
                    class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('admin.dashboard', 'bg-blue-600')">
                    📊 Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}"
                    class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition @active('admin.users*', 'bg-blue-600')">
                    👥 Usuários
                </a>
            </li>
            <li>
                <a href="#"
                    class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition text-slate-500 cursor-not-allowed">
                    💰 Orçamentos (Em breve)
                </a>
            </li>
            <li>
                <a href="#"
                    class="block px-4 py-2 rounded-lg hover:bg-slate-800 transition text-slate-500 cursor-not-allowed">
                    🏢 Empresas (Em breve)
                </a>
            </li>
        </ul>
    </nav>
@endsection

@section('header')
    <div class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">@yield('title', 'Admin Panel')</h1>
                <p class="text-sm text-gray-600">@yield('subtitle', 'Gerencie seu tenant')</p>
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
