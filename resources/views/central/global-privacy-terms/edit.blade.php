<x-layouts.central>
    <div class="max-w-4xl">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Termo de privacidade e segurança (global)</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Aplica-se a <span class="font-semibold">todos os tenants</span>. Ao publicar uma nova versão, utilizadores autenticados precisam aceitar de novo antes de usar os painéis.
            </p>
            <dl class="mt-4 flex flex-wrap gap-6 text-sm text-gray-700 dark:text-gray-300">
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-500">Versão publicada</dt>
                    <dd class="font-semibold">{{ $term->version > 0 ? $term->version : '— (rascunho)' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-500">Última publicação</dt>
                    <dd class="font-semibold">{{ $term->published_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                </div>
            </dl>
        </div>

        <form method="post" action="{{ route('central.global-privacy-term.update') }}" class="space-y-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
            @csrf
            @method('PUT')

            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Título</label>
                <input type="text" name="title" id="title" value="{{ old('title', $term->title) }}" required maxlength="255"
                       class="w-full rounded-lg border border-gray-300 px-3 py-2 text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white"/>
                @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="body_html" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-300">Texto (editor)</label>
                <textarea name="body_html" id="body_html" rows="16" class="w-full rounded-lg border border-gray-300 px-3 py-2 font-mono text-sm text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white">{{ old('body_html', $term->body_html) }}</textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">HTML confiável (editado só por super admin). Revise antes de publicar.</p>
                @error('body_html')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                <button type="submit" name="action" value="draft"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-900 dark:text-gray-100 dark:hover:bg-gray-800">
                    Guardar rascunho
                </button>
                <button type="submit" name="action" value="publish"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    Publicar nova versão
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof CKEDITOR !== 'undefined') {
                    CKEDITOR.replace('body_html', { height: 420, language: 'pt-br' });
                }
            });
        </script>
    @endpush
</x-layouts.central>
