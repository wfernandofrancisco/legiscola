<x-layouts.admin>
    <x-slot name="title">{{ $survey->title }}</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />
    <x-subpage-header title="{{ $survey->title }}" subtitle="Visualização da pesquisa de satisfação." />

    <div class="mb-4 flex gap-2">
        <a href="{{ route('admin.pesquisas-satisfacao.edit', $survey) }}"
            class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">Editar</a>
        <a href="{{ route('admin.pesquisas-satisfacao.index') }}"
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 dark:border-gray-600 dark:text-gray-200">Voltar</a>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            Status:
            <span class="font-semibold {{ $survey->is_active ? 'text-emerald-600' : 'text-gray-500' }}">
                {{ $survey->is_active ? 'Ativa' : 'Inativa' }}
            </span>
        </p>
        @if ($survey->description)
            <p class="mt-4 text-sm leading-relaxed text-gray-700 dark:text-gray-200">{{ $survey->description }}</p>
        @endif

        <ol class="mt-6 space-y-4">
            @foreach ($survey->questions as $question)
                <li class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $loop->iteration }}. {{ $question->question }}</p>
                    <p class="mt-1 text-xs uppercase tracking-wide text-gray-500">
                        {{ $question->isChoices() ? 'Opções' : 'Campo livre' }}
                    </p>
                    @if ($question->isChoices())
                        <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-gray-600 dark:text-gray-300">
                            @foreach ($question->options as $option)
                                <li>{{ $option->label }}</li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ol>
    </div>
</x-layouts.admin>
