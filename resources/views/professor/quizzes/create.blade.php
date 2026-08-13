<x-layouts.professor>
    <x-slot name="title">Novo Quiz</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="Cadastrar Quiz" subtitle="Crie perguntas, alternativas e resposta correta." />

    <div class="w-full bg-gray-50 dark:bg-gray-900 -mx-4 sm:mx-0 sm:rounded-lg p-4 sm:p-0">
        @include('professor.quizzes.includes._form', [
            'action' => 'create',
            'quiz' => null,
            'classes' => $classes ?? collect(),
        ])
    </div>
</x-layouts.professor>
