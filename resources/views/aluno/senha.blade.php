<x-layouts.aluno title="Alterar senha">
    @php
        $inp = 'w-full rounded-xl border border-slate-700 bg-slate-900/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20';
    @endphp

    <div class="mx-auto max-w-lg">
        <p class="mb-6 text-sm text-slate-400">Informe a senha atual para definir uma nova senha de acesso.</p>

        <form method="post" action="{{ route('app.senha.update') }}" class="space-y-5 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 sm:p-8">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Senha atual</label>
                <input type="password" name="current_password" required autocomplete="current-password" class="{{ $inp }}" />
                @error('current_password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Nova senha</label>
                <input type="password" name="password" required autocomplete="new-password" class="{{ $inp }}" />
                @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Confirmar nova senha</label>
                <input type="password" name="password_confirmation" required autocomplete="new-password" class="{{ $inp }}" />
            </div>
            <div class="flex justify-end pt-2">
                <button type="submit" class="rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 hover:brightness-110">
                    Atualizar senha
                </button>
            </div>
        </form>
    </div>
</x-layouts.aluno>
