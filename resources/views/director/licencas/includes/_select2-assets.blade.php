@push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Evita o Select2 empurrar a página e criar barra horizontal. */
        .select2-wrap {
            min-width: 0;
            max-width: 100%;
            position: relative;
        }
        .select2-container { width: 100% !important; max-width: 100% !important; }
        .select2-container--open { z-index: 40; }
        .select2-dropdown {
            box-sizing: border-box;
            max-width: 100%;
            overflow-x: hidden;
        }
        .select2-results__option {
            word-break: break-word;
            white-space: normal;
        }
        .select2-container--default .select2-selection--single {
            height: 42px;
            border: 1px solid #cbd5e1;
            border-radius: 0.5rem;
            padding: 6px 8px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 28px;
            color: #0f172a;
            padding-left: 4px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-radius: 0.5rem;
            border-color: #cbd5e1;
            padding: 0.5rem 0.75rem;
            width: 100% !important;
            box-sizing: border-box;
        }
        .dark .select2-container--default .select2-selection--single {
            background: #1e293b;
            border-color: #475569;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9;
        }
        .dark .select2-dropdown {
            background: #1e293b;
            border-color: #475569;
            color: #f1f5f9;
        }
        .dark .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background: #4f46e5;
        }
        .dark .select2-container--default .select2-search--dropdown .select2-search__field {
            background: #0f172a;
            border-color: #475569;
            color: #f1f5f9;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.jQuery || !jQuery.fn.select2) return;

            jQuery('.js-select2').each(function () {
                const $el = jQuery(this);
                const $parent = $el.closest('.select2-wrap');

                $el.select2({
                    width: '100%',
                    // Dropdown fica dentro do campo — não anexa no body (causa overflow horizontal).
                    dropdownParent: $parent.length ? $parent : $el.parent(),
                    placeholder: $el.data('placeholder') || 'Selecione',
                    allowClear: ! $el.prop('required'),
                    language: {
                        noResults: function () { return 'Nenhum resultado'; },
                        searching: function () { return 'Buscando…'; },
                    },
                });
            });

            // Select2 não dispara change nativo do Alpine; sincroniza o tipo do item.
            jQuery('#catalog_item_id').on('change', function () {
                const form = this.closest('form');
                if (form && form._x_dataStack && form._x_dataStack[0]?.syncTipoFromSelect) {
                    form._x_dataStack[0].syncTipoFromSelect();
                }
            });
        });
    </script>
@endpush
