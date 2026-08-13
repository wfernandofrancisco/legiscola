@extends('layouts.portal')

@section('title', 'Nova senha')

@section('content')
    <x-portal.auth-frame
        title="Definir nova senha"
        subtitle="Crie uma senha forte. Depois você entra na área certa da sua conta."
        :context-label="$contextLabel ?? 'Acesso'"
        :context-hint="$contextHint ?? null"
    >
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
            @csrf
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="email">E-mail</label>
                <input id="email" name="email" type="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                @error('email')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="password">Nova senha</label>
                <input id="password" name="password" type="password" required autocomplete="new-password"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                @error('password')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="password_confirmation">Confirmar senha</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                       class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
            </div>

            <p class="text-xs text-slate-500">Mínimo de 8 caracteres.</p>

            <button type="submit"
                    class="flex w-full items-center justify-center rounded-full px-6 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                    style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                Salvar nova senha
            </button>
        </form>

        <p class="mt-8 border-t border-slate-100 pt-6 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400">
            <a href="{{ $loginUrl ?? url('/tenant/login') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Voltar ao login</a>
        </p>
    </x-portal.auth-frame>
@endsection
