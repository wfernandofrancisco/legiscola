<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <header class="bg-white dark:bg-gray-800 shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-xl font-bold text-indigo-600 dark:text-indigo-400">
                {{ config('app.name') }}
            </a>
            <nav class="flex items-center gap-4 text-sm">
                @guest
                    <a href="{{ route('tenant.login') }}"
                        class="text-gray-600 dark:text-gray-300 hover:text-indigo-600">Entrar</a>
                    <a href="{{ route('register') }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Criar conta
                    </a>
                @else
                    <span class="text-gray-500 dark:text-gray-400">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-600 dark:text-gray-300 hover:text-red-600">
                            Sair
                        </button>
                    </form>
                @endguest
            </nav>
        </div>
    </header>

    <main>
        {{ $slot }}
    </main>

    <footer class="mt-16 py-8 text-center text-sm text-gray-400">
        &copy; {{ date('Y') }} {{ config('app.name') }}
    </footer>

</body>

</html>
