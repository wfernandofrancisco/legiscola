#!/usr/bin/env python3
"""Replace old catalog cards with improved ones."""

with open('resources/views/portal/catalogo.blade.php', 'r', encoding='utf-8') as f:
    lines = f.readlines()

# Lines 200-263 (1-indexed) = index 199-262 (0-indexed) is the old card block
start = 199  # "                    <div class="grid grid-cols-1 gap-6..."
end = 263    # "                    </div>" (after @endforeach)

print(f"Line {start+1}: {repr(lines[start][:70])}")
print(f"Line {end}: {repr(lines[end-1][:70])}")

new_block = '''                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($empresas as $empresa)
                            @php
                                $nomeEmpresa = $empresa->override?->nome_fantasia ?: ($empresa->nome_fantasia ?: $empresa->razao_social);
                                $primeiroItem = $empresa->catalogItems->first();
                                $coverUrl = $primeiroItem?->fotoPrincipalPublicUrl() ?? null;
                                $isServico = str_contains(strtolower((string) ($primeiroItem?->tipo?->value ?? '')), 'servico');
                            @endphp
                            <a href="{{ route('portal.empresas.show', $empresa) }}"
                               class="group flex flex-col overflow-hidden rounded-3xl border-2 border-blue-100 bg-white shadow-sm transition-all duration-300 hover:border-blue-400 hover:shadow-2xl hover:-translate-y-0.5">

                                {{-- Capa --}}
                                <div class="relative h-52 overflow-hidden {{ $isServico ? 'bg-gradient-to-br from-violet-100 to-sky-100' : 'bg-gradient-to-br from-blue-50 to-cyan-100' }}">
                                    @if ($coverUrl)
                                        <img src="{{ $coverUrl }}" alt="{{ $primeiroItem?->nome ?? $nomeEmpresa }}"
                                             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-7xl font-black {{ $isServico ? 'text-violet-200' : 'text-blue-200' }}">
                                            {{ Str::upper(substr($nomeEmpresa, 0, 2)) }}
                                        </div>
                                    @endif

                                    {{-- Badge tipo --}}
                                    @if ($primeiroItem?->tipo)
                                        <span class="absolute top-3 left-3 rounded-full {{ $isServico ? 'bg-violet-500' : 'bg-blue-500' }} px-3 py-1 text-xs font-bold text-white shadow-lg">
                                            {{ $primeiroItem->tipo->label() }}
                                        </span>
                                    @endif

                                    {{-- Badge contagem --}}
                                    @if ($empresa->catalog_items_visiveis_count > 0)
                                        <span class="absolute top-3 right-3 rounded-full bg-emerald-500 px-3 py-1 text-xs font-bold text-white shadow-lg">
                                            {{ $empresa->catalog_items_visiveis_count }} oferta{{ $empresa->catalog_items_visiveis_count > 1 ? 's' : '' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Conteudo --}}
                                <div class="flex flex-1 flex-col p-5">
                                    {{-- Nome empresa --}}
                                    <p class="flex items-center gap-1 text-[10px] font-bold uppercase tracking-widest text-slate-400 truncate">
                                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $nomeEmpresa }}
                                    </p>

                                    {{-- Produto / Servico --}}
                                    @if ($primeiroItem)
                                        <h3 class="mt-2 text-base font-bold text-slate-900 leading-snug line-clamp-2 group-hover:text-blue-600 transition-colors">
                                            {{ $primeiroItem->nome }}
                                        </h3>
                                        @if ($primeiroItem->descricao)
                                            <p class="mt-1.5 text-xs text-slate-500 line-clamp-2 leading-relaxed">
                                                {{ Str::limit(strip_tags((string) $primeiroItem->descricao), 90) }}
                                            </p>
                                        @endif
                                    @else
                                        <h3 class="mt-2 text-base font-bold text-slate-700 leading-snug line-clamp-2">{{ $nomeEmpresa }}</h3>
                                    @endif

                                    {{-- Footer --}}
                                    <div class="mt-auto flex items-center justify-between gap-2 pt-4 border-t-2 border-blue-50">
                                        <span class="flex items-center gap-1 text-xs text-slate-400 truncate">
                                            <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0L6.343 16.657a8 8 0 1111.314 0z"/></svg>
                                            {{ $empresa->bairroParaListagemPortal() ?? 'Sem bairro' }}
                                        </span>
                                        @if ($primeiroItem?->preco_base)
                                            <span class="shrink-0 text-sm font-extrabold text-blue-700">
                                                R$&nbsp;{{ number_format((float) $primeiroItem->preco_base, 2, ',', '.') }}
                                            </span>
                                        @elseif ($empresa->catalog_items_visiveis_count > 1)
                                            <span class="shrink-0 text-xs font-semibold text-slate-400">+{{ $empresa->catalog_items_visiveis_count - 1 }} mais</span>
                                        @else
                                            <span class="shrink-0 text-xs font-semibold text-blue-500">Ver detalhes &rarr;</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
'''

new_lines = lines[:start] + [new_block] + lines[end:]
with open('resources/views/portal/catalogo.blade.php', 'w', encoding='utf-8') as f:
    f.writelines(new_lines)

print("Done! New total lines:", len(new_lines))
