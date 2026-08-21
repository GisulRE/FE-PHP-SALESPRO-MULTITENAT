<div id="page" class="animate-bottom">

    @yield('content')

</div>
@yield('script')
<script type="text/javascript">
    $('.selectpicker').selectpicker({
        style: 'btn-link',
    });

    function setPage(url) {
        if (url && url !== '#') {
            window.location.href = url;
        }
    }

    $(document).ready(function() {
        // Limpiar estados de navegación obsoletos
        localStorage.removeItem('clicked');
        localStorage.removeItem('url');
    });
</script>
