@extends('layouts.portal')

@section('title', 'Confirme seu e-mail')

@section('content')
    <x-portal.auth-frame
        title="Confirme seu e-mail"
        subtitle="Quase lá: abra o link que enviamos para ativar sua conta na área do aluno. Se não encontrar a mensagem, verifique o spam."
    >
        <div class="rounded-2xl border border-slate-100 bg-slate-50/80 px-5 py-4 dark:border-slate-800 dark:bg-slate-900/60">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">E-mail cadastrado</p>
            <p class="mt-1 break-all text-sm font-semibold text-slate-900 dark:text-white">
                {{ auth()->user()?->email }}
            </p>
        </div>

        @if (session('warning'))
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-900/40 dark:bg-amber-950/30 dark:text-amber-100">
                {{ session('warning') }}
            </div>
        @endif

        @if ($errors->has('email'))
            <div class="mt-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/40 dark:bg-red-950/40 dark:text-red-100">
                {{ $errors->first('email') }}
            </div>
        @endif

        @if (session('status') === 'verification-link-sent')
            <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-200">
                Um novo link de confirmação foi enviado para o seu e-mail.
            </div>
        @endif

        <div class="mt-6 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-slate-700 dark:border-slate-700 dark:bg-slate-900/50 dark:text-slate-300">
            Depois de clicar no link do e-mail, você será encaminhado para a área do aluno. Se a página não atualizar sozinha, volte a acessar o login.
        </div>

        <div class="mt-8 grid gap-3 sm:grid-cols-2">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-center rounded-full px-5 py-3 text-sm font-bold text-white shadow-lg transition hover:opacity-95"
                        style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                    Reenviar e-mail
                </button>
            </form>

            <form method="POST" action="{{ route('tenant.logout') }}">
                @csrf
                <button type="submit"
                        class="flex w-full items-center justify-center rounded-full border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900">
                    Sair desta sessão
                </button>
            </form>
        </div>

        <p class="mt-8 border-t border-slate-100 pt-6 text-center text-sm text-slate-600 dark:border-slate-800 dark:text-slate-400">
            Já confirmou?
            <a href="{{ route('portal.acesso.login') }}" class="font-semibold underline underline-offset-2" style="color:var(--portal-primary)">Ir para o login</a>
        </p>
    </x-portal.auth-frame>
@endsection
