/**
 * Permissions Management
 * Filtros, busca AJAX, modais e dropdowns
 */

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        // ── Elementos do DOM ────────────────────────────────────────────────
        const permissionsTableWrapper = document.getElementById('permissions-table-wrapper');
        const permissionFilterForm = document.getElementById('permission-filter-form');
        const searchInput = document.querySelector('input[name="search"]');
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/$/, '');

        let filterTimer = null;

        // ── Construir parâmetros de filtro ──────────────────────────────────
        function buildParams() {
            const params = new URLSearchParams();

            if (searchInput && searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }

            return params;
        }

        // ── Carregar tabela via AJAX ────────────────────────────────────────
        function loadTable(url) {
            if (!permissionsTableWrapper) return;

            permissionsTableWrapper.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    permissionsTableWrapper.innerHTML = html;
                    permissionsTableWrapper.style.opacity = '1';
                    window.history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error('Erro ao carregar tabela:', error);
                    permissionsTableWrapper.style.opacity = '1';
                });
        }

        // ── Aplicar filtros ────────────────────────────────────────────────
        function applyFilters() {
            const params = buildParams();
            const queryString = params.toString();
            const url = baseUrl + (queryString ? '?' + queryString : '');
            loadTable(url);
        }

        // ── Event listeners para filtros ───────────────────────────────────
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(applyFilters, 400);
            });
        }

        // ── Delegação para links de paginação ────────────────────
        document.addEventListener('click', function (e) {
            const link = e.target.closest('#permissions-table-wrapper a[href]');

            if (!link) return;

            const url = link.getAttribute('href');
            if (!url || url === '#') return;

            let parsed;

            try {
                parsed = new URL(url, window.location.origin);
            } catch (e) {
                return;
            }

            // Intercepta apenas links da propria listagem (paginacao/ordenacao).
            // Links como editar/criar/show devem seguir navegacao normal.
            if (parsed.pathname.replace(/\/$/, '') !== window.location.pathname.replace(/\/$/, '')) {
                return;
            }

            e.preventDefault();

            // Sincroniza hidden inputs de sort com a URL clicada
            try {
                const sortByParam = parsed.searchParams.get('sort_by');
                const sortDirParam = parsed.searchParams.get('sort_dir');

                if (sortByParam) {
                    const sortByInput = permissionFilterForm?.querySelector('[name="sort_by"]');
                    if (sortByInput) sortByInput.value = sortByParam;
                }
                if (sortDirParam) {
                    const sortDirInput = permissionFilterForm?.querySelector('[name="sort_dir"]');
                    if (sortDirInput) sortDirInput.value = sortDirParam;
                }
            } catch (err) {
                console.warn('Erro ao parsear URL de sort:', err);
            }

            loadTable(url);
        });

        // ── Interceptar submissão do formulário ────────────────────────────
        if (permissionFilterForm) {
            permissionFilterForm.addEventListener('submit', function (e) {
                e.preventDefault();
                applyFilters();
            });
        }

        // ── Suporte ao botão voltar do navegador ───────────────────────────
        window.addEventListener('popstate', function () {
            loadTable(window.location.href);
        });
    })();
});
