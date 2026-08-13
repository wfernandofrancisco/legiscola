@extends('layouts.public')

@section('title', 'Validação de Certificado — '.config('app.name'))

@section('content')
    <header class="border-b border-sky-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-3xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="group flex items-center gap-2 rounded-lg outline-none ring-offset-2 focus-visible:ring-2 focus-visible:ring-sky-500">
                @if(file_exists(public_path('img/logo.png')))
                    <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="h-8 w-auto object-contain" width="140" height="36"/>
                @else
                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-sky-600 text-xs font-bold text-white">{{ strtoupper(substr(config('app.name'), 0, 2)) }}</span>
                    <span class="font-display text-base font-semibold text-slate-900">{{ config('app.name') }}</span>
                @endif
            </a>
            <a href="{{ url('/') }}" class="text-sm font-semibold text-sky-700 underline-offset-2 hover:underline">Voltar ao início</a>
        </div>
    </header>

    <main class="min-h-[60vh] bg-sky-50 py-12 sm:py-16">
        <div class="mx-auto max-w-2xl px-4 sm:px-6">
            <div class="overflow-hidden rounded-2xl border border-sky-200 bg-white p-8 shadow-lg sm:p-10">
                <div class="mb-6 flex flex-wrap items-center gap-4">
                    <img src="{{ asset('img/marketing/banner-civic.svg') }}" width="400" height="120" alt="" class="h-16 w-full max-w-xs rounded-lg object-cover object-left sm:h-20" role="presentation"/>
                    <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wider text-emerald-900">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500" aria-hidden="true"></span>
                        Validação pública
                    </div>
                </div>
                <h1 class="font-display text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">Validação de certificado</h1>

                @if($certificate)
                    <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                        <p class="text-base font-semibold text-emerald-900">Certificado válido.</p>
                    </div>
                    <ul class="mt-6 space-y-3 text-base text-slate-800">
                        <li><span class="font-bold text-slate-900">Aluno:</span> {{ data_get($certificate->snapshot, 'student_name', $certificate->student?->user?->name) }}</li>
                        <li><span class="font-bold text-slate-900">Curso:</span> {{ data_get($certificate->snapshot, 'course_name', $certificate->course?->name) }}</li>
                        <li><span class="font-bold text-slate-900">Emitido em:</span> {{ $certificate->issued_at?->format('d/m/Y H:i') }}</li>
                    </ul>
                    <div class="mt-8">
                        <a href="{{ route('certificados.download', $certificate->validation_hash) }}"
                           class="inline-flex items-center justify-center rounded-full bg-sky-600 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">
                            Baixar certificado (PDF)
                        </a>
                    </div>
                @else
                    <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 p-4">
                        <p class="text-base font-semibold text-rose-900">Certificado não encontrado.</p>
                    </div>
                @endif
            </div>
        </div>
    </main>
@endsection
