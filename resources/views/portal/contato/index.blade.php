@extends('layouts.portal')

@section('title', 'Contato')

@php
    $whatsappNumero = $portalAdminSettings?->whatsapp;
    $whatsappSaudacao = 'Olá! Vim pelo site da '
        .($portalAdminSettings?->nome_camara ?: ($portalTenant?->display_name ?? $portalTenant?->name ?? 'câmara'))
        .' e gostaria de mais informações.';
    $whatsappUrl = \App\Support\WhatsAppLink::url($whatsappNumero, $whatsappSaudacao);
@endphp

@section('content')
    <x-portal.page-hero title="Contato institucional" subtitle="Envie mensagens à equipe para dúvidas acadêmicas, parcerias ou suporte. Retornamos pelos canais informados pela câmara." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 lg:grid-cols-5 sm:px-6 lg:px-8">
            <div class="portal-animate-card lg:col-span-2 rounded-3xl border border-slate-200 bg-slate-50 p-10 dark:border-slate-800 dark:bg-slate-900/40" data-animate="fadeInLeft">
                @if ($whatsappUrl)
                    <div class="mb-8 rounded-2xl border border-emerald-200/80 bg-emerald-50 p-6 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                        <p class="text-sm font-semibold text-emerald-900 dark:text-emerald-100">Atendimento pelo WhatsApp</p>
                        <p class="mt-1.5 text-xs leading-relaxed text-emerald-800/80 dark:text-emerald-200/70">
                            Fale direto com a equipe. A conversa abre já no número oficial{{ $whatsappNumero ? ' ('.$whatsappNumero.')' : '' }}.
                        </p>
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer"
                           class="mt-5 inline-flex w-full items-center justify-center gap-2.5 rounded-full bg-[#25D366] px-6 py-3 text-sm font-bold text-white shadow-lg shadow-emerald-500/25 transition hover:bg-[#1EBE5B] focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300/60">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.174.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 0 1 6.988 2.896 9.83 9.83 0 0 1 2.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.359.101 11.947c0 2.096.549 4.142 1.593 5.945L0 24l6.304-1.654a11.9 11.9 0 0 0 5.741 1.463h.005c6.585 0 11.946-5.359 11.949-11.948 0-3.192-1.24-6.192-3.495-8.448"/>
                            </svg>
                            Conversar no WhatsApp
                        </a>
                    </div>
                @endif
                <address class="not-italic text-sm text-slate-600 dark:text-slate-300">
                    @if(!empty($portalAdminSettings?->logradouro))
                        <p class="font-semibold text-slate-900 dark:text-white">Endereço</p>
                        <p class="mt-3">{{ trim($portalAdminSettings->logradouro.(($portalAdminSettings->numero ?? '') !== '' ? ', '.$portalAdminSettings->numero : '')) }}</p>
                        @if(!empty($portalAdminSettings->bairro) || !empty($portalAdminSettings->cidade))
                            <p class="mt-1">{{ collect([$portalAdminSettings->bairro, $portalAdminSettings->cidade, $portalAdminSettings->uf])->filter()->join(', ') }}</p>
                        @endif
                        @if(!empty($portalAdminSettings->cep))
                            <p class="mt-1">CEP {{ $portalAdminSettings->cep }}</p>
                        @endif
                    @elseif($portalTenant && $portalTenant->logradouro)
                        <p class="font-semibold text-slate-900 dark:text-white">Endereço</p>
                        <p class="mt-3">{{ $portalTenant->logradouro }}, {{ $portalTenant->numero }}</p>
                        <p>{{ $portalTenant->bairro }}, {{ $portalTenant->cidade }} — {{ $portalTenant->estado }}</p>
                    @endif
                    @if(!empty($portalAdminSettings?->telefone))
                        <p class="mt-6 font-semibold text-slate-900 dark:text-white">Telefone</p>
                        <p class="mt-2">{{ $portalAdminSettings->telefone }}</p>
                    @endif
                    @if(!empty($portalAdminSettings?->email))
                        <p class="mt-6 font-semibold text-slate-900 dark:text-white">E-mail preferencial</p>
                        <p class="mt-2"><a class="underline" href="mailto:{{ $portalAdminSettings->email }}">{{ $portalAdminSettings->email }}</a></p>
                    @endif
                    @if(!empty($portalTenant?->contact_email))
                        <p class="mt-6 text-xs text-slate-500">Administrativo: {{ $portalTenant->contact_email }}</p>
                    @endif
                </address>
            </div>
            <div class="lg:col-span-3">
                <div class="portal-animate-card rounded-3xl border border-slate-200 bg-white p-8 shadow-lg dark:border-slate-800 dark:bg-slate-900/60" data-animate="fadeInRight">
                    <form method="post" action="{{ route('portal.contato.store') }}" class="space-y-6">
                        @csrf
                        <div>
                            <label for="name" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">Nome</label>
                            <input id="name" name="name" type="text" required value="{{ old('name') }}"
                                   class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none ring-4 ring-transparent focus:border-slate-400 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                            @error('name') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="email" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">E-mail</label>
                                <input id="email" name="email" type="email" required value="{{ old('email') }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none ring-4 ring-transparent focus:border-slate-400 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                                @error('email') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="phone" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">Telefone <span class="font-normal text-slate-400">(opcional)</span></label>
                                <input id="phone" name="phone" type="text" value="{{ old('phone') }}"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none ring-4 ring-transparent focus:border-slate-400 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600"/>
                                @error('phone') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="message" class="mb-2 block text-sm font-semibold text-slate-800 dark:text-slate-100">Mensagem</label>
                            <textarea id="message" name="message" rows="5" required
                                      class="w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm outline-none ring-4 ring-transparent focus:border-slate-400 focus:ring-slate-200/60 dark:border-slate-700 dark:bg-slate-950 dark:text-white dark:focus:border-slate-600">{{ old('message') }}</textarea>
                            @error('message') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <x-turnstile />
                        @error('cf-turnstile-response')
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <button type="submit"
                                class="w-full rounded-full px-8 py-3 text-sm font-bold text-white shadow-xl transition hover:opacity-95"
                                style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">
                            Enviar mensagem
                        </button>
                    </form>

                    @if ($whatsappUrl)
                        <div class="mt-8 flex items-center gap-4" aria-hidden="true">
                            <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">ou</span>
                            <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                        </div>
                        <button type="button" id="contato-whatsapp-btn"
                                data-whatsapp-base="https://wa.me/{{ \App\Support\WhatsAppLink::normalize($whatsappNumero) }}"
                                class="mt-6 inline-flex w-full items-center justify-center gap-2.5 rounded-full border-2 border-[#25D366] px-8 py-3 text-sm font-bold text-[#128C4A] transition hover:bg-[#25D366] hover:text-white focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300/60 dark:text-emerald-300 dark:hover:text-white">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.174.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.1-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884a9.82 9.82 0 0 1 6.988 2.896 9.83 9.83 0 0 1 2.893 6.994c-.003 5.45-4.437 9.885-9.885 9.885M20.52 3.449C18.24 1.245 15.24 0 12.045 0 5.463 0 .104 5.359.101 11.947c0 2.096.549 4.142 1.593 5.945L0 24l6.304-1.654a11.9 11.9 0 0 0 5.741 1.463h.005c6.585 0 11.946-5.359 11.949-11.948 0-3.192-1.24-6.192-3.495-8.448"/>
                            </svg>
                            Enviar pelo WhatsApp
                        </button>
                        <p class="mt-3 text-center text-xs text-slate-500 dark:text-slate-400">
                            Abre o WhatsApp já com o texto que você digitou acima.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($whatsappUrl)
        @push('scripts')
            <script>
                (function () {
                    var btn = document.getElementById('contato-whatsapp-btn');
                    if (!btn) return;

                    btn.addEventListener('click', function () {
                        var nome = (document.getElementById('name') || {}).value || '';
                        var email = (document.getElementById('email') || {}).value || '';
                        var mensagem = (document.getElementById('message') || {}).value || '';

                        var linhas = [];
                        if (nome.trim()) linhas.push('Nome: ' + nome.trim());
                        if (email.trim()) linhas.push('E-mail: ' + email.trim());
                        if (mensagem.trim()) linhas.push('', mensagem.trim());

                        var texto = linhas.length
                            ? linhas.join('\n')
                            : @json($whatsappSaudacao);

                        window.open(
                            btn.dataset.whatsappBase + '?text=' + encodeURIComponent(texto),
                            '_blank',
                            'noopener'
                        );
                    });
                })();
            </script>
        @endpush
    @endif
@endsection
