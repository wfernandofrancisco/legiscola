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
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Newsreader:ital,opsz,wght@0,6..72,500;1,6..72,400;1,6..72,600&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <style>
        .font-display { font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif; }
        .font-serif { font-family: 'Newsreader', ui-serif, Georgia, serif; }
        .font-ui { font-family: 'Bricolage Grotesque', ui-sans-serif, system-ui, sans-serif; }

        @keyframes blob-a {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(6%, -8%) scale(1.12); }
            66% { transform: translate(-4%, 5%) scale(0.92); }
        }
        @keyframes blob-b {
            0%, 100% { transform: translate(0, 0) scale(1); }
            40% { transform: translate(-8%, 6%) scale(1.08); }
            70% { transform: translate(5%, -4%) scale(1.18); }
        }
        @keyframes float-y {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        @keyframes float-y-slow {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-14px) rotate(2deg); }
        }
        @keyframes shine {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }
        @keyframes rise {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes sun-pulse {
            0%, 100% { opacity: 0.55; transform: scale(1); }
            50% { opacity: 0.9; transform: scale(1.06); }
        }

        .auth-blob-a { animation: blob-a 18s ease-in-out infinite; }
        .auth-blob-b { animation: blob-b 22s ease-in-out infinite; }
        .auth-float { animation: float-y 5s ease-in-out infinite; }
        .auth-float-slow { animation: float-y-slow 7s ease-in-out infinite; }
        .auth-sun { animation: sun-pulse 8s ease-in-out infinite; }
        .auth-in { animation: rise 0.7s cubic-bezier(0.16, 1, 0.3, 1) both; }
        .auth-d1 { animation-delay: 0.05s; }
        .auth-d2 { animation-delay: 0.12s; }
        .auth-d3 { animation-delay: 0.2s; }
        .auth-d4 { animation-delay: 0.3s; }
        .auth-d5 { animation-delay: 0.42s; }

        .auth-btn {
            background: linear-gradient(110deg, #059669 0%, #0ea5e9 45%, #10b981 75%, #059669 100%);
            background-size: 220% 100%;
            animation: shine 6s linear infinite;
        }
        .auth-btn:hover { filter: brightness(1.06); }

        .auth-field:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgb(16 185 129 / 18%);
        }
    </style>
</head>
<body class="font-ui h-full bg-sky-50 text-slate-900 antialiased selection:bg-emerald-200 selection:text-slate-900">
    <main class="h-full">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
