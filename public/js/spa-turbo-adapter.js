/**
 * SPA Turbo Adapter for SalesPro / Laravel Blade
 * Integrates Hotwire Turbo Drive with jQuery, Bootstrap, Selectpicker, Datepickers, and DataTables
 */
(function() {
    'use strict';

    // 1. Configurar Turbo CSRF Token en todas las peticiones fetch de Turbo
    document.addEventListener('turbo:before-fetch-request', function(event) {
        var token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            event.detail.fetchOptions.headers['X-CSRF-TOKEN'] = token.getAttribute('content');
        }
        event.detail.fetchOptions.headers['X-Requested-With'] = 'XMLHttpRequest';
    });

    // 2. Antes de guardar la página en caché de Turbo: limpiar modales, selectpickers y DataTables
    document.addEventListener('turbo:before-cache', function() {
        if (typeof $ !== 'undefined') {
            $('.modal.show').modal('hide');
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open').css('padding-right', '');
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip('dispose');
            }
            $('.dropdown-menu.show').removeClass('show');

            if ($.fn.DataTable) {
                $('table.dataTable').each(function() {
                    try {
                        if ($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().destroy();
                        }
                    } catch (e) {}
                });
            }
        }
    });

    // 3. Al cargar una nueva página con Turbo (o en la primera carga tradicional)
    document.addEventListener('turbo:load', function() {
        if (typeof $ === 'undefined') return;

        try {
            if ($.fn.tooltip) {
                $('[data-toggle="tooltip"]').tooltip({ boundary: 'window' });
            }
        } catch (e) {}

        try {
            if ($.fn.selectpicker) {
                $('.selectpicker').selectpicker('refresh');
            }
        } catch (e) {
            try {
                $('.selectpicker').selectpicker();
            } catch (err) {}
        }

        window.scrollTo({ top: 0, behavior: 'instant' });
    });

})();
