@extends('layouts.portal')

@section('title', 'Contato')

@section('content')
    <x-portal.page-hero title="Contato institucional" subtitle="Envie mensagens à equipe para dúvidas acadêmicas, parcerias ou suporte. Retornamos pelos canais informados pela câmara." />

    <section class="no-portal-animate py-16">
        <div class="mx-auto grid max-w-7xl gap-12 px-4 lg:grid-cols-5 sm:px-6 lg:px-8">
            <div class="portal-animate-card lg:col-span-2 rounded-3xl border border-slate-200 bg-slate-50 p-10 dark:border-slate-800 dark:bg-slate-900/40" data-animate="fadeInLeft">
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
                </div>
            </div>
        </div>
    </section>
@endsection
