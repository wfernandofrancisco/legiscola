<!DOCTYPE html>
<html lang="pt-BR" class="h-full antialiased">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Legiscola — Escola Legislativa')</title>
    <meta name="description" content="Plataforma de gestão para Escolas Legislativas">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,500;0,9..144,600;0,9..144,700;1,9..144,500&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        .font-display { font-family: 'Fraunces', ui-serif, Georgia, serif; }
        .font-ui { font-family: 'Outfit', system-ui, sans-serif; }
        @keyframes auth-rise {
            from { opacity: 0; transform: translateY(0.7rem); }
            to { opacity: 1; transform: translateY(0); }
        }
        .auth-animate { animation: auth-rise 0.7s cubic-bezier(0.22, 1, 0.36, 1) both; }
        .auth-d1 { animation-delay: 0.06s; }
        .auth-d2 { animation-delay: 0.14s; }
        .auth-d3 { animation-delay: 0.22s; }
        .auth-d4 { animation-delay: 0.3s; }
        .auth-d5 { animation-delay: 0.38s; }
        .auth-ink { background-color: #001823; }
        .auth-grid {
            background-image:
                linear-gradient(rgb(196 165 116 / 9%) 1px, transparent 1px),
                linear-gradient(90deg, rgb(196 165 116 / 9%) 1px, transparent 1px);
            background-size: 44px 44px;
        }
        .auth-grain {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.45'/%3E%3C/svg%3E");
            opacity: 0.07;
        }
        .auth-input:focus {
            border-color: #001823;
            box-shadow: 0 0 0 4px rgb(0 24 35 / 8%);
        }
    </style>
</head>
<body class="font-ui h-full bg-[#f4efe6] text-slate-900 antialiased selection:bg-amber-200/70 selection:text-[#001823]">
    <main class="h-full">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
