<div id="page" class="animate-bottom">
    @yield('content')
</div>
@yield('script')
<script type="text/javascript">
    // Actualiza la URL del navegador sin recargar la página
    function updateURL(url) {
        if (window.location.href !== url) {
            history.pushState(null, null, url);
        }
    }

    // Carga asíncrona de páginas y reemplazo de contenedor DOM
    function setPage(url, pushState = true) {
        if (!url || url === '#' || url.startsWith('javascript:') || url.startsWith('#')) {
            return;
        }

        $('li').removeClass('active');
        localStorage.setItem('clicked', 1);

        if (document.getElementById("loader")) {
            document.getElementById("loader").style.display = "block";
        }

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                // 1. Limpiar instancias previas de DataTables
                if ($.fn.DataTable) {
                    $.fn.dataTable.ext.errMode = 'none'; // Suprimir alertas emergentes de DataTables
                    $('#page table').each(function() {
                        try {
                            if ($.fn.DataTable.isDataTable(this)) {
                                $(this).DataTable().destroy();
                            }
                        } catch (e) {}
                    });
                }

                // 2. Limpiar instancias previas de Dropzone
                if (typeof Dropzone !== 'undefined') {
                    Dropzone.autoDiscover = false;
                    if (Dropzone.instances && Dropzone.instances.length) {
                        Dropzone.instances.forEach(function(dz) {
                            try { dz.destroy(); } catch(e) {}
                        });
                        Dropzone.instances = [];
                    }
                }

                // 3. Reemplaza únicamente el contenido del contenedor principal #page
                var $temp = $('<div>').append($.parseHTML(data, document, true));
                var newContent = $temp.find('#page').length ? $temp.find('#page').html() : data;
                
                $('#page').html(newContent);

                // 4. Ejecutar scripts que vengan en la nueva vista
                $temp.find('script').each(function() {
                    var src = $(this).attr('src');
                    if (!src) {
                        try {
                            $.globalEval($(this).text() || $(this).html());
                        } catch (e) {
                            console.log('Script inline:', e);
                        }
                    }
                });

                localStorage.removeItem('clicked');
                localStorage.removeItem('url');

                // Actualiza la barra de direcciones del navegador
                if (pushState) {
                    updateURL(url);
                }

                // Re-inicializa plugins dinámicos de UI
                if ($.fn.selectpicker) {
                    $('.selectpicker').selectpicker({
                        style: 'btn-link',
                    });
                    $('.selectpicker').selectpicker('refresh');
                }

                if (document.getElementById("loader")) {
                    document.getElementById("loader").style.display = "none";
                }

                window.scrollTo({ top: 0, behavior: 'instant' });
            },
            error: function(xhr, status, error) {
                if (document.getElementById("loader")) {
                    document.getElementById("loader").style.display = "none";
                }
                console.error('Error al cargar la página vía AJAX:', error);
                window.location.href = url; // Fallback tradicional
            }
        });
    }

    // Delegación de eventos para los enlaces del Menú Lateral
    $(document).ready(function () {
        localStorage.removeItem('clicked');
        localStorage.removeItem('url');

        if ($.fn.selectpicker) {
            $('.selectpicker').selectpicker({
                style: 'btn-link',
            });
        }

        // Interceptar clics en enlaces con la clase .stopReload o enlaces del sidebar
        $(document).on('click', '.stopReload, .side-navbar a.stopReload, .side-navbar a', function (e) {
            let $el = $(this);
            let url = $el.attr('href');
            let toggle = $el.attr('data-toggle');

            // Evitar interceptar toggles de colapso, modales, pestañas o enlaces vacíos
            if (toggle === 'collapse' || toggle === 'modal' || toggle === 'tab') {
                return;
            }
            
            if (url && url !== '#' && !url.startsWith('#') && !url.startsWith('javascript:')) {
                e.preventDefault();
                if (window.location.href !== url) {
                    setPage(url);
                }
            }
        });
    });

    // Soporte para navegación con botones atrás/adelante del navegador
    window.onpopstate = function(event) {
        setPage(window.location.href, false);
    };
</script>
