@if (request()->has('embedded'))
    {{-- Modo modal/AJAX: retorna só o conteúdo sem layout --}}
    @include('central.tenants.includes._show_card')
@else
    <x-layouts.central>
        <x-slot name="title">{{ $tenant->name }}</x-slot>

        <x-slot name="actions">
            <a href="{{ route('central.tenants.edit', $tenant) }}"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Editar
            </a>
            @if ($tenant->status !== 'ativo')
                <form method="POST" action="{{ route('central.tenants.activate', $tenant) }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition">Ativar</button>
                </form>
            @else
                <form method="POST" action="{{ route('central.tenants.deactivate', $tenant) }}">
                    @csrf
                    <button type="submit"
                        class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition">Desativar</button>
                </form>
            @endif
        </x-slot>

        <x-breadcrumb />

        @include('central.tenants.includes._show_card')
    </x-layouts.central>
@endif
