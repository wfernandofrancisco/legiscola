@extends('layouts.guest')

@section('title', 'Aceitar termo de privacidade')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-12">
        <div class="rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
            <h1 class="text-xl font-bold text-gray-900">Atualização do termo de privacidade</h1>
            <p class="mt-2 text-sm text-gray-600">
                Foi publicada uma nova versão (n.º {{ $term->version }}) do termo global da plataforma. Para continuar, leia e aceite abaixo.
            </p>

            <div class="prose prose-sm mt-6 max-w-none border-y border-gray-100 py-6 text-gray-800">
                <h2 class="text-base font-semibold">{{ $term->title }}</h2>
                {!! $term->body_html !!}
            </div>

            <form method="post" action="{{ route('privacy-term.accept') }}" class="mt-8 space-y-4">
                @csrf
                <label class="flex items-start gap-3 text-sm text-gray-800">
                    <input type="checkbox" name="accept" value="1" class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ old('accept') ? 'checked' : '' }} />
                    <span>Declaro que li e concordo com o termo acima, nos termos da LGPD.</span>
                </label>
                @error('accept')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-bold text-white shadow hover:bg-indigo-700">
                    Continuar
                </button>
            </form>
        </div>
    </div>
@endsection
