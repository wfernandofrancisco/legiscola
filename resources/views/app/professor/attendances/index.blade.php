@extends('layouts.app')

@section('title', 'Lançar Presenças')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Lançamento de Presença</h1>
        <form method="POST" action="{{ route('responsible.presencas.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
            @csrf
            <input name="course_id" type="number" placeholder="ID Curso" class="rounded-lg border-slate-300">
            <input name="student_id" type="number" placeholder="ID Aluno" class="rounded-lg border-slate-300">
            <input name="curriculum_id" type="number" placeholder="ID Disciplina (opcional)" class="rounded-lg border-slate-300">
            <input name="class_date" type="date" class="rounded-lg border-slate-300">
            <select name="status" class="rounded-lg border-slate-300">
                <option value="presente">Presente</option>
                <option value="falta">Falta</option>
                <option value="justificada">Falta Justificada</option>
            </select>
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700">Registrar Presença</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Últimos Lançamentos</h2>
        <div class="mt-4 space-y-2">
            @foreach($attendances as $attendance)
                <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    @php
                        $statusLabel = [
                            'presente' => 'Presente',
                            'falta' => 'Falta',
                            'justificada' => 'Falta Justificada',
                            'present' => 'Presente',
                            'absent' => 'Falta',
                            'justified' => 'Falta Justificada',
                        ][$attendance->status] ?? ucfirst((string) $attendance->status);
                    @endphp
                    Aluno #{{ $attendance->student_id }} - {{ $attendance->course->name }} em {{ $attendance->class_date?->format('d/m/Y') }}: {{ $statusLabel }}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
