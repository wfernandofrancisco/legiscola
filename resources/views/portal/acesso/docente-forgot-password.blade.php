@extends('layouts.portal')

@section('title', 'Recuperar senha — Docente')

@section('content')
    <x-portal.auth-frame
        title="Recuperar senha (docente)"
        subtitle="Informe o e-mail da sua conta institucional. Enviaremos um link para definir uma nova senha."
        context-label="Área do docente"
        context-hint="O mesmo fluxo vale para conta de gestor/docente no tenant. Verifique também a pasta de spam."
    >
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
            </div>
            <x-turnstile />
            @error('cf-turnstile-response')
                <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
            @enderror
            <button type="submit"
                    class="flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                    style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                Enviar link de recuperação
            </button>
        </form>

        <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
            <a href="{{ route('portal.acesso.docente.login') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Voltar ao login do docente</a>
        </p>

        <p class="mt-4 text-center text-xs text-slate-500 dark:text-slate-500">
            <a href="{{ route('portal.acesso.forgot') }}" class="font-medium underline underline-offset-2 hover:text-slate-700 dark:hover:text-slate-300" style="color:var(--portal-primary)">Recuperação — área do aluno</a>
        </p>
    </x-portal.auth-frame>
@endsection
