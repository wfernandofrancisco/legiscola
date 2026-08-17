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
            <div class="pt-1">
                <x-form.input name="certificado_disponivel_ate" label="Data limite para emissão do certificado" type="datetime-local"
                    :value="old('certificado_disponivel_ate', optional($event?->certificado_disponivel_ate)->format('Y-m-d\TH:i'))"
                    hint="Até esta data o aluno pode acessar e baixar o certificado. Deixe em branco para manter disponível sem prazo." />
            </div>
        </div>
        <div class="md:col-span-3 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/40">
            <div class="flex items-center gap-2">
                <input id="chamada_georreferencia" type="checkbox" name="chamada_georreferencia" value="1"
                    @checked(old('chamada_georreferencia', $event?->chamada_georreferencia ?? false))>
                <label for="chamada_georreferencia" class="text-sm font-medium text-gray-700 dark:text-gray-300">Chamada por georreferência</label>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Com esta opção ativa, o aluno inscrito pode registrar presença no portal usando o GPS do celular, desde que esteja dentro do raio e no horário configurados.
            </p>
            <div id="geofence-fields" class="grid grid-cols-1 gap-4 md:grid-cols-3 {{ old('chamada_georreferencia', $event?->chamada_georreferencia) ? '' : 'hidden' }}">
                <x-form.input name="latitude" label="Latitude" type="number" step="any"
                    :value="old('latitude', $event?->latitude)"
                    hint="Ex.: -22.3570" />
                <x-form.input name="longitude" label="Longitude" type="number" step="any"
                    :value="old('longitude', $event?->longitude)"
                    hint="Ex.: -47.3849" />
                <x-form.input name="geofence_raio_metros" label="Raio (metros)" type="number"
                    :value="old('geofence_raio_metros', $event?->geofence_raio_metros ?? 100)"
                    hint="Distância máxima permitida do ponto do evento (10 a 5000 m)." />
                <x-form.input name="presenca_inicio_em" label="Início da chamada" type="datetime-local"
                    :value="old('presenca_inicio_em', optional($event?->presenca_inicio_em)->format('Y-m-d\TH:i'))" />
                <x-form.input name="presenca_fim_em" label="Fim da chamada" type="datetime-local"
                    :value="old('presenca_fim_em', optional($event?->presenca_fim_em)->format('Y-m-d\TH:i'))" />
            </div>
        </div>
        <div class="md:col-span-3 flex flex-col gap-3 rounded-lg border border-gray-200 bg-gray-50/80 p-4 dark:border-gray-600 dark:bg-gray-900/40">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Certificado do palestrante</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Preencha para gerar um link público em que o palestrante informa CPF e senha e baixa o certificado
                (template ativo do tipo «Palestrante»).
            </p>
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <x-form.input name="palestrante_nome" label="Nome do palestrante"
                    :value="old('palestrante_nome', $event?->palestrante_nome)" />
                <x-form.input id="palestrante_cpf" name="palestrante_cpf" label="CPF do palestrante (opcional)" data-mask="cpf"
                    :value="old('palestrante_cpf', $event?->palestrante_cpf)"
                    hint="Se informado, o CPF digitado no link precisa bater com este." />
                <div>
                    <x-form.input id="palestrante_senha" name="palestrante_senha" label="Senha do palestrante" type="text"
                        :value="old('palestrante_senha')"
                        :hint="$event?->palestrante_senha ? 'Deixe em branco para manter a senha atual.' : 'Mínimo 6 caracteres. Use «Gerar senha» se preferir.'" />
                    <button type="button" id="gerar-senha-palestrante"
                        class="mt-2 inline-flex rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-1.5 text-xs font-semibold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-800 dark:bg-indigo-950/40 dark:text-indigo-300">
                        Gerar senha
                    </button>
                </div>
            </div>
            @if ($action === 'edit' && $event?->hasSpeakerCertificateSetup())
                <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-3 dark:border-emerald-900/50 dark:bg-emerald-950/30">
                    <p class="text-xs font-semibold text-emerald-800 dark:text-emerald-200">Link para o palestrante baixar o certificado</p>
                    <div class="mt-2 flex flex-col gap-2 sm:flex-row sm:items-center">
                        <input id="palestrante-cert-link" type="text" readonly
                            value="{{ $event->speakerCertificatePublicUrl() }}"
                            class="w-full rounded-lg border border-emerald-200 bg-white px-3 py-2 text-xs text-gray-800 dark:border-emerald-900 dark:bg-gray-900 dark:text-gray-100" />
                        <button type="button" id="copy-palestrante-cert-link"
                            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700">
                            Copiar link
                        </button>
                    </div>
                    <p class="mt-2 text-[11px] text-emerald-700 dark:text-emerald-300">
                        Envie este link junto com a senha. O palestrante usa CPF + senha para baixar o PDF.
                    </p>
                </div>
            @endif
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

                var geoToggle = document.getElementById('chamada_georreferencia');
                var geoFields = document.getElementById('geofence-fields');
                if (geoToggle && geoFields) {
                    function syncGeoFields() {
                        geoFields.classList.toggle('hidden', !geoToggle.checked);
                    }
                    geoToggle.addEventListener('change', syncGeoFields);
                    syncGeoFields();
                }

                var gerarSenha = document.getElementById('gerar-senha-palestrante');
                var senhaInput = document.getElementById('palestrante_senha');
                if (gerarSenha && senhaInput) {
                    gerarSenha.addEventListener('click', function () {
                        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
                        var out = '';
                        for (var i = 0; i < 10; i++) {
                            out += chars.charAt(Math.floor(Math.random() * chars.length));
                        }
                        senhaInput.value = out;
                        senhaInput.focus();
                        senhaInput.select();
                    });
                }

                var copyLinkBtn = document.getElementById('copy-palestrante-cert-link');
                var linkInput = document.getElementById('palestrante-cert-link');
                if (copyLinkBtn && linkInput) {
                    copyLinkBtn.addEventListener('click', function () {
                        linkInput.select();
                        linkInput.setSelectionRange(0, 99999);
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            navigator.clipboard.writeText(linkInput.value);
                        } else {
                            document.execCommand('copy');
                        }
                        copyLinkBtn.textContent = 'Copiado!';
                        setTimeout(function () { copyLinkBtn.textContent = 'Copiar link'; }, 1500);
                    });
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
