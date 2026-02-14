<script>
    swal.setDefaults({ 
        buttons: {

            cancel: {
            text: "Cancelar",
            value: null,
            visible: false,
            className: "",
            closeModal: true,
            },
            confirm: {
                text: "Confirmar",
                value: true,
                visible: true,
                className: "",
                closeModal: true
            }
        }
    });

    $('.siat-sincronizacion').submit(function(e) {
        e.preventDefault();
        swal({
        title: "¿Está seguro?",
        text: "Este proceso tiene por objetivo borrar los datos, y luego insertar datos de SIAT*",
        icon: "warning",
        buttons: true,
        dangerMode: true,
        })
        .then((status) => {
        if (status) {
            this.submit();
        } else {
            swal("Datos no cambiados!");
        }
        });
    });
</script>