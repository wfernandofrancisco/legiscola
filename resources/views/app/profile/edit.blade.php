<x-layouts.user :hide-heading="true">
    <div class="min-h-screen bg-slate-50 pb-16 dark:bg-gray-950">

        {{-- Premium dark banner --}}
        <div class="relative w-full overflow-hidden bg-slate-900 text-white">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(148,163,184,.055)_1px,transparent_1px),linear-gradient(90deg,rgba(148,163,184,.055)_1px,transparent_1px)] bg-size-[32px_32px]"></div>
            <div class="pointer-events-none absolute -top-32 -left-20 h-96 w-96 rounded-full bg-cyan-500/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-20 right-1/4 h-64 w-120 rounded-full bg-blue-600/15 blur-3xl"></div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-linear-to-r from-transparent via-cyan-400/30 to-transparent"></div>
            <div class="relative mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-cyan-300/80">Conta</p>
                <h1 class="mt-2 text-3xl font-extrabold tracking-tight sm:text-4xl">Meu perfil</h1>
                <p class="mt-2 text-sm text-slate-300">Dados pessoais e senha de acesso ao painel.</p>
            </div>
        </div>

        <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-100">
                    <svg class="h-4 w-4 shrink-0 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @php
                $inp = 'w-full rounded-xl border border-slate-200 bg-slate-50 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 transition hover:bg-white hover:border-slate-300 focus:bg-white focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/15 focus:outline-none dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';
            @endphp

            {{-- Dados pessoais --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-white">
                        <svg class="h-4 w-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Dados pessoais
                    </h2>
                </div>
                <form method="post" action="{{ route('app.profile.update') }}" class="space-y-4 p-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Nome completo</label>
                        <input name="name" value="{{ old('name', $user->name) }}" required class="{{ $inp }}" placeholder="Seu nome completo" />
                        @error('name')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">E-mail</label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="{{ $inp }}" />
                            @error('email')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Telefone</label>
                            <input name="phone" id="profile-phone" value="{{ old('phone', $user->phone) }}" class="{{ $inp }}" placeholder="(00) 00000-0000" />
                            @error('phone')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">CPF</label>
                            <input name="cpf" id="profile-cpf" value="{{ old('cpf', $user->cpf) }}" class="{{ $inp }}" placeholder="000.000.000-00" />
                            @error('cpf')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Endereço completo</label>
                            <input name="endereco_completo" value="{{ old('endereco_completo', $user->endereco_completo) }}" class="{{ $inp }}" placeholder="Rua, número, cidade..." />
                            @error('endereco_completo')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-slate-800 dark:bg-slate-700 dark:hover:bg-slate-600">
                            Salvar dados
                        </button>
                    </div>
                </form>
            </section>

            {{-- Alterar senha --}}
            <section class="overflow-hidden rounded-2xl border border-slate-200/60 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900">
                <div class="border-b border-slate-100 bg-slate-50/60 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <h2 class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-white">
                        <svg class="h-4 w-4 text-cyan-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        Alterar senha
                    </h2>
                </div>
                <form method="post" action="{{ route('app.profile.password') }}" class="space-y-4 p-6">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Senha atual</label>
                        <input type="password" name="current_password" required class="{{ $inp }}" autocomplete="current-password" placeholder="••••••••" />
                        @error('current_password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Nova senha</label>
                            <input type="password" name="password" required class="{{ $inp }}" autocomplete="new-password" placeholder="••••••••" />
                            @error('password')<p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-semibold text-slate-700 dark:text-slate-300">Confirmar nova senha</label>
                            <input type="password" name="password_confirmation" required class="{{ $inp }}" autocomplete="new-password" placeholder="••••••••" />
                        </div>
                    </div>
                    <div class="flex justify-end pt-2">
                        <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-bold text-slate-800 shadow-sm transition hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-100 dark:hover:bg-slate-700">
                            Atualizar senha
                        </button>
                    </div>
                </form>
            </section>

        </div>
    </div>
</x-layouts.responsible>

@push('scripts')
<script>
(function () {
    // CPF mask
    const cpf = document.getElementById('profile-cpf');
    if (cpf) {
        cpf.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 9)      v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6,9)+'-'+v.slice(9);
            else if (v.length > 6) v = v.slice(0,3)+'.'+v.slice(3,6)+'.'+v.slice(6);
            else if (v.length > 3) v = v.slice(0,3)+'.'+v.slice(3);
            this.value = v;
        });
    }
    // Phone mask
    const phone = document.getElementById('profile-phone');
    if (phone) {
        phone.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
            else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
            else if (v.length > 0) v = '('+v;
            this.value = v;
        });
    }
    // Strip masks on submit
    document.querySelectorAll('form').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (cpf)   cpf.value   = cpf.value.replace(/\D/g, '');
            if (phone) phone.value = phone.value.replace(/\D/g, '');
        });
    });
})();
</script>
@endpush