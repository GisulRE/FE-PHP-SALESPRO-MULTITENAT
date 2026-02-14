
{{-- Caja de pago: GiftCard --}}
<div class="col-md-12 mt-1" id="MP_giftCard">
    <input type="hidden" name="tarjeta_regalo_hidden_id">
    {{-- monto que tiene gift --}}
    <input type="hidden" name="saldo_gift_card">
    {{-- monto que pagará con la gift --}}
    <input type="hidden" name="balance_gift_card" value="0"> 
    <label>Tarjeta de regalo</label>
    <select name="tarjeta_regalo_hidden" class="form-control selectpicker" class="selectpicker form-control" data-live-search="true" data-live-search-style="contains" title="Seleccionar tarjeta de regalo...">
    </select>
    <div class="input-group-append datos_visual_giftcard">
        <div class="form-check form-check-inline">
            <button type="button" class="btn btn-link btn-sm" onclick="editarMontoGiftCard();" ><i class="dripicons-document-edit"></i></button>
            <span class="badge badge-pill badge-info badge-credito"></span>
        </div>
        <div class="form-check form-check-inline">
            <span class="badge badge-pill badge-secondary badge-debito"></span>
        </div>
        <div class="form-check form-check-inline">
            <span class="badge badge-pill badge-light badge-saldo-total"></span>
        </div>
    </div>
    
</div>


<script>
    $("#MP_giftCard").hide();
    $('.datos_visual_giftcard').hide();

    //gift card solo para pago combinado
    function giftCard() {
        $("#MP_giftCard").show(); 
        $('.datos_visual_giftcard').hide(); 
        var id_c = $("#customer_id").val();
        $.ajax({
            url: 'sales/get_gift_card/'+id_c,
            type: "GET",
            dataType: "json",
            success:function(data) {
                $('#add-payment select[name="tarjeta_regalo_hidden"]').empty();
                $.each(data, function(index) {
                    gift_card_amount[data[index]['id']] = data[index]['amount'];
                    gift_card_expense[data[index]['id']] = data[index]['expense'];
                    $('#add-payment select[name="tarjeta_regalo_hidden"]').append('<option value="'+ data[index]['id'] +'">'+ data[index]['card_no'] +'</option>');
                });
                $('.selectpicker').selectpicker('refresh');
                $('.selectpicker').selectpicker();
            }
        });
    }

    $('#add-payment select[name="tarjeta_regalo_hidden"]').on("change", function() {
        var balance = gift_card_amount[$(this).val()] - gift_card_expense[$(this).val()];
        // insertamos la id de la tarjeta seleccionada y guardamos el balance total.
        $('#add-payment input[name="tarjeta_regalo_hidden_id"]').val($(this).val());
        $('#add-payment input[name="saldo_gift_card"]').val(balance);
        
        
        // Se muestra un alert mensaje, de cuanto es el balance de la Gift Card 
        if($('input[name="paid_amount"]').val() > balance){
            title = 'La cantidad excede el saldo de la tarjeta! ';
            mostrarSwal(balance, title, 0, balance);
        }else{
            monto_a_pagar = $('input[name="paid_amount"]').val();
            title = 'Saldo de la tarjeta suficiente! ';
            mostrarSwal(balance, title, monto_a_pagar, monto_a_pagar);
        }
    });

    // Código que permite a sweetAlert editar el monto de la tarjeta de regalo
    $('#add-payment').on('shown.bs.modal', function() {
        $(document).off('focusin.modal');
    });

    // Alerta visual de la Tarjeta de Regalo, en pago combinado.
    function mostrarSwal(balance, title, monto_a_pagar, montoSeleccionado) {
        var inputValue = balance
        var setSeleccionado = montoSeleccionado
        if (monto_a_pagar>0) {
            inputValue = monto_a_pagar
        }

        Swal.fire({
            icon: 'info',
            timerProgressBar: true,
            title: title,
            inputLabel: 'Saldo de la tarjeta de regalo: '+parseFloat(balance).toFixed(2)+  ' BS',
            input: 'number',
            inputValue: setSeleccionado,
            inputAttributes: {
                min: 0.01,
                max: inputValue,
                step: 0.01
            },
            inputValidator: (value) => {
                if (value > inputValue) {
                    return 'La cantidad ingresada supera el saldo de la tarjeta de regalo.'
                }
                if (value < 0) {
                    return 'La cantidad ingresada no tiene sentido'
                }
                if (value == 0) {
                    return 'Valor mínimo aceptado es 0.01' ;
                }
            },
        }).then((result) =>{
            if (result.isConfirmed) {   
                var inputNumber = Swal.getInput()
                var valorSeleccionado = inputNumber.value
                resolverBalanceGiftCard(valorSeleccionado) 
                
            }
        });
    }

    function resolverBalanceGiftCard(inputValue) {
        var setBalance = parseFloat(inputValue).toFixed(2);
        
        //procedemos a mostrar la resta de la gift card al [Monto a Pagar] y [Monto Recibido] 
        //para que usuario pueda ver cuando es el monto faltante para finalizar la compra
        var totalbs = parseFloat($('input[name="paying_amount"]').val());
        var total_balance = (setBalance + totalbs);
        var totalus = total_balance / tc;
        $('input[name="paying_amount"]').val(total_balance);
        $('input[name="paying_amount_us"]').val(totalus);

        //mostramos datos del balance de la tarjeta
        var gift_selected = parseFloat($('#add-payment select[name="tarjeta_regalo_hidden"]').val()).toFixed(2);
        saldo_gift_card = parseFloat($('#add-payment input[name="saldo_gift_card"]').val()).toFixed(2);
        saldo_total = saldo_gift_card-setBalance;
        $('.badge-credito').text('Crédito: '+ saldo_gift_card);
        $('.badge-debito').text('Débito: '+ setBalance); 
        $('.badge-saldo-total').text('Saldo total: '+ saldo_total.toFixed(2)); 
        $('.datos_visual_giftcard').show();
        //guardamos el balance de la gift card 
        $('#add-payment input[name="balance_gift_card"]').val(setBalance);
        console.log("montoGifCard = "+ setBalance);
        ValidacionMetodoPago();
    }

    function editarMontoGiftCard() {
        
        var totalbs = parseFloat($("#grand-total").text());
        var balance = $('#add-payment input[name="saldo_gift_card"]').val();       

        montoSeleccionado = (($('#add-payment input[name="balance_gift_card"]').val()));

        console.log("montoGifCard preseleccionado => "+ montoSeleccionado);
        //Se muestra un alert mensaje, de cuanto es el balance de la Gift Card 
        if(totalbs > balance){
            title = 'La cantidad excede el saldo de la tarjeta! ';
            mostrarSwal(balance, title, 0, montoSeleccionado);
        }
        else{
            monto_a_pagar = totalbs;
            title = 'Saldo de la tarjeta suficiente! ';
            mostrarSwal(balance, title, monto_a_pagar, montoSeleccionado);
        }
    }
</script>