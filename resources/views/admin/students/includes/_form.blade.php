@php
    use App\Enums\Escolaridade;
    $student = $student ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST" action="{{ $action === 'edit' ? route('admin.alunos.update', $student) : route('admin.alunos.store') }}" enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados do Aluno</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Nome completo" :required="true" :value="$student?->user?->name ?? old('name')" />
            <x-form.input name="email" label="E-mail" type="email" :required="true" :value="$student?->email ?? old('email')" />
            <x-form.input name="enrollment_number" label="Matrícula" :required="true" :value="$student?->enrollment_number ?? old('enrollment_number')" />
            <x-form.date name="birth_date" label="Data de nascimento" :value="optional($student?->birth_date)->format('Y-m-d') ?? old('birth_date')" />
            <x-form.select name="sexo" label="Sexo" :selected="$student?->sexo ?? old('sexo')" :options="[
                'masculino' => 'Masculino',
                'feminino' => 'Feminino',
                'outro' => 'Outro',
                'nao_informado' => 'Não informado',
            ]" />
            <x-form.input name="cpf" label="CPF" data-mask="cpf" :required="true" :value="$student?->cpf ?? old('cpf')" />
            <x-form.select name="status" label="Status" :required="true" :selected="$student?->status ?? old('status', 'ativo')" :options="[
                'ativo' => 'Ativo',
                'inativo' => 'Inativo',
            ]" />
            <x-form.input name="telefone" label="Telefone" data-mask="phone" :value="$student?->telefone ?? old('telefone')" />
            <x-form.input name="celular" label="Celular" data-mask="phone" :value="$student?->celular ?? old('celular')" />
            <x-form.input name="cep" label="CEP" data-mask="cep" :value="$student?->cep ?? old('cep')" />
            <x-form.input name="logradouro" label="Logradouro" :value="$student?->logradouro ?? old('logradouro')" />
            <x-form.input name="numero" label="Número" :value="$student?->numero ?? old('numero')" />
            <x-form.input name="bairro" label="Bairro" :value="$student?->bairro ?? old('bairro')" />
            <x-form.input name="cidade" label="Cidade" :value="$student?->cidade ?? old('cidade')" />
            <x-form.input name="uf" label="UF" :value="$student?->uf ?? old('uf')" />
            <x-form.input name="profissao" label="Profissão" :value="$student?->profissao ?? old('profissao')" />
            <x-form.select name="escolaridade" label="Escolaridade" :selected="$student?->escolaridade ?? old('escolaridade')" :options="Escolaridade::options()" />
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto do aluno</label>
                <div class="flex items-center gap-4 rounded-xl border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                    <img id="student-photo-preview"
                        src="{{ $student?->photo_path ? asset('storage/'.$student->photo_path) : 'https://placehold.co/96x96/e5e7eb/6b7280?text=Foto' }}"
                        alt="Pré-visualização da foto" class="h-20 w-20 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm">
                    <div class="flex-1">
                        <input id="student-photo-input" type="file" name="photo" accept="image/*"
                            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG/JPG até 2MB. A foto será exibida em formato arredondado.</p>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium hover:bg-indigo-700 transition">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Cadastrar Aluno' }}
        </button>
        <a href="{{ route('admin.alunos.index') }}"
            class="ml-2 rounded-lg px-5 py-2 text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition">Cancelar</a>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                var fileInput = document.getElementById('student-photo-input');
                var preview = document.getElementById('student-photo-preview');
                if (!fileInput || !preview) return;

                fileInput.addEventListener('change', function (event) {
                    var file = event.target.files && event.target.files[0];
                    if (!file) return;
                    var reader = new FileReader();
                    reader.onload = function (e) {
                        preview.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                });
            })();
        </script>
    @endpush
@endonce
