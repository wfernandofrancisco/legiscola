<form method="POST" action="{{ $action === 'edit' ? route('admin.professores.update', $teacher) : route('admin.professores.store') }}"
    enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-form.input name="full_name" label="Nome completo" :required="true" :value="$teacher?->full_name ?? old('full_name')" />
        <x-form.input name="email" type="email" label="E-mail" :required="true" :value="$teacher?->email ?? old('email')" />
        <x-form.input name="celular" label="Celular" data-mask="phone" :value="$teacher?->phone ?? old('celular')" />
        <x-form.select name="status" label="Status" :required="true" :options="['ativo' => 'Ativo', 'inativo' => 'Inativo']" :selected="$teacher?->status ?? old('status', 'ativo')" />
        <x-form.input name="specialities" label="Especialidades" hint="Separe por vírgula para gerar tags." :value="$teacher?->specialities ?? old('specialities')" />
        <div class="md:col-span-2">
            <x-form.textarea name="bio" label="Biografia" rows="4" :value="$teacher?->bio ?? old('bio')" />
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto do professor</label>
            <div class="flex items-center gap-4 rounded-xl border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                <img id="teacher-photo-preview"
                    src="{{ $teacher?->photo_path ? asset('storage/'.$teacher->photo_path) : 'https://placehold.co/96x96/e5e7eb/6b7280?text=Foto' }}"
                    alt="Pré-visualização da foto" class="h-20 w-20 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm">
                <div class="flex-1">
                    <input id="teacher-photo-input" type="file" name="photo" accept="image/*"
                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG/JPG até 2MB. A foto será exibida em formato arredondado.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 mt-6">
        <button type="submit" class="inline-flex rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Professor' }}
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                var fileInput = document.getElementById('teacher-photo-input');
                var preview = document.getElementById('teacher-photo-preview');
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
