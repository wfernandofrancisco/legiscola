<x-layouts.central>
    <x-slot name="title">Editar diretor regional</x-slot>

    <x-breadcrumb />

    <x-page-header :title="$director->name" subtitle="Diretor regional" />

    <div class="rounded-2xl border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-slate-900">
        @include('central.directors.includes._form', ['action' => 'edit', 'director' => $director])
    </div>
</x-layouts.central>
