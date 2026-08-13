/**
 * Admin Users Management
 * Filtros, busca AJAX and pagination
 */

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        // ── Elementos do DOM ────────────────────────────────────────────────
        const usersTableWrapper = document.getElementById('users-table-wrapper');
        const userFilterForm = document.getElementById('user-filter-form');
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
        const userTypeSelect = document.querySelector('select[name="user_type"]');
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/$/, '');

        let filterTimer = null;

        // ── Construir parâmetros de filtro ──────────────────────────────────
        function buildParams() {
            const params = new URLSearchParams();

            if (searchInput && searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }

            if (statusSelect && statusSelect.value) {
                params.set('status', statusSelect.value);
            }

            if (userTypeSelect && userTypeSelect.value) {
                params.set('user_type', userTypeSelect.value);
            }

            const sortByInput = userFilterForm?.querySelector('[name="sort_by"]');
            const sortDirInput = userFilterForm?.querySelector('[name="sort_dir"]');

            if (sortByInput?.value) {
                params.set('sort_by', sortByInput.value);
            }
            if (sortDirInput?.value) {
                params.set('sort_dir', sortDirInput.value);
            }

            return params;
        }

        // ── Carregar tabela via AJAX ────────────────────────────────────────
        function loadTable(url) {
            if (!usersTableWrapper) return;

            usersTableWrapper.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    usersTableWrapper.innerHTML = html;
                    usersTableWrapper.style.opacity = '1';
                    window.history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error('Erro ao carregar tabela:', error);
                    usersTableWrapper.style.opacity = '1';
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

        if (statusSelect) {
            statusSelect.addEventListener('change', function () {
                clearTimeout(filterTimer);
                applyFilters();
            });
        }

        if (userTypeSelect) {
            userTypeSelect.addEventListener('change', function () {
                clearTimeout(filterTimer);
                applyFilters();
            });
        }

        // ── Delegação para links de paginação e ordenação ────────────────────
        document.addEventListener('click', function (e) {
            const link = e.target.closest('#users-table-wrapper a[href]');

            if (!link) return;

            const url = link.getAttribute('href');
            if (!url || url === '#') return;

            let parsed;

            try {
                parsed = new URL(url, window.location.origin);
            } catch (err) {
                console.warn('Erro ao parsear URL da tabela:', err);
                return;
            }

            // Intercepta apenas links da propria listagem (paginacao/ordenacao).
            // Links como editar/deletar devem seguir navegacao normal.
            if (parsed.pathname.replace(/\/$/, '') !== window.location.pathname.replace(/\/$/, '')) {
                return;
            }

            e.preventDefault();

            // Sincroniza hidden inputs de sort com a URL clicada
            try {
                const sortByParam = parsed.searchParams.get('sort_by');
                const sortDirParam = parsed.searchParams.get('sort_dir');

                if (sortByParam) {
                    const sortByInput = userFilterForm?.querySelector('[name="sort_by"]');
                    if (sortByInput) sortByInput.value = sortByParam;
                }
                if (sortDirParam) {
                    const sortDirInput = userFilterForm?.querySelector('[name="sort_dir"]');
                    if (sortDirInput) sortDirInput.value = sortDirParam;
                }
            } catch (err) {
                console.warn('Erro ao parsear URL de sort:', err);
            }

            loadTable(url);
        });
    })();
});
