import re, os

path = r'C:\laragon\www\desenvolve-city\resources\views\app\orcamento-solicitacoes\show.blade.php'
with open(path, 'rb') as f:
    src = f.read().decode('utf-8')

# ── 1. AVALIATION CARD FOR LEFT COLUMN (new, improved) ───────────────────────
AVALIACAO_LEFT = r"""
                {{-- ★ Avaliação — aparece na coluna principal logo após o header --}}
                @if ($s->podeReceberAvaliacao())
                    <div x-data="{ rating: {{ (int) old('avaliacao_estrelas', 0) }}, hovered: 0 }"
                         class="overflow-hidden rounded-3xl border-2 border-amber-200 bg-white shadow-sm">
                        <div class="border-b border-amber-100 bg-amber-50 px-6 py-4">
                            <h3 class="flex items-center gap-2 text-base font-bold text-amber-950">
                                <svg class="h-5 w-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Avalie o atendimento
                            </h3>
                            <p class="mt-1 text-xs text-amber-800">Sua nota é importante para outros moradores e para o ranking das empresas.</p>
                        </div>

                        <form method="post" action="{{ route('app.orcamento-solicitacoes.avaliar', $s) }}" class="p-6 space-y-6">
                            @csrf

                            {{-- Star picker --}}
                            <div>
                                <label class="mb-3 block text-sm font-semibold text-slate-800">Nota geral <span class="text-red-500">*</span></label>
                                <input type="hidden" name="avaliacao_estrelas" :value="rating" />
                                <div class="flex items-center gap-2">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <button type="button"
                                                @click="rating = {{ $i }}"
                                                @mouseenter="hovered = {{ $i }}"
                                                @mouseleave="hovered = 0"
                                                class="cursor-pointer text-5xl leading-none transition-transform duration-75 select-none focus:outline-none"
                                                :style="{ color: (hovered ? hovered : rating) >= {{ $i }} ? '#f59e0b' : '#e2e8f0', transform: (hovered ? hovered : rating) >= {{ $i }} ? 'scale(1.15)' : 'scale(1)' }">★</button>
                                    @endfor
                                    <span class="ml-3 min-w-28 text-sm font-semibold text-amber-700"
                                          x-text="hovered ? ['','Péssimo','Ruim','Regular','Bom','Excelente'][hovered] : (rating ? ['','Péssimo','Ruim','Regular','Bom','Excelente'][rating] : '')"></span>
                                </div>
                                <p class="mt-2 text-xs text-slate-400" x-show="!rating && !hovered">Passe o mouse e clique para selecionar a nota</p>
                                @error('avaliacao_estrelas')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            {{-- Critérios opcionais --}}
                            <div>
                                <p class="mb-3 text-sm font-semibold text-slate-800">Critérios detalhados <span class="text-xs font-normal text-slate-400">(opcional)</span></p>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    @foreach (\App\Support\OrcamentoAvaliacaoDimensoes::labels() as $key => $dimLabel)
                                        <div x-data="{ sc: {{ (int) old('scores.'.$key, 0) }}, sh: 0 }" class="rounded-2xl border border-slate-100 bg-slate-50 px-4 py-3">
                                            <label class="mb-2 block text-xs font-semibold text-slate-700" for="dim_{{ $key }}">{{ $dimLabel }}</label>
                                            <input type="hidden" name="scores[{{ $key }}]" :value="sc || ''" />
                                            <div class="flex items-center gap-1">
                                                @for ($st = 1; $st <= 5; $st++)
                                                    <button type="button"
                                                            @click="sc = (sc === {{ $st }} ? 0 : {{ $st }})"
                                                            @mouseenter="sh = {{ $st }}"
                                                            @mouseleave="sh = 0"
                                                            class="cursor-pointer text-2xl leading-none transition-transform duration-75 select-none focus:outline-none"
                                                            :style="{ color: (sh ? sh : sc) >= {{ $st }} ? '#f59e0b' : '#e2e8f0', transform: (sh ? sh : sc) >= {{ $st }} ? 'scale(1.1)' : 'scale(1)' }">★</button>
                                                @endfor
                                                <span class="ml-2 text-xs font-medium text-slate-500" x-text="sc ? sc+'/5' : '—'"></span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Comentário --}}
                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-800" for="avaliacao_comentario">Comentário <span class="text-xs font-normal text-slate-400">(opcional)</span></label>
                                <textarea id="avaliacao_comentario" name="avaliacao_comentario" rows="4"
                                          placeholder="Conte como foi a experiência — atendimento, qualidade, prazo..."
                                          class="w-full resize-none rounded-2xl border-slate-200 bg-slate-50 px-4 py-3 text-sm leading-relaxed text-slate-800 transition focus:border-amber-400 focus:bg-white focus:ring-2 focus:ring-amber-300/30 focus:outline-none">{{ old('avaliacao_comentario') }}</textarea>
                                @error('avaliacao_comentario')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>

                            <button type="submit"
                                    :disabled="rating === 0"
                                    :class="rating === 0 ? 'cursor-not-allowed opacity-40' : 'hover:bg-amber-600 active:bg-amber-700'"
                                    class="w-full rounded-2xl bg-amber-500 py-3 text-sm font-bold text-white shadow-sm transition">
                                Enviar avaliação
                            </button>
                        </form>
                    </div>

                @elseif ($s->avaliacao_estrelas)
                    <div class="overflow-hidden rounded-3xl border-2 border-slate-100 bg-white shadow-sm">
                        <div class="border-b border-slate-100 bg-slate-50 px-6 py-4">
                            <p class="flex items-center gap-2 font-bold text-slate-800">
                                <svg class="h-5 w-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                Sua avaliação
                            </p>
                        </div>
                        <div class="p-6">
                            <div class="flex items-center gap-3">
                                <span class="text-4xl leading-none" style="color:#f59e0b;">{!! str_repeat('★', (int) $s->avaliacao_estrelas) !!}<span style="color:#e2e8f0;">{!! str_repeat('★', max(0, 5 - (int) $s->avaliacao_estrelas)) !!}</span></span>
                                <span class="text-sm font-semibold text-slate-700">{{ ['','Péssimo','Ruim','Regular','Bom','Excelente'][(int) $s->avaliacao_estrelas] ?? '' }}</span>
                            </div>
                            @if (is_array($s->avaliacao_scores) && $s->avaliacao_scores !== [])
                                <div class="mt-4 grid gap-2 border-t border-slate-100 pt-4 sm:grid-cols-2">
                                    @foreach (\App\Support\OrcamentoAvaliacaoDimensoes::labels() as $key => $dimLabel)
                                        @if (!empty($s->avaliacao_scores[$key]))
                                            <div class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                                                <p class="text-[11px] font-semibold text-slate-500">{{ $dimLabel }}</p>
                                                <p class="mt-0.5 text-lg leading-none" style="color:#f59e0b;">{!! str_repeat('★', (int)$s->avaliacao_scores[$key]) !!}<span style="color:#e2e8f0;">{!! str_repeat('★', max(0, 5 - (int)$s->avaliacao_scores[$key])) !!}</span></p>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            @endif
                            @if ($s->avaliacao_comentario)
                                <p class="mt-4 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $s->avaliacao_comentario }}</p>
                            @endif
                            <p class="mt-3 text-xs text-slate-400">Registrada em {{ $s->avaliado_em?->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @endif
"""

# Insert avaliacao card in LEFT column right after header card (before "Resposta da empresa")
OLD_ANCHOR = "                {{-- Resposta da empresa --}}"
NEW_ANCHOR = AVALIACAO_LEFT + "\n" + OLD_ANCHOR
src = src.replace(OLD_ANCHOR, NEW_ANCHOR, 1)

# ── 2. REMOVE avaliacao from SIDEBAR (replace big block with stubs) ───────────
# The sidebar avaliacao podeReceberAvaliacao block
old_sidebar_block_start = "                    {{-- ★ AVALIAÇÃO — aparece primeiro se ainda não avaliou --}}\n                    @if ($s->podeReceberAvaliacao())"
# find and replace up to and including the @endif for avaliacao_estrelas block
# Find the pattern in sidebar
import re

# Replace sidebar avaliacao section with nothing (it's now in left column)
sidebar_pattern = r'                    \{\{-- ★ AVALIAÇÃO[^}]+--\}\}\n                    @if \(\$s->podeReceberAvaliacao\(\)\).*?@endif\n\n                    @if \(\$urlWa'
m = re.search(r'(\s+\{\{-- ★ AVALIAÇÃO.*?--\}\}\s+@if \(\$s->podeReceberAvaliacao\(\)\))(.*?)(\s+@endif\s+@elseif \(\$s->avaliacao_estrelas\))(.*?)(\s+@endif\s+\s+@if \(\$urlWa)', src, re.DOTALL)
if m:
    # Replace the whole block with just the WhatsApp link start
    replacement = "\n\n                    @if ($urlWa"
    src = src[:m.start()] + replacement + src[m.end() - len("\n\n                    @if ($urlWa"):]
    print("Sidebar avaliacao block removed")
else:
    print("WARNING: sidebar pattern not matched — trying alternative")
    # try simpler approach
    old_av_sidebar = src[src.index("                    {{-- ★ AVALIAÇÃO — aparece primeiro se ainda não avaliou --}}"):]
    print("First 200 chars after marker:", repr(old_av_sidebar[:200]))

# ── 3. Remove stubs "already rendered at top of sidebar" ─────────────────────
src = src.replace(
    "                    @if ($s->podeReceberAvaliacao())\n                        {{-- already rendered at top of sidebar --}}\n                    @elseif ($s->avaliacao_estrelas)\n                        {{-- already rendered at top of sidebar --}}\n                    @endif\n\n                    {{-- Info card --}}",
    "                    {{-- Info card --}}"
)

with open(path, 'wb') as f:
    f.write(src.encode('utf-8'))
print("Done. File size:", len(src))
