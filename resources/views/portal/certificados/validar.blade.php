@extends('layouts.portal')

@section('title', 'Validar certificado')

@section('content')
    <x-portal.page-hero
        narrow
        title="Validar certificado"
        subtitle="Informe o código de validação impresso no certificado para confirmar se o documento é autêntico."
    />

    <div class="mx-auto max-w-xl px-4 pb-20 sm:px-6 lg:px-8 mt-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900/40">
            <form method="post" action="{{ route('portal.certificados.validar.consultar') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="codigo" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Código de validação</label>
                    <input
                        id="codigo"
                        name="codigo"
                        type="text"
                        value="{{ old('codigo', $codigoDigitado ?? '') }}"
                        autocomplete="off"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-inner outline-none ring-slate-400/40 focus:border-slate-400 focus:ring-2 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-50"
                        placeholder="Cole o código exatamente como no certificado"
                        required
                    />
                    @error('codigo')
                        <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <x-turnstile />
                @error('cf-turnstile-response')
                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                @enderror
                <button
                    type="submit"
                    class="inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:opacity-95 sm:w-auto"
                    style="background-image:linear-gradient(135deg,var(--portal-primary,#3b82f6),var(--portal-secondary,#1e40af))"
                >
                    Consultar
                </button>
            </form>

            @if(($consulted ?? false) === true)
                <div class="mt-8 border-t border-slate-100 pt-6 dark:border-slate-800">
                    @if($certificate)
                        <p class="text-sm font-semibold text-emerald-700 dark:text-emerald-400">Certificado válido e encontrado nos registros desta escola.</p>
                        <dl class="mt-4 space-y-2 text-sm text-slate-700 dark:text-slate-300">
                            <div><dt class="font-medium text-slate-500 dark:text-slate-400">Aluno</dt>
                                <dd>{{ data_get($certificate->snapshot, 'student_name', $certificate->student?->user?->name) }}</dd></div>
                            <div><dt class="font-medium text-slate-500 dark:text-slate-400">Curso / evento</dt>
                                <dd>{{ data_get($certificate->snapshot, 'course_name', $certificate->course?->name) ?? data_get($certificate->snapshot, 'event_name', $certificate->event?->title) }}</dd></div>
                            <div><dt class="font-medium text-slate-500 dark:text-slate-400">Emitido em</dt>
                                <dd>{{ $certificate->issued_at?->format('d/m/Y H:i') }}</dd></div>
                        </dl>
                        <div class="mt-5">
                            <a
                                href="{{ route('certificados.download', $certificate->validation_hash) }}"
                                class="inline-flex rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-emerald-700"
                            >
                                Baixar PDF
                            </a>
                        </div>
                    @else
                        <p class="text-sm font-semibold text-red-700 dark:text-red-400">
                            Nenhum certificado encontrado para este código nesta escola. Verifique se o código está correto ou se o certificado foi emitido por outro município.
                        </p>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
