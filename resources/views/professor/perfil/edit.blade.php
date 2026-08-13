<x-layouts.professor>
    <x-slot name="title">Meu perfil</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <div class="max-w-2xl space-y-6">
        @if ($teacher)
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dados do docente</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Atualize foto, contato, biografia e especialidades. O e-mail também é usado para acesso à conta.</p>

                @if ($errors->any())
                    <div
                        class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm-.75-11.25a.75.75 0 0 1 1.5 0v4.5a.75.75 0 0 1-1.5 0v-4.5Zm.75 7.5a.875.875 0 1 1 0-1.75.875.875 0 0 1 0 1.75Z"
                                clip-rule="evenodd" />
                        </svg>
                        <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('professor.perfil.update') }}" enctype="multipart/form-data"
                    class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-form.input name="full_name" label="Nome completo" :required="true" :value="old('full_name', $teacher->full_name)" />
                        <x-form.input name="email" type="email" label="E-mail" :required="true"
                            :value="old('email', $teacher->email ?? auth()->user()->email)" />
                        <x-form.input name="celular" label="Celular" data-mask="phone" :value="old('celular', $teacher->phone)" />
                        <x-form.input name="specialities" label="Especialidades" hint="Separe por vírgula."
                            :value="old('specialities', $teacher->specialities)" />
                        <div class="md:col-span-2">
                            <x-form.textarea name="bio" label="Biografia" rows="4" :value="old('bio', $teacher->bio)" />
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Foto</label>
                            <div
                                class="flex items-center gap-4 rounded-xl border border-gray-200 dark:border-gray-700 p-3 bg-gray-50 dark:bg-gray-900/40">
                                <img id="professor-photo-preview"
                                    src="{{ $teacher->photo_path ? asset('storage/'.$teacher->photo_path) : 'https://placehold.co/96x96/e5e7eb/6b7280?text=Foto' }}"
                                    alt="Pré-visualização" class="h-20 w-20 rounded-full object-cover ring-2 ring-white dark:ring-gray-800 shadow-sm">
                                <div class="flex-1">
                                    <input id="professor-photo-input" type="file" name="photo" accept="image/*"
                                        class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100" />
                                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">PNG/JPG até 2MB.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                            Salvar alterações
                        </button>
                        <a href="{{ route('professor.senha.edit') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
                            Trocar senha
                        </a>
                        <a href="{{ route('professor.dashboard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-300 hover:underline">
                            Voltar ao painel
                        </a>
                    </div>
                </form>
            </div>

            @once
                @push('scripts')
                    <script>
                        (function () {
                            var fileInput = document.getElementById('professor-photo-input');
                            var preview = document.getElementById('professor-photo-preview');
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
        @else
            <div
                class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6 sm:p-8">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Dados da conta</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Nome, e-mail e telefone da sua conta de gestão.</p>

                @if ($errors->any())
                    <div
                        class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 dark:bg-red-950 dark:border-red-800 px-4 py-3 mb-6">
                        <ul class="space-y-0.5 text-[13px] text-red-700 dark:text-red-400">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('professor.perfil.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <x-form.input name="name" label="Nome completo" :required="true" :value="old('name', auth()->user()->name)" />
                    <x-form.input name="email" type="email" label="E-mail" :required="true" :value="old('email', auth()->user()->email)" />
                    <x-form.input name="phone" label="Telefone" data-mask="phone" :value="old('phone', auth()->user()->phone)" />

                    <div class="pt-6 border-t border-gray-200 dark:border-gray-700 flex flex-wrap gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg font-medium transition">
                            Salvar alterações
                        </button>
                        <a href="{{ route('professor.senha.edit') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition">
                            Trocar senha
                        </a>
                        <a href="{{ route('professor.dashboard') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 text-gray-600 dark:text-gray-300 hover:underline">
                            Voltar ao painel
                        </a>
                    </div>
                </form>
            </div>

            @push('scripts')
                <script src="{{ asset('js/masks.js') }}"></script>
            @endpush
        @endif
    </div>
</x-layouts.professor>
