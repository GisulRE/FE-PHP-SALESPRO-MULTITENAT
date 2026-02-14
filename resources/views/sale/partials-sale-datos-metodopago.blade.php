<script>

    // si el usuario cierra el modal al facturar, se debe mover a panel de montos
    function mostrarPanelMontosModal() {
        if ($("input[name='bandera_factura_hidden']").val() == 1) {
            $('#myTab a[href="#primerTab"]').tab('show');
        }
    }

    function refrescarMontos() {
        $('input[name="paying_amount"]').val('0.00');
        $('input[name="paying_amount_us"]').val('0.00');
        $("#change").text('0.00');
    }

    function hideDatosInputTexto() {
        blockAmounts();
        $("#html_montos_metodos_de_pago").empty();

        $("#MP_tarjeta").hide();
        $('input[name="number_card"]').val("");
        
        $("#MP_cheque").hide();
        $('input[name="cheque_no"]').val("");

        $("#MP_giftCard").hide();
        $('#add-payment input[name="balance_gift_card"]').val(0);

        $(".qrsimple").hide();

        $('.selectpicker').selectpicker('refresh');
    }
</script>