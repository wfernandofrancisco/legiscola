<x-layouts.admin>
    <x-slot name="title">Mapa de alunos</x-slot>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" />

    <x-breadcrumb :items="$breadcrumbs ?? []" />

    <x-page-header title="Mapa de alunos" subtitle="Visualize endereços com coordenadas e filtre por perfil ou matrícula em turmas/cursos." />

    <script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function studentMap() {
            const markersUrl = @json(route('admin.alunos.mapa.marcadores'));
            const defaultCenter = [-22.185556, -47.390278];
            const defaultZoom = 13;

            return {
                map: null,
                layer: null,
                mapLarge: false,
                error: '',
                metaText: '',
                filters: {
                    sexo: '',
                    bairro: '',
                    course_id: '',
                    course_class_id: '',
                    enrollment_status: '',
                },
                initMap() {
                    if (typeof L === 'undefined') {
                        this.error = 'Biblioteca do mapa não carregou. Verifique a rede ou bloqueio de script.';
                        return;
                    }
                    this.map = L.map('map-students').setView(defaultCenter, defaultZoom);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        maxZoom: 19,
                        attribution: '&copy; OpenStreetMap',
                    }).addTo(this.map);
                    this.layer = L.layerGroup().addTo(this.map);
                },
                toggleMapLarge() {
                    this.mapLarge = !this.mapLarge;
                    setTimeout(() => {
                        if (this.map) {
                            this.map.invalidateSize();
                        }
                    }, 220);
                },
                resetFilters() {
                    this.filters = {
                        sexo: '',
                        bairro: '',
                        course_id: '',
                        course_class_id: '',
                        enrollment_status: '',
                    };
                    this.loadMarkers();
                },
                buildQuery() {
                    const p = new URLSearchParams();
                    Object.entries(this.filters).forEach(([k, v]) => {
                        if (v !== '' && v !== null) {
                            p.set(k, v);
                        }
                    });
                    return p.toString();
                },
                escapeHtml(s) {
                    return String(s)
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;');
                },
                popupHtml(m) {
                    const lines = (m.enrollments || []).map((e) => {
                        const curso = e.curso || '—';
                        const turma = e.turma || '—';
                        const st = e.status || '—';
                        const ts = e.turma_status ? ` · turma: ${e.turma_status}` : '';
                        return `<li class="text-xs">${this.escapeHtml(curso)} — ${this.escapeHtml(turma)} <span class="text-gray-500">(${this.escapeHtml(st)}${ts})</span></li>`;
                    });
                    const list =
                        lines.length > 0 ?
                        `<ul class="list-disc pl-4 mt-1 space-y-0.5 max-h-40 overflow-y-auto">${lines.join('')}</ul>` :
                        '<p class="text-xs text-gray-500 mt-1">Sem matrícula em turma vinculada.</p>';
                    return (
                        `<div class="min-w-[200px]"><strong>${this.escapeHtml(m.name)}</strong><br>` +
                        `<span class="text-xs text-gray-600">${this.escapeHtml(m.matricula || '')}</span><br>` +
                        `<span class="text-xs">${this.escapeHtml(m.bairro || '—')} · ${this.escapeHtml(m.sexo || '—')}</span>` +
                        `<div class="mt-2 border-t border-gray-200 pt-2"><span class="text-xs font-medium">Matrículas</span>${list}</div></div>`
                    );
                },
                async loadMarkers() {
                    if (!this.map || !this.layer) {
                        return;
                    }
                    this.error = '';
                    this.metaText = 'Carregando…';
                    try {
                        const qs = this.buildQuery();
                        const url = qs ? `${markersUrl}?${qs}` : markersUrl;
                        const res = await fetch(url, {
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            credentials: 'same-origin',
                        });
                        if (!res.ok) {
                            throw new Error('Erro ao carregar marcadores');
                        }
                        const data = await res.json();
                        this.layer.clearLayers();
                        const pts = [];
                        (data.markers || []).forEach((m) => {
                            const mk = L.marker([m.lat, m.lng]).bindPopup(this.popupHtml(m));
                            mk.addTo(this.layer);
                            pts.push([m.lat, m.lng]);
                        });
                        if (pts.length === 1) {
                            this.map.setView(pts[0], 15);
                        } else if (pts.length > 1) {
                            this.map.fitBounds(pts, {
                                padding: [32, 32],
                                maxZoom: 16,
                            });
                        } else {
                            this.map.setView(defaultCenter, defaultZoom);
                        }
                        this.map.invalidateSize();
                        const skip = data.skipped_no_coords || 0;
                        this.metaText =
                            `${data.total_on_map || 0} aluno(s) no mapa` +
                            (skip > 0 ? ` · ${skip} sem latitude/longitude com os mesmos filtros` : '');
                    } catch (e) {
                        this.error = e.message || 'Falha na requisição';
                        this.metaText = '';
                    }
                },
            };
        }
    </script>

    <div class="space-y-6" x-data="studentMap()" x-init="initMap(); loadMarkers()"
        @resize.window="map && map.invalidateSize()">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Localização</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Sexo</label>
                        <select x-model="filters.sexo"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm p-2">
                            <option value="">Todos</option>
                            <option value="masculino">Masculino</option>
                            <option value="feminino">Feminino</option>
                            <option value="outro">Outro</option>
                            <option value="nao_informado">Não informado</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Bairro</label>
                        <select x-model="filters.bairro"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm p-2">
                            <option value="">Todos</option>
                            @foreach ($bairros as $row)
                                <option value="{{ $row->bairro }}">{{ $row->bairro }} ({{ $row->total }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm">
                <h2 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Turmas e cursos</h2>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Curso</label>
                        <select x-model="filters.course_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm p-2">
                            <option value="">Todos</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id }}">{{ $curso->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Turma</label>
                        <select x-model="filters.course_class_id"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm p-2">
                            <option value="">Todas</option>
                            @foreach ($turmas as $turma)
                                <option value="{{ $turma->id }}">{{ $turma->name }}
                                    @if ($turma->course)
                                        — {{ $turma->course->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Situação da
                            matrícula</label>
                        <select x-model="filters.enrollment_status"
                            class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm p-2">
                            <option value="">Qualquer (respeita curso/turma se escolhidos)</option>
                            <option value="em_andamento">Em andamento (inscrito ou cursando, turma não cancelada)
                            </option>
                            <option value="desistido">Desistiu</option>
                            <option value="concluido">Concluiu</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Combine com curso e/ou turma para
                            refinar. No mapa, o popup lista matrículas em turmas com curso.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" @click="loadMarkers()"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                Aplicar filtros
            </button>
            <button type="button" @click="resetFilters()"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/60">
                Limpar
            </button>
        </div>

        <p class="text-xs text-gray-500 dark:text-gray-400" x-show="metaText" x-text="metaText"></p>
        <p class="text-xs text-amber-700 dark:text-amber-300" x-show="error" x-text="error"></p>

        <div
            class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden flex flex-col">
            <div
                class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 dark:border-gray-700 px-4 py-3 bg-gray-50/90 dark:bg-gray-800/90">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">Mapa</span>
                <div class="flex flex-wrap items-center gap-2">
                    <button type="button" @click="toggleMapLarge()"
                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-medium transition"
                        :class="mapLarge ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700/60'">
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                        </svg>
                        <span x-text="mapLarge ? 'Mapa normal' : 'Ampliar mapa'"></span>
                    </button>
                </div>
            </div>
            <div id="map-students" class="w-full z-0 shrink-0 transition-[height] duration-200 ease-out"
                :class="mapLarge ? 'h-[min(82vh,880px)] min-h-[480px]' : 'h-[min(48vh,420px)] min-h-[280px]'"></div>
        </div>
    </div>

</x-layouts.admin>
