<x-layouts.aluno title="Dados cadastrais">
    @php
        $inp = 'w-full rounded-xl border border-slate-700 bg-slate-900/80 px-3.5 py-2.5 text-sm text-white placeholder-slate-500 transition focus:border-cyan-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/20';
        $sel = $inp;
    @endphp

    <div class="mx-auto max-w-2xl">
        <p class="mb-6 text-sm text-slate-400">Atualize foto, e-mail, contato e endereço. O e-mail de login será o mesmo do cadastro de aluno.</p>

        <form method="post" action="{{ route('app.cadastro.update') }}" enctype="multipart/form-data" class="space-y-8 rounded-3xl border border-slate-800 bg-slate-900/50 p-6 sm:p-8">
            @csrf
            @method('PUT')

            <div class="flex flex-col items-center gap-4 sm:flex-row sm:items-start">
                <img id="aluno-photo-preview"
                     src="{{ $student->photo_path ? asset('storage/'.$student->photo_path) : 'https://placehold.co/120x120/1e293b/64748b?text=Foto' }}"
                     alt="" class="h-28 w-28 rounded-2xl border border-slate-700 object-cover shadow-lg" width="112" height="112" />
                <div class="w-full">
                    <label class="mb-1 block text-xs font-semibold text-slate-300">Nova foto</label>
                    <input id="aluno-photo-input" type="file" name="photo" accept="image/*" class="{{ $inp }} border-dashed file:mr-3 file:rounded-lg file:border-0 file:bg-cyan-500 file:px-3 file:py-1.5 file:text-xs file:font-bold file:text-slate-950" />
                    @error('photo')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">E-mail</label>
                <input type="email" name="email" value="{{ old('email', $student->email ?? $student->user?->email) }}" required class="{{ $inp }}" />
                @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Sexo</label>
                <select name="sexo" class="{{ $sel }}">
                    <option value="">—</option>
                    @foreach (['masculino' => 'Masculino', 'feminino' => 'Feminino', 'outro' => 'Outro', 'nao_informado' => 'Não informado'] as $val => $lab)
                        <option value="{{ $val }}" @selected(old('sexo', $student->sexo) === $val)>{{ $lab }}</option>
                    @endforeach
                </select>
                @error('sexo')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">Celular</label>
                    <input name="celular" id="aluno-celular" value="{{ old('celular', $student->celular) }}" class="{{ $inp }}" placeholder="(00) 00000-0000" />
                    @error('celular')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">Telefone</label>
                    <input name="telefone" id="aluno-telefone" value="{{ old('telefone', $student->telefone) }}" class="{{ $inp }}" placeholder="(00) 0000-0000" />
                    @error('telefone')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">CEP</label>
                    <input name="cep" id="aluno-cep" value="{{ old('cep', $student->cep) }}" class="{{ $inp }}" />
                    @error('cep')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">UF</label>
                    <input name="uf" value="{{ old('uf', $student->uf) }}" maxlength="2" class="{{ $inp }}" placeholder="SP" />
                    @error('uf')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Logradouro</label>
                <input name="logradouro" value="{{ old('logradouro', $student->logradouro) }}" class="{{ $inp }}" />
                @error('logradouro')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">Número</label>
                    <input name="numero" value="{{ old('numero', $student->numero) }}" class="{{ $inp }}" />
                    @error('numero')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-semibold text-slate-300">Bairro</label>
                    <input name="bairro" value="{{ old('bairro', $student->bairro) }}" class="{{ $inp }}" />
                    @error('bairro')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-semibold text-slate-300">Cidade</label>
                <input name="cidade" value="{{ old('cidade', $student->cidade) }}" class="{{ $inp }}" />
                @error('cidade')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
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
        var input = document.getElementById('aluno-photo-input');
        var preview = document.getElementById('aluno-photo-preview');
        if (input && preview) {
            input.addEventListener('change', function () {
                var f = this.files && this.files[0];
                if (!f) return;
                preview.src = URL.createObjectURL(f);
            });
        }
        function maskPhone(el, mobile) {
            if (!el) return;
            el.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').slice(0, mobile ? 11 : 10);
                if (mobile) {
                    if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,7)+'-'+v.slice(7);
                    else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
                    else if (v.length > 0) v = '('+v;
                } else {
                    if (v.length > 6) v = '('+v.slice(0,2)+') '+v.slice(2,6)+'-'+v.slice(6);
                    else if (v.length > 2) v = '('+v.slice(0,2)+') '+v.slice(2);
                    else if (v.length > 0) v = '('+v;
                }
                this.value = v;
            });
        }
        maskPhone(document.getElementById('aluno-celular'), true);
        maskPhone(document.getElementById('aluno-telefone'), false);
        var cep = document.getElementById('aluno-cep');
        if (cep) {
            cep.addEventListener('input', function () {
                var v = this.value.replace(/\D/g, '').slice(0, 8);
                if (v.length > 5) v = v.slice(0,5)+'-'+v.slice(5);
                this.value = v;
            });
        }
        var form = document.querySelector('form[action="{{ route('app.cadastro.update') }}"]');
        if (form) {
            form.addEventListener('submit', function () {
                var c = document.getElementById('aluno-celular');
                var t = document.getElementById('aluno-telefone');
                var z = document.getElementById('aluno-cep');
                if (c) c.value = c.value.replace(/\D/g, '');
                if (t) t.value = t.value.replace(/\D/g, '');
                if (z) z.value = z.value.replace(/\D/g, '');
            });
        }
    })();
    </script>
    @endpush
</x-layouts.aluno>
