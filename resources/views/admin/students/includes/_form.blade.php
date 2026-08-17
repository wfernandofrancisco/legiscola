@php
    $student = $student ?? null;
    $action = $action ?? 'create';
@endphp

<form method="POST" action="{{ $action === 'edit' ? route('admin.alunos.update', $student) : route('admin.alunos.store') }}"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <fieldset>
        <legend class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Dados do Aluno</legend>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Nome completo" :required="true" :value="$student?->user?->name ?? old('name')" />
            <x-form.input name="cpf" label="CPF" data-mask="cpf" :required="true" :value="$student?->cpf ?? old('cpf')" />
            <x-form.date name="birth_date" label="Data de nascimento" :required="true" :value="optional($student?->birth_date)->format('Y-m-d') ?? old('birth_date')" />
            <x-form.select name="sexo" label="Sexo" :required="true" :selected="$student?->sexo ?? old('sexo')" :options="[
                'masculino' => 'Masculino',
                'feminino' => 'Feminino',
                'outro' => 'Outro',
                'nao_informado' => 'Não informado',
            ]" />
            <x-form.input name="email" label="E-mail" type="email" :required="true" :value="$student?->email ?? old('email')" />
            <x-form.input name="cidade" label="Cidade" :required="true" :value="$student?->cidade ?? old('cidade')" />
            <x-form.input name="password" label="Senha" type="password" :required="$action === 'create'" autocomplete="new-password" />
            @if ($action === 'edit')
                <p class="md:col-span-2 -mt-2 text-xs text-gray-500 dark:text-gray-400">Deixe a senha em branco para manter a atual.</p>
            @endif
            <x-form.input name="password_confirmation" label="Confirmar senha" type="password" :required="$action === 'create'" autocomplete="new-password" />
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
