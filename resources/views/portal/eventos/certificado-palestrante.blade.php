@extends('layouts.portal')

@section('title', 'Certificado do palestrante')

@section('content')
    <x-portal.auth-frame
        title="Certificado do palestrante"
        subtitle="Baixe o certificado de {{ $event->palestrante_nome }} referente ao evento «{{ $event->title }}»."
    >
        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        @if (! $hasTemplate)
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-100">
                A organização ainda não cadastrou um template ativo do tipo «Palestrante».
            </div>
        @else
            <form method="POST" action="{{ route('portal.eventos.certificado-palestrante.store', $event) }}" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="cpf">CPF</label>
                    <input id="cpf" name="cpf" type="text" value="{{ old('cpf') }}" required autofocus inputmode="numeric" autocomplete="off"
                           data-mask="cpf"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-800 dark:text-slate-100" for="senha">Senha</label>
                    <input id="senha" name="senha" type="password" required autocomplete="current-password"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none transition focus:border-slate-400 focus:ring-4 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                    <p class="mt-1.5 text-xs text-slate-500">Use a senha enviada pela organização do evento.</p>
                </div>
                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:brightness-110"
                        style="background:var(--portal-primary)">
                    Baixar certificado
                </button>
            </form>
        @endif

        <p class="mt-6 text-center text-xs text-slate-500">
            Evento em {{ $event->date_time?->format('d/m/Y H:i') ?? '—' }}
        </p>
    </x-portal.auth-frame>
@endsection
