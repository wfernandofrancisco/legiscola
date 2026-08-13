<x-layouts.admin>
    <x-slot name="title">Templates de Certificado</x-slot>
    <x-breadcrumb :items="$breadcrumbs ?? []" />

    @php
        $activeFilterCount = collect([request('search'), request('engine')])->filter(fn($v) => filled($v))->count();
        $headerItems = [
            ['title' => 'templates', 'value' => $templates->total(), 'color' => 'indigo'],
            ['title' => 'filtros ativos', 'value' => $activeFilterCount, 'color' => 'sky'],
        ];
    @endphp

    <x-page-header title="Templates de Certificado" subtitle="Modele certificados por tenant." :items="$headerItems"
        :action-href="route('admin.templates-certificado.create')" action-text="Novo Template" />

    <form method="GET" action="{{ route('admin.templates-certificado.index') }}" class="mb-6">
        <x-filter-panel title="Pesquisa e filtros" subtitle="Busque por nome e engine."
            :reset-href="request()->hasAny(['search', 'engine']) ? route('admin.templates-certificado.index') : null">
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-5">
                <div class="lg:col-span-4">
                    <x-form.input label="Buscar template" name="search" value="{{ request('search') }}" autocomplete="off"
                        placeholder="Nome do template..." />
                </div>
                <div class="lg:col-span-1">
                    <x-form.select label="Engine" name="engine" placeholder="Todas" :options="[
                        'blade' => 'Blade',
                        'html' => 'HTML',
                        'image' => 'Imagem',
                    ]" :selected="request('engine')" />
                </div>
            </div>
        </x-filter-panel>
    </form>

    @include('admin.certificate-templates.includes._table', compact('templates'))
</x-layouts.admin>
