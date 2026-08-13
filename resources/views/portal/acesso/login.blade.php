@extends('layouts.portal')

@section('title', 'Entrar')

@section('content')
    <x-portal.auth-frame
        title="Entrar na área do aluno"
        subtitle="Acesse cursos, avisos e materiais da {{ $tenant->portalBrandTitle() }}."
    >
        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('tenant.login.store') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
            </div>
            <div>
                <div class="flex items-center justify-between gap-3">
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="password">Senha</label>
                    <a href="{{ route('portal.acesso.forgot') }}" class="shrink-0 text-xs font-semibold" style="color:var(--portal-primary)">Esqueci minha senha</a>
                </div>
                <input id="password" name="password" type="password" required autocomplete="current-password"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
            </div>
            <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300 dark:border-slate-600"/>
                Manter sessão neste navegador
            </label>
            <x-turnstile />
            @error('cf-turnstile-response')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <button type="submit"
                    class="flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                    style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                Entrar
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600 dark:text-slate-400">
            É professor ou equipe pedagógica?
            <a href="{{ route('portal.acesso.docente.login') }}" class="font-semibold underline underline-offset-2 hover:opacity-90" style="color:var(--portal-primary)">Entrar na área do docente</a>
        </p>

        <p class="mt-6 border-t border-slate-100 pt-6 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400">
            Primeira vez aqui?
            <a href="{{ route('portal.acesso.register') }}" class="font-semibold underline decoration-slate-300 underline-offset-2 hover:decoration-slate-500" style="color:var(--portal-primary)">
                Criar cadastro
            </a>
        </p>
    </x-portal.auth-frame>
@endsection
