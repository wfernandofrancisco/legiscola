@extends('layouts.public')

@section('title', config('app.name').' — Bem-vindo')

@section('content')
    <header class="border-b border-sky-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" class="font-display text-lg font-semibold text-slate-900">{{ config('app.name') }}</a>
            <nav class="flex items-center gap-3 text-sm">
                @guest
                    <a href="{{ route('tenant.login') }}" class="font-semibold text-slate-700 hover:text-sky-700">Entrar</a>
                    <a href="{{ route('register') }}"
                       class="inline-flex rounded-full bg-sky-600 px-4 py-2 font-semibold text-white shadow-sm transition hover:bg-sky-700">
                        Criar conta
                    </a>
                @else
                    <span class="hidden max-w-[10rem] truncate text-slate-700 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="font-semibold text-slate-700 hover:text-sky-700">Sair</button>
                    </form>
                @endguest
            </nav>
        </div>
    </header>

    <main class="pub-hero-bg pub-soft-grid min-h-[calc(100vh-8rem)] border-b border-sky-100">
        <section class="mx-auto grid max-w-6xl gap-12 px-4 py-16 sm:px-6 sm:py-20 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-8 lg:py-24">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-sky-700">Plataforma</p>
                <h1 class="font-display mt-4 text-4xl font-semibold leading-tight tracking-tight text-slate-900 sm:text-5xl">
                    Bem-vindo ao <span class="text-sky-700">{{ config('app.name') }}</span>
                </h1>
                <p class="mt-6 max-w-lg text-lg leading-relaxed text-slate-600">
                    Gestão integrada para quem leva a escola legislativa a sério — do portal ao certificado, com governança e LGPD no mesmo lugar.
                </p>
                <div class="mt-10 flex flex-col gap-3 sm:flex-row sm:flex-wrap">
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center justify-center rounded-full bg-sky-600 px-8 py-3.5 text-sm font-semibold text-white shadow-md transition hover:bg-sky-700">
                        Começar agora
                    </a>
                    <a href="{{ route('tenant.login') }}"
                       class="inline-flex items-center justify-center rounded-full border-2 border-sky-600 bg-white px-8 py-3.5 text-sm font-semibold text-sky-800 transition hover:bg-sky-50">
                        Já tenho conta
                    </a>
                </div>
            </div>
            <div class="rounded-3xl border border-sky-200 bg-white p-3 shadow-xl">
                <img src="{{ asset('img/marketing/hero-illustration.svg') }}"
                     width="800"
                     height="640"
                     alt=""
                     class="w-full rounded-2xl"
                     role="presentation"/>
            </div>
        </section>
    </main>

    <footer class="bg-slate-900 py-8 text-center text-sm text-slate-400">
        © {{ date('Y') }} {{ config('app.name') }}
    </footer>
@endsection
