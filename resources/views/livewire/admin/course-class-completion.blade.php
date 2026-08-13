<div class="rounded-2xl border border-slate-200 bg-white p-4">
    <label class="text-sm font-medium text-slate-700">Frequência mínima (%)</label>
    <input type="number" min="1" max="100" wire:model="minimumAttendance" class="mt-2 w-32 rounded-lg border-slate-300">
    <button wire:click="complete" class="ml-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">
        Fechar turma
    </button>
</div>
