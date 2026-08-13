<form method="POST" action="{{ $action === 'edit' ? route('admin.eventos.update', $event) : route('admin.eventos.store') }}"
    enctype="multipart/form-data"
    class="w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    @csrf
    @if ($action === 'edit')
        @method('PUT')
    @endif
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-form.input name="title" label="Título" :value="$event?->title ?? old('title')" />
        <x-form.input name="max_seats" label="Vagas" type="number" :value="$event?->max_seats ?? old('max_seats')" />
        <x-form.input name="date_time" label="Data e hora do evento" type="datetime-local" :value="old('date_time', optional($event?->date_time)->format('Y-m-d\TH:i'))" />
        <div class="md:col-span-3 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/40">
            <div class="flex items-center gap-2">
                <input id="allow_online_registration" type="checkbox" name="allow_online_registration" value="1"
                    @checked(old('allow_online_registration', $event?->allow_online_registration ?? false))>
                <label for="allow_online_registration" class="text-sm font-medium text-gray-700 dark:text-gray-300">Permitir inscrição online</label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Com inscrição online ativa, informe o período em que o aluno pode se inscrever pelo portal.</p>
            <div id="registration-window-fields" class="grid grid-cols-1 gap-4 md:grid-cols-2 {{ old('allow_online_registration', $event?->allow_online_registration) ? '' : 'hidden' }}">
                <x-form.input name="registration_starts_at" label="Início das inscrições" type="datetime-local"
                    :value="old('registration_starts_at', optional($event?->registration_starts_at)->format('Y-m-d\TH:i'))" />
                <x-form.input name="registration_ends_at" label="Fim das inscrições" type="datetime-local"
                    :value="old('registration_ends_at', optional($event?->registration_ends_at)->format('Y-m-d\TH:i'))" />
            </div>
            <div class="flex items-center gap-2 pt-2">
                <input id="com_certificado" type="checkbox" name="com_certificado" value="1"
                    @checked(old('com_certificado', $event?->com_certificado ?? false))>
                <label for="com_certificado" class="text-sm font-medium text-gray-700 dark:text-gray-300">Emitir certificado aos presentes</label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">Requer template de certificado ativo com tipo de emissão «evento» e aluno marcado como presente.</p>
        </div>
        <x-form.input id="zipcode" name="zipcode" label="CEP" data-mask="cep" :value="$event?->zipcode ?? old('zipcode')" />
        <x-form.input id="address" name="address" label="Endereço" :value="$event?->address ?? old('address')" />
        <x-form.input name="number" label="Número" :value="$event?->number ?? old('number')" />
        <x-form.input name="complement" label="Complemento" :value="$event?->complement ?? old('complement')" />
        <x-form.input id="district" name="district" label="Bairro" :value="$event?->district ?? old('district')" />
        <x-form.input id="city" name="city" label="Cidade" :value="$event?->city ?? old('city')" />
        <x-form.input id="state" name="state" label="UF" :value="$event?->state ?? old('state')" />
        <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Foto do evento</label>
            <img id="event-photo-preview"
                src="{{ $event?->photo_path ? asset('storage/' . $event->photo_path) : 'https://placehold.co/240x140/e5e7eb/6b7280?text=Sem+foto' }}"
                alt="Preview da foto do evento"
                class="h-28 w-48 object-cover rounded-lg border border-gray-200 dark:border-gray-700 mb-3">
            <input id="event-photo-input" type="file" name="photo" accept="image/*"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-200" />
            @error('photo')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
        <div class="md:col-span-3">
            <x-form.textarea name="description" label="Descrição" :value="$event?->description ?? old('description')" rows="5" />
        </div>
    </div>
    <div class="pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
        <button type="submit" class="inline-flex rounded-lg bg-indigo-600 text-white px-5 py-2 text-sm font-medium">
            {{ $action === 'edit' ? 'Salvar Alterações' : 'Criar Evento' }}
        </button>
    </div>
</form>

@once
    @push('scripts')
        <script>
            (function() {
                var zipcode = document.getElementById('zipcode');
                var address = document.getElementById('address');
                var district = document.getElementById('district');
                var city = document.getElementById('city');
                var state = document.getElementById('state');
                var photoInput = document.getElementById('event-photo-input');
                var photoPreview = document.getElementById('event-photo-preview');
                if (!zipcode || !address || !district || !city || !state) return;

                function digits(v) {
                    return (v || '').replace(/\D/g, '');
                }

                function fillFields(data) {
                    if (!address.value) address.value = data.logradouro || '';
                    if (!district.value) district.value = data.bairro || '';
                    if (!city.value) city.value = data.localidade || '';
                    if (!state.value) state.value = data.uf || '';
                }

                function searchCep() {
                    var cep = digits(zipcode.value);
                    if (cep.length !== 8) return;

                    fetch('https://viacep.com.br/ws/' + cep + '/json/')
                        .then(function(res) {
                            return res.json();
                        })
                        .then(function(data) {
                            if (!data || data.erro) return;
                            fillFields(data);
                        })
                        .catch(function() {});
                }

                zipcode.addEventListener('blur', searchCep);

                var allowReg = document.getElementById('allow_online_registration');
                var regFields = document.getElementById('registration-window-fields');
                if (allowReg && regFields) {
                    function syncRegFields() {
                        regFields.classList.toggle('hidden', !allowReg.checked);
                    }
                    allowReg.addEventListener('change', syncRegFields);
                    syncRegFields();
                }

                if (photoInput && photoPreview) {
                    photoInput.addEventListener('change', function() {
                        var file = photoInput.files && photoInput.files[0];
                        if (!file) return;
                        if (!file.type || file.type.indexOf('image/') !== 0) return;
                        var reader = new FileReader();
                        reader.onload = function(e) {
                            if (e.target && e.target.result) {
                                photoPreview.src = e.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                    });
                }
            })();
        </script>
    @endpush
@endonce
