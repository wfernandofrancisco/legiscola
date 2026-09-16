@php
    $courseLessons = $course->lessons ?? collect();
@endphp

<section class="mt-8 w-full bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 shadow-sm p-6">
    <div class="mb-4">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Aulas deste curso</h2>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            Conteúdo reutilizado em todas as turmas: título, vídeo e material.
            Data, horário e presencial/online você define na <strong>grade da turma</strong>.
        </p>
    </div>

    @if ($courseLessons->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 px-4 py-8 text-center text-sm text-gray-500 dark:border-gray-600 dark:bg-gray-900/40 dark:text-gray-400">
            Nenhuma aula ainda. Cadastre o conteúdo abaixo; depois monte a grade ao criar a turma.
        </div>
    @else
        <div class="space-y-3">
            @foreach ($courseLessons as $lesson)
                <details class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-700 dark:bg-gray-900">
                    <summary class="flex cursor-pointer list-none items-start gap-4 p-4 [&::-webkit-details-marker]:hidden">
                        <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-xs font-semibold text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300">
                            {{ $loop->iteration }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $lesson->title }}</p>
                            <div class="mt-2 flex flex-wrap gap-2 text-[11px]">
                                @if ($lesson->hasVideo())
                                    <span class="rounded-md bg-emerald-50 px-2 py-0.5 font-medium text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-300">Vídeo</span>
                                @else
                                    <span class="rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-500 dark:bg-gray-800 dark:text-gray-400">Sem vídeo</span>
                                @endif
                                @if ($lesson->hasMaterial())
                                    <span class="rounded-md bg-blue-50 px-2 py-0.5 font-medium text-blue-700 dark:bg-blue-950/40 dark:text-blue-300">Material</span>
                                @endif
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">Editar</span>
                    </summary>
                    <div class="border-t border-gray-200 bg-gray-50 p-5 dark:border-gray-700 dark:bg-gray-800/40">
                        <form method="POST" action="{{ route('admin.cursos.aulas.update', [$course, $lesson]) }}" enctype="multipart/form-data" class="space-y-4">
                            @csrf
                            @method('PUT')
                            @include('admin.courses.includes._lesson-fields', ['lesson' => $lesson])
                            <div class="flex flex-wrap justify-end gap-2">
                                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Salvar aula</button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.cursos.aulas.destroy', [$course, $lesson]) }}" class="mt-3"
                            onsubmit="return confirm('Remover a aula {{ $lesson->title }} do curso? Turmas já abertas mantêm a aula na grade.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Remover do curso</button>
                        </form>
                    </div>
                </details>
            @endforeach
        </div>
    @endif

    <div class="mt-6 rounded-xl border-2 border-dashed border-indigo-200 bg-indigo-50/50 p-5 dark:border-indigo-800 dark:bg-indigo-950/20">
        <h3 class="text-sm font-bold text-indigo-900 dark:text-indigo-100">Adicionar aula</h3>
        <p class="mt-1 mb-4 text-xs text-indigo-800/80 dark:text-indigo-300/80">Só o conteúdo. A agenda é por turma.</p>
        <form method="POST" action="{{ route('admin.cursos.aulas.store', $course) }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @include('admin.courses.includes._lesson-fields', ['lesson' => null])
            <div class="flex justify-end">
                <button type="submit" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
                    Salvar nova aula
                </button>
            </div>
        </form>
    </div>
</section>
