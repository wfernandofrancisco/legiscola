<x-layouts.director>
    <x-page-header title="Liberar item para uma câmara"
        subtitle="Cursos: a câmara abre turmas. Palestras: você agenda data e inscritos; a câmara confirma o evento." />

    @if ($itens->isEmpty())
        <div
            class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center dark:border-slate-700 dark:bg-slate-900">
            <p class="text-sm font-medium text-slate-700 dark:text-slate-200">Você ainda não tem item publicado.</p>
            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                Só itens com situação "Publicado" podem ser liberados.
            </p>
            <a href="{{ route('diretor.catalogo.index') }}"
                class="mt-4 inline-flex rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700">
                Ir para o catálogo
            </a>
        </div>
    @else
        <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
            @include('director.licencas.includes._form', ['licenca' => null])
        </div>
    @endif

    @include('director.licencas.includes._select2-assets')
</x-layouts.director>
