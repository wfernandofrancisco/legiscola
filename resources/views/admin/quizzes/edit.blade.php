<x-layouts.admin>
    <x-slot name="title">Editar Quiz</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Editar Quiz" subtitle="Atualize turmas, perguntas e gabarito." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('admin.quizzes.includes._form', [
            'action' => 'edit',
            'quiz' => $quiz,
            'classes' => $classes ?? collect(),
        ])
    </div>
</x-layouts.admin>
