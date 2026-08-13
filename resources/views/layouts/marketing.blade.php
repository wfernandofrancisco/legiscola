<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', 'Legiscola — plataforma para Escolas Legislativas: portal público, gestão acadêmica, certificados e conformidade com a LGPD.')">
    <title>@yield('title', config('app.name').' — Escola Legislativa digital')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('meta')
</head>
<body class="min-h-full bg-stone-50 pb-8 text-stone-900 selection:bg-amber-200/80 selection:text-stone-900 dark:bg-stone-950 dark:text-stone-100 dark:selection:bg-amber-900/40 dark:selection:text-amber-100">
    @yield('content')
    @stack('scripts')
</body>
</html>
