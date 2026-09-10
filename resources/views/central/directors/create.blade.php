<x-layouts.central>
    <x-slot name="title">Novo diretor regional</x-slot>

    <x-breadcrumb />

    <x-page-header title="Novo diretor regional"
        subtitle="O diretor acompanha as câmaras das UFs escolhidas e recebe um e-mail para definir a senha." />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('central.directors.includes._form', ['action' => 'create', 'director' => null])
    </div>
</x-layouts.central>
