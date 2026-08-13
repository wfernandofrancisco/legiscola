<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth antialiased">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('meta_description', config('app.name').' — plataforma para Escolas Legislativas: portal, gestão, certificados e LGPD.')">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('meta')
    <style>
        /* Paleta inspirada em sites gov-tech (ex.: 1Doc): sempre legível em tema claro */
        .font-display { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .font-ui { font-family: 'Outfit', system-ui, sans-serif; }
        @keyframes pub-rise {
            from { opacity: 0; transform: translateY(0.75rem); }
            to { opacity: 1; transform: translateY(0); }
        }
        .pub-animate { animation: pub-rise 0.6s ease-out both; }
        .pub-d1 { animation-delay: 0.05s; }
        .pub-d2 { animation-delay: 0.1s; }
        .pub-d3 { animation-delay: 0.15s; }
        .pub-d4 { animation-delay: 0.2s; }
        .pub-d5 { animation-delay: 0.25s; }
        .pub-hero-bg {
            background: linear-gradient(165deg, #e0f2fe 0%, #f0f9ff 38%, #ffffff 55%, #ecfdf5 100%);
        }
        .pub-soft-grid {
            background-image: linear-gradient(rgb(14 116 144 / 19%) 1px, transparent 1px), linear-gradient(90deg, rgb(178 178 178 / 21%) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .bg-card-header{
            background-color:#001823;
        }
    </style>
</head>
<body class="font-ui min-h-full bg-sky-50 text-slate-900 antialiased selection:bg-emerald-200 selection:text-slate-900">
    @yield('content')
    @stack('scripts')
</body>
</html>
