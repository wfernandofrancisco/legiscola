@extends('layouts.portal')

@section('title', 'Histórico de turmas')

@section('content')
    <x-portal.page-hero align="center" title="Histórico de turmas" subtitle="Turmas já encerradas, em ordem das mais recentes.">
        <x-slot name="actions">
            <a href="{{ route('portal.cursos.index') }}"
               class="inline-flex rounded-full px-7 py-2.5 text-sm font-semibold text-white shadow-lg hover:opacity-95"
               style="background:linear-gradient(135deg,var(--portal-primary),var(--portal-secondary))">Turmas ativas →</a>
        </x-slot>
    </x-portal.page-hero>

    <section class="no-portal-animate py-12">
        <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
            @foreach($turmas as $turma)
                @include('portal.partials.course-class-card', ['turma' => $turma, 'tone' => 'secondary'])
            @endforeach
        </div>
        <div class="mx-auto mt-10 flex max-w-7xl justify-center px-4 sm:px-6 lg:px-8">
            {{ $turmas->links() }}
        </div>
    </section>
@endsection
