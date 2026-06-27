<div id="page" class="animate-bottom">

    @yield('content')

</div>
@yield('script')
<script type="text/javascript">
    $('.selectpicker').selectpicker({
        style: 'btn-link',
    });

    // Actualiza la URL del navegador sin recargar la página
    function updateURL(url) {
        history.pushState(null, null, url);
    }

    /**
     * setPage(url) — Carga una página vía AJAX e inyecta el contenido en #page.
     *
     * PROBLEMA RESUELTO: Algunos controllers devuelven el layout HTML completo
     * (<!DOCTYPE html>...) cuando se les llama vía AJAX, porque no distinguen
     * entre peticiones AJAX y peticiones normales. Cuando ese HTML completo se
     * inyectaba en #page, visualmente parecía que el sistema "iba a home".
     *
     * SOLUCIÓN: Se detecta si la respuesta contiene un DOCTYPE. Si es así, se
     * realiza una navegación normal (window.location.href) en vez de inyectar
     * el HTML. Esto asegura que el módulo cargue correctamente siempre.
     */
    function setPage(url) {
        $('li').removeClass('active');
        localStorage.setItem('clicked', 1);

        $.ajax({
            url: url,
            type: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(data) {
                // Detectar si la respuesta es una página HTML completa.
                // Esto ocurre cuando el controller devuelve una vista con la directiva
                // "extends" del layout principal y no tiene lógica para peticiones AJAX.
                // En ese caso navegamos normalmente para evitar que el layout completo
                // se incruste en #page.
                if (typeof data === 'string' && data.trim().toLowerCase().startsWith('<!doctype')) {
                    console.info('[setPage] Respuesta es HTML completo, navegando a:', url);
                    localStorage.removeItem('url');
                    // No eliminar 'clicked' aquí: la navegación completa lo necesita
                    // para que el guard de la página destino no redirija a home.
                    window.location.href = url;
                    return;
                }

                // Respuesta parcial: extraer solo el contenido del div#page interior
                // para evitar anidar dos divs con id="page" (el de main.blade.php
                // y el que trae layout.layout dentro de la respuesta AJAX).
                var content = data;
                try {
                    var doc = new DOMParser().parseFromString(data, 'text/html');
                    var innerPage = doc.getElementById('page');
                    if (innerPage) {
                        content = innerPage.innerHTML;
                    }
                } catch (e) { /* fallback: usar data completa */ }

                $('#page').html(content);
                localStorage.removeItem('clicked');
                localStorage.removeItem('url');
                updateURL(url);
            },
            error: function(xhr, status, error) {
                console.error('[setPage] Error AJAX', {
                    url: url,
                    status: xhr.status,
                    error: error
                });

                // Si el servidor devuelve un error de red o 5xx,
                // navegamos normalmente. 'clicked' debe mantenerse
                // para que el guard de la página destino no redirija a home.
                if (xhr.status === 0 || xhr.status >= 500) {
                    localStorage.removeItem('url');
                    window.location.href = url;
                } else {
                    localStorage.removeItem('clicked');
                    localStorage.removeItem('url');
                    alert('Hubo un error al cargar la página. (HTTP ' + xhr.status + ')');
                }
            }
        });
    }

    $(document).ready(function() {

        $('.stopReload_out').click(function(e) {
            e.preventDefault();

            let url = $(this).attr('href');
            if (window.location.href != url) {
                setPage(url);
            }
        });

    });
</script>
