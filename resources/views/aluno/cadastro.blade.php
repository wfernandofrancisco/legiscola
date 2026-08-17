<x-layouts.aluno title="Dados cadastrais">
    @php
        $inp = 'w-full rounded-xl border border-slate-700 bg-slate-900/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20';
        $sel = $inp;
    @endphp

    <div class="mx-auto max-w-2xl">
        <p class="mb-6 text-sm text-slate-400">Atualize seus dados. O e-mail de login será o mesmo do cadastro de aluno.</p>

        <form method="post" action="{{ route('app.cadastro.update') }}" class="space-y-6 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300" for="name">Nome completo</label>
                <input id="name" type="text" name="name" value="{{ old('name', $student->user?->name) }}" required autocomplete="name" class="{{ $inp }}" />
                @error('name')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="cpf">CPF</label>
                    <input id="cpf" name="cpf" type="text" inputmode="numeric" maxlength="14" data-mask="cpf" required
                           value="{{ old('cpf', $student->cpf) }}" class="{{ $inp }}" />
                    @error('cpf')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="birth_date">Data de nascimento</label>
                    <input id="birth_date" type="date" name="birth_date" required
                           value="{{ old('birth_date', optional($student->birth_date)->format('Y-m-d')) }}" class="{{ $inp }}" />
                    @error('birth_date')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="sexo">Sexo</label>
                    <select id="sexo" name="sexo" required class="{{ $sel }}">
                        <option value="" disabled @selected(! old('sexo', $student->sexo))>Selecione</option>
                        @foreach (['masculino' => 'Masculino', 'feminino' => 'Feminino', 'outro' => 'Outro', 'nao_informado' => 'Não informado'] as $val => $lab)
                            <option value="{{ $val }}" @selected(old('sexo', $student->sexo) === $val)>{{ $lab }}</option>
                        @endforeach
                    </select>
                    @error('sexo')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="cidade">Cidade</label>
                    <input id="cidade" name="cidade" type="text" required value="{{ old('cidade', $student->cidade) }}" class="{{ $inp }}" />
                    @error('cidade')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300" for="email">E-mail</label>
                <input id="email" type="email" name="email" value="{{ old('email', $student->email ?? $student->user?->email) }}" required autocomplete="email" class="{{ $inp }}" />
                @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="password">Nova senha</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" class="{{ $inp }}" />
                    @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-slate-500">Deixe em branco para manter a senha atual.</p>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300" for="password_confirmation">Confirmar senha</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password" class="{{ $inp }}" />
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-cyan-500 to-indigo-600 px-6 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-500/25 hover:brightness-110">
                    Salvar alterações
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    (function () {
        function onlyDigits(str) {
            return String(str || '').replace(/\D/g, '');
        }
        function formatCpf(d) {
            d = onlyDigits(d).slice(0, 11);
            if (d.length <= 3) return d;
            if (d.length <= 6) return d.slice(0, 3) + '.' + d.slice(3);
            if (d.length <= 9) return d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6);
            return d.slice(0, 3) + '.' + d.slice(3, 6) + '.' + d.slice(6, 9) + '-' + d.slice(9, 11);
        }
        var cpf = document.getElementById('cpf');
        if (cpf) {
            cpf.addEventListener('input', function () {
                cpf.value = formatCpf(cpf.value);
            });
            if (cpf.value) cpf.dispatchEvent(new Event('input'));
        }
    })();
    </script>
    @endpush
</x-layouts.aluno>
