<div id="page" class="animate-bottom">

    @yield('content')

</div>
@yield('script')
<script type="text/javascript">
    $('.selectpicker').selectpicker({
        style: 'btn-link',
    });
    //    capturarar asignar evento a las etiquetas a fuera del nav
    function updateURL(url) {
        history.pushState(null, null, url);
    }

    function setPage(url) {
        $('li').removeClass('active');
        localStorage.setItem('clicked', 1);
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#page').html(data);

                localStorage.removeItem('clicked');

                localStorage.removeItem('url');

                updateURL(url);
            },
            error: function() {
                alert('Hubo un error al cargar la página.');
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
