/**
 * Tenants Management
 * Filtros, busca AJAX, modais e dropdowns
 */

document.addEventListener('DOMContentLoaded', function () {
    (function () {
        // ── Elementos do DOM ────────────────────────────────────────────────
        const tenantsTableWrapper = document.getElementById('tenants-table-wrapper');
        const tenantFilterForm = document.getElementById('tenant-filter-form');
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status"]');
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

            const sortByInput = tenantFilterForm?.querySelector('[name="sort_by"]');
            const sortDirInput = tenantFilterForm?.querySelector('[name="sort_dir"]');

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
            if (!tenantsTableWrapper) return;

            tenantsTableWrapper.style.opacity = '0.5';

            fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    tenantsTableWrapper.innerHTML = html;
                    tenantsTableWrapper.style.opacity = '1';
                    window.history.pushState(null, '', url);
                })
                .catch(error => {
                    console.error('Erro ao carregar tabela:', error);
                    tenantsTableWrapper.style.opacity = '1';
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

        // ── Delegação para links de paginação e ordenação ────────────────────
        document.addEventListener('click', function (e) {
            const link = e.target.closest('#tenants-table-wrapper a[href]');

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
                    const sortByInput = tenantFilterForm?.querySelector('[name="sort_by"]');
                    if (sortByInput) sortByInput.value = sortByParam;
                }
                if (sortDirParam) {
                    const sortDirInput = tenantFilterForm?.querySelector('[name="sort_dir"]');
                    if (sortDirInput) sortDirInput.value = sortDirParam;
                }
            } catch (err) {
                console.warn('Erro ao parsear URL de sort:', err);
            }

            loadTable(url);
        });

        // ── Dropdown de ações ───────────────────────────────────────────────
        window.toggleActionsDropdown = function (btn, id) {
            const menus = document.querySelectorAll('[data-dropdown]');
            const menu = document.getElementById('actions-menu-' + id);

            if (!menu) return;

            const isOpen = menu.style.display !== 'none';

            // Fecha todos os menus
            menus.forEach(m => {
                m.style.display = 'none';
            });

            if (isOpen) return;

            // Mede o menu antes de confirmar posição
            menu.style.visibility = 'hidden';
            menu.style.display = 'block';
            const menuHeight = menu.offsetHeight;
            const menuWidth = menu.offsetWidth;
            menu.style.visibility = '';

            const btnRect = btn.getBoundingClientRect();
            let top = btnRect.top - menuHeight - 8;
            let left = btnRect.left + (btnRect.width / 2) - (menuWidth / 2);

            // Mantém dentro da viewport
            if (left < 8) left = 8;
            if (left + menuWidth > window.innerWidth - 8) {
                left = window.innerWidth - menuWidth - 8;
            }
            if (top < 8) {
                top = btnRect.bottom + 8; // Abre abaixo se não caber acima
            }

            menu.style.top = top + 'px';
            menu.style.left = left + 'px';
        };

        // Fecha menus ao clicar fora
        document.addEventListener('click', function (e) {
            const isDropdownBtn = e.target.closest('[data-dropdown-trigger]');
            const isDropdownMenu = e.target.closest('[data-dropdown]');

            if (!isDropdownBtn && !isDropdownMenu) {
                document.querySelectorAll('[data-dropdown]').forEach(m => {
                    m.style.display = 'none';
                });
            }
        });

        // ── Modal de visualização ───────────────────────────────────────────
        window.openTenantModal = function (url) {
            const modal = document.getElementById('tenant-show-modal');
            const content = document.getElementById('tenant-show-content');
            const fullLink = document.getElementById('tenant-show-full-link');

            if (!modal || !content || !fullLink) return;

            // Mostra loading
            content.innerHTML = `
            <div class="flex items-center justify-center h-32">
                <div class="w-8 h-8 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
            </div>
        `;

            fullLink.href = url;
            modal.style.display = 'flex';

            // Carrega conteúdo
            fetch(url + '?embedded=1', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => response.text())
                .then(html => {
                    content.innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro ao carregar modal:', error);
                    content.innerHTML = `
                <p class="text-center text-red-500 py-8">
                    Erro ao carregar.
                    <a href="${url}" class="underline">Abrir em nova aba</a>
                </p>
            `;
                });
        };

        window.closeTenantModal = function () {
            const modal = document.getElementById('tenant-show-modal');
            if (modal) {
                modal.style.display = 'none';
            }
        };

        // Fecha modal ao clicar no fundo
        const modal = document.getElementById('tenant-show-modal');
        if (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === this) {
                    window.closeTenantModal();
                }
            });
        }
    })();
});