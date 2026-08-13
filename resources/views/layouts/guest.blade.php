<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Legiscola - SaaS de Escola Legislativa')</title>

    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Meta tags -->
    <meta name="description" content="Plataforma SaaS de gestão integrada para múltiplos clientes">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-white text-gray-900">
    <!-- Content -->
    <main>
        @yield('content')
    </main>

    <!-- Scripts -->
    @stack('scripts')
</body>

</html>
