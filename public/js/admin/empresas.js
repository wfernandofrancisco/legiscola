document.addEventListener('DOMContentLoaded', function () {
    (function () {
        const tableWrapper = document.getElementById('empresas-table-wrapper');
        const filterForm = document.getElementById('empresa-filter-form');
        const searchInput = document.querySelector('input[name="search"]');
        const statusSelect = document.querySelector('select[name="status_dados"]');
        const situacaoSelect = document.querySelector('select[name="situacao_cadastral"]');
        const baseUrl = window.location.origin + window.location.pathname.replace(/\/$/, '');

        let filterTimer = null;

        function buildParams() {
            const params = new URLSearchParams();

            if (searchInput && searchInput.value.trim()) {
                params.set('search', searchInput.value.trim());
            }

            if (statusSelect && statusSelect.value) {
                params.set('status_dados', statusSelect.value);
            }
            if (situacaoSelect && situacaoSelect.value) {
                params.set('situacao_cadastral', situacaoSelect.value);
            }

            const sortByInput = filterForm?.querySelector('[name="sort_by"]');
            const sortDirInput = filterForm?.querySelector('[name="sort_dir"]');

            if (sortByInput?.value) {
                params.set('sort_by', sortByInput.value);
            }
            if (sortDirInput?.value) {
                params.set('sort_dir', sortDirInput.value);
            }

            return params;
        }

        function syncFormFromUrl() {
            let params;
            try {
                params = new URLSearchParams(window.location.search);
            } catch (e) {
                return;
            }
            if (searchInput) {
                searchInput.value = params.get('search') || '';
            }
            if (statusSelect) {
                statusSelect.value = params.get('status_dados') || '';
            }
            if (situacaoSelect) {
                situacaoSelect.value = params.get('situacao_cadastral') || '';
            }
            const sortByInput = filterForm?.querySelector('[name="sort_by"]');
            const sortDirInput = filterForm?.querySelector('[name="sort_dir"]');
            if (sortByInput) {
                sortByInput.value = params.get('sort_by') || '';
            }
            if (sortDirInput) {
                sortDirInput.value = params.get('sort_dir') || 'desc';
            }
        }

        function loadTable(url, options) {
            if (!tableWrapper) return;

            const push = options && options.pushState === false ? false : true;

            tableWrapper.style.opacity = '0.5';
            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then((response) => response.text())
                .then((html) => {
                    tableWrapper.innerHTML = html;
                    tableWrapper.style.opacity = '1';
                    if (push) {
                        window.history.pushState(null, '', url);
                    }
                })
                .catch((error) => {
                    console.error('Erro ao carregar empresas:', error);
                    tableWrapper.style.opacity = '1';
                });
        }

        function applyFilters() {
            const params = buildParams();
            const queryString = params.toString();
            const url = baseUrl + (queryString ? '?' + queryString : '');
            loadTable(url);
        }

        window.addEventListener('popstate', function () {
            syncFormFromUrl();
            loadTable(window.location.href, { pushState: false });
        });

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

        if (situacaoSelect) {
            situacaoSelect.addEventListener('change', function () {
                clearTimeout(filterTimer);
                applyFilters();
            });
        }

        document.addEventListener('click', function (e) {
            const link = e.target.closest('#empresas-table-wrapper a[href]');
            if (!link) return;

            const url = link.getAttribute('href');
            if (!url || url === '#') return;

            let parsed;
            try {
                parsed = new URL(url, window.location.origin);
            } catch (err) {
                return;
            }

            if (parsed.pathname.replace(/\/$/, '') !== window.location.pathname.replace(/\/$/, '')) {
                return;
            }

            e.preventDefault();

            const sortByParam = parsed.searchParams.get('sort_by');
            const sortDirParam = parsed.searchParams.get('sort_dir');

            const sortByInput = filterForm?.querySelector('[name="sort_by"]');
            const sortDirInput = filterForm?.querySelector('[name="sort_dir"]');

            if (sortByInput) sortByInput.value = sortByParam ?? '';
            if (sortDirInput) sortDirInput.value = sortDirParam ?? 'desc';

            loadTable(url);
        });
    })();
});
