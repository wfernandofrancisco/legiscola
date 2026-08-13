<x-layouts.responsible title="Meu perfil" subtitle="Atualize dados e senha. Este painel não é o admin da escola.">
    <div class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        @if ($errors->any())
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/40 dark:bg-red-950/30 dark:text-red-200">{{ $errors->first() }}</div>
        @endif

        {{-- breadcrumb institucional: painel = /professor --}}
        <nav class="mb-6 text-xs font-semibold text-slate-500 dark:text-slate-400">
            <a href="{{ route('responsible.dashboard') }}" class="hover:underline" style="color:var(--portal-primary, #0891b2)">Painel gestor</a>
            <span class="mx-1">/</span>
            <span class="text-slate-700 dark:text-slate-300">Meu perfil</span>
        </nav>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Dados pessoais</h2>

            <form method="POST" action="{{ route('responsible.perfil.update') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="name">Nome completo</label>
                    <input id="name" name="name" type="text" required value="{{ old('name', auth()->user()->name) }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="email">E-mail</label>
                    <input id="email" name="email" type="email" required value="{{ old('email', auth()->user()->email) }}"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="phone">Telefone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', auth()->user()->phone) }}" data-mask="phone"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-slate-900 dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>

                <div class="flex flex-wrap gap-3 pt-4">
                    <button type="submit" class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-cyan-600 dark:hover:bg-cyan-500">
                        Salvar alterações
                    </button>
                    <a href="{{ route('responsible.dashboard') }}" class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800">
                        Voltar ao painel
                    </a>
                </div>
            </form>
        </div>

        <div class="mt-8 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900/70">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white">Alterar senha</h2>

            <form method="POST" action="{{ route('responsible.perfil.change-password') }}" class="mt-6 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="current_password">Senha atual</label>
                    <input id="current_password" name="current_password" type="password" required autocomplete="current-password"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password">Nova senha</label>
                    <input id="password" name="password" type="password" required autocomplete="new-password"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300" for="password_confirmation">Confirmar nova senha</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password"
                           class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm dark:border-slate-600 dark:bg-slate-950 dark:text-white"/>
                </div>

                <button type="submit" class="mt-2 rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-slate-800 dark:bg-cyan-600 dark:hover:bg-cyan-500">
                    Alterar senha
                </button>
            </form>
        </div>

        @if(auth()->user()->isTenantProfessor())
            <p class="mt-8 text-center text-sm text-slate-600 dark:text-slate-400">
                Área exclusiva turmas/ficha:
                <a href="{{ route('professor.dashboard') }}" class="font-semibold underline" style="color:var(--portal-primary, #0891b2)">Painel docente</a>
            </p>
        @endif
    </div>

    @push('scripts')
        <script src="{{ asset('js/masks.js') }}"></script>
    @endpush
</x-layouts.responsible>
