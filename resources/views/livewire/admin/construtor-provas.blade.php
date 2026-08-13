<x-layouts.admin>
    <x-slot name="title">Construtor de Provas</x-slot>

    <div class="space-y-6 p-4 sm:p-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h1 class="text-xl font-bold text-slate-900">Montagem de Provas</h1>
            <p class="text-sm text-slate-600">Selecione turma, questões e anexos.</p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm space-y-4">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Turma</label>
                    <select wire:model="classId" class="w-full rounded-xl border-slate-300 text-sm">
                        <option value="">Escolha...</option>
                        @foreach($turmas as $turma)
                            <option value="{{ $turma->id }}">{{ $turma->name }} - {{ $turma->course?->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Título da prova</label>
                    <input wire:model="title" type="text" class="w-full rounded-xl border-slate-300 text-sm" placeholder="Ex.: Avaliação Final">
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Instruções</label>
                <textarea wire:model="instructions" rows="3" class="w-full rounded-xl border-slate-300 text-sm"></textarea>
            </div>

            <div>
                <p class="mb-2 text-sm font-medium text-slate-700">Banco de Questões</p>
                <div class="max-h-72 space-y-2 overflow-y-auto rounded-xl border border-slate-200 p-3">
                    @foreach($questions as $question)
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg p-2 hover:bg-slate-50">
                            <input type="checkbox" wire:model="questionIds" value="{{ $question->id }}" class="mt-1 rounded border-slate-300">
                            <span class="text-sm text-slate-700">
                                <strong>[{{ $question->subject?->name }}]</strong> {{ $question->content }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Anexos</label>
                <input type="file" wire:model="attachments" multiple class="w-full rounded-xl border-slate-300 text-sm">
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button wire:click="salvar" class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                    Salvar Prova
                </button>

                @if($template)
                    <a target="_blank" href="{{ route('admin.provas.imprimir', $template) }}"
                        class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">
                        Imprimir Prova
                    </a>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin>
