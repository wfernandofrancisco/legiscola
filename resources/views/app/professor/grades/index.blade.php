@extends('layouts.app')

@section('title', 'Lançar Notas')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="text-2xl font-bold text-slate-900">Lançamento de Notas</h1>
        <form method="POST" action="{{ route('responsible.notas.store') }}" class="mt-4 grid gap-3 md:grid-cols-3">
            @csrf
            <input name="course_id" type="number" placeholder="ID Curso" class="rounded-lg border-slate-300">
            <input name="student_id" type="number" placeholder="ID Aluno" class="rounded-lg border-slate-300">
            <input name="curriculum_id" type="number" placeholder="ID Disciplina (opcional)" class="rounded-lg border-slate-300">
            <input name="score" type="number" step="0.01" placeholder="Nota" class="rounded-lg border-slate-300">
            <input name="max_score" type="number" step="0.01" value="10" class="rounded-lg border-slate-300">
            <input name="evaluated_at" type="date" class="rounded-lg border-slate-300">
            <button class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 md:col-span-3">Lançar Nota</button>
        </form>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-semibold text-slate-900">Últimas Notas</h2>
        <div class="mt-4 space-y-2">
            @foreach($grades as $grade)
                <div class="rounded-lg bg-slate-50 px-3 py-2 text-sm text-slate-700">
                    Aluno #{{ $grade->student_id }} - {{ $grade->course->name }}: {{ number_format((float) $grade->score, 1, ',', '.') }}/{{ number_format((float) $grade->max_score, 1, ',', '.') }}
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
