// onkeyup="this.value=this.value.replace(/[^0-9]/g,'');"
$('select[name="paid_by_id_select"]').on("change", function() {
    $("#number_card").prop("required", false)
    refrescarMontos();
    hideDatosInputTexto();
    alertaTablaItem_o_Empleado_vacio();
    var id = $(this).val();
    $(".payment-form").off("submit");
    if (checkStatusIntervalId) clearInterval(checkStatusIntervalId);
    if (timerIntervalId) clearInterval(timerIntervalId);
    if (id == 27) {    
        giftCard();
    } 
    if (id == 2) {
        creditCard();
    } 
    if (id == 3) {
        cheque();
    } 
    if (id == 1) {
        unblockAmounts();
    }
    if (id == 8) {
        deposits();
    }


    metodosPago($(this));

});

function metodosPago(dom){
    return new Promise(resolve => {
        var descripcion = $('option:selected',dom).data("descripcion");
        const MPpagos = [
            {metodo:'TARJETA',id:"MPtarjeta"},
            {metodo:'GIFT',id:"MPgiftCard"},
            {metodo:'CHEQUE',id:"MPcheque"},
            {metodo:'VALE',id:"MPvale"},
            {metodo:'OTRO',id:"MPotros"},
            {metodo:'PAGO POSTERIOR',id:"MPpagoPosterior"},
            {metodo:'TRANSFERENCIA BANCARIA',id:"MPtransferenciaBancaria"},
            {metodo:'DEBITO AUTOMATICO',id:"MPdebitoAutomatico"},
            {metodo:'DEPOSITO',id:"MPdepositoCuenta"},
            {metodo:'SWIFT',id:"MPtransferenciaSwift"},
            {metodo:'CANAL',id:"MPcanalPago"},
            {metodo:'BILLETERA',id:"MPbilleteraMovil"},
            {metodo:'ONLINE',id:"MPpagoOnline"},
            {metodo:'EFECTIVO',id:"MPefectivo"},
        ];
        MPpagos.map(x=>{
            if (descripcion.includes(x.metodo)){
                window[x.id]();
            }else{
                $("#"+x.id).hide();
            }
        });
        resolve(true)
    });
}

function ValidacionMetodoPago() {
    var suma = parseFloat($('#add-payment input[name="balance_gift_card"]').val());
    var montoTotal = parseFloat($("#grand-total").text());
    var montoCambio = false;
    var arrayID = [];
    $('#html_montos_metodos_de_pago').find('input').each(function() {
        if ($(this).attr('id')!=="numeroCheque" && $(this).attr('id')!=="montoCambio"){
            suma = suma + parseFloat($(this).val());
        }
        if ($(this).attr('id')!=="montoCambio"){
            montoCambio = true
        }
        arrayID.push($(this).attr('id'));
    });
    var total_balance = suma;
    var totalus = total_balance / tc;
    $('input[name="paying_amount"]').val(total_balance.toFixed(2));
    $('input[name="paying_amount_us"]').val(totalus.toFixed(2));

    if (montoCambio){
        let diferencia = suma - montoTotal
        if (diferencia <= 0 ){
            diferencia = 0;
        }
        suma = suma - diferencia;
        $("#montoCambio").val(parseFloat(diferencia).toFixed(2));
        $("#change").text(parseFloat(total_balance - montoTotal).toFixed(2));
    }
    

    if (suma===montoTotal){
        // Todo correcto
        arrayID.map(x=>{
            $("#"+x).removeClass('is-invalid');
            $("#"+x).addClass('is-valid');
            if ($("input[name='bandera_factura_hidden']").val() == true) {
                $("#segundoTabContinue").removeClass("disabled noselect");
                $('#myTab a[href="#segundoTab"]').removeClass("disabled noselect");
            } 
            guardarMetodosPagos();
        });
    }else{
        // Todo incorrecto
        arrayID.map(x=>{
            $("#"+x).removeClass('is-valid');
            $("#"+x).addClass('is-invalid');
            if ($("input[name='bandera_factura_hidden']").val() == true) {
                $("#segundoTabContinue").addClass("disabled noselect");
                $('#myTab a[href="#segundoTab"]').addClass("disabled noselect");
            }
        });
    }
}

function guardarMetodosPagos() {

    $('input[name="monto_efectivo"]').val($("#montoEfectivo").val());
    $('input[name="monto_tarjeta"]').val($("#montoTarjeta").val());
    $('input[name="monto_cheque"]').val($("#montoCheque").val());
    $('input[name="monto_vale"]').val($("#montoVale").val());
    $('input[name="monto_otros"]').val($("#montoOtros").val());
    $('input[name="monto_pago_posterior"]').val($("#montoPagoPosterior").val());
    $('input[name="monto_transferencia_bancaria"]').val($("#montoTransferenciaBancaria").val());
    $('input[name="monto_deposito"]').val($("#montoDepositoCuenta").val());
    $('input[name="monto_swift"]').val($("#montoSwift").val());
    $('input[name="monto_cambio"]').val($("#montoCambio").val());
    
    $('input[name="monto_canal_pago"]').val($("#montoCanalPago").val());
    $('input[name="monto_billetera"]').val($("#montoBilleteraMovil").val());
    $('input[name="monto_pago_online"]').val($("#montoPagoOnline").val());
    $('input[name="monto_debito_automatico"]').val($("#montoDebitoAutomatico").val());
}

function MPtarjeta(){
    $("#MP_tarjeta").show();
    let html = `
        <div class="col form-group">
            <label>Monto Tarjeta</label>
            <input id="montoTarjeta" class="form-control" onkeyup="ValidacionMetodoPago()" type="number" step="0.01" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
        </div>`
    $("#html_montos_metodos_de_pago").append(html);
    $("#number_card").prop("required", true)
}

function MPgiftCard(){
    giftCard();
}

function MPcheque(){
    $("#MP_cheque").show();
    let html = `
        <div class="col-lg-6 form-group">
            <label>Monto Cheque</label>
            <input id="montoCheque" class="form-control" onkeyup="ValidacionMetodoPago()" type="number" step="0.01" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
        </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPefectivo(){
    let html = `
    <div class="col form-group">
        <label>Monto Efectivo</label>
        <input id="montoEfectivo" class="form-control" onkeyup="ValidacionMetodoPago();" type="number" step="0.01" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>
    <div class="col-6 form-group">
        <label>Monto Cambio</label>
        <input readonly id="montoCambio" class="form-control" type="number" step="0.01" min="0" max="1000000" value="0" />
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPvale(){
    let html = `<div class="col form-group">
        <label>Monto Vale</label>
        <input id="montoVale" type="number" class="form-control" onkeyup="ValidacionMetodoPago()" step="0.01" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPotros(){
    let html = `<div class="col form-group">
        <label>Monto Otros</label>
        <input id="montoOtros" type="number" step="0.01" class="form-control" onkeyup="ValidacionMetodoPago()" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPpagoPosterior(){
    let html = `<div class="col form-group">
        <label>Monto Pago Posterior</label>
        <input id="montoPagoPosterior" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPtransferenciaBancaria(){
    let html = `<div class="col form-group">
        <label>Monto Transferencia Bancaria</label>
        <input id="montoTransferenciaBancaria" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPdepositoCuenta(){
    let html = `<div class="col-12 form-group">
        <label>Monto Dep. en Cuenta</label>
        <input id="montoDepositoCuenta" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`
    $("#html_montos_metodos_de_pago").append(html);
}

function MPtransferenciaSwift(){
    let html = `
    <div class="col form-group">
        <label>Monto Transferencia Swift</label>
        <input id="montoSwift" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`;
    $("#html_montos_metodos_de_pago").append(html);
}

function MPcanalPago(){
    let html = `
    <div class="col form-group">
        <label>Monto Canal de Pago</label>
        <input id="montoCanalPago" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`;
    $("#html_montos_metodos_de_pago").append(html);
}

function MPbilleteraMovil(){
    let html = `
    <div class="col form-group">
        <label>Monto Billetera Móvil</label>
        <input id="montoBilleteraMovil" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`;
    $("#html_montos_metodos_de_pago").append(html);
}

function MPpagoOnline(){
    let html = `
    <div class="col form-group">
        <label>Monto Pago Online</label>
        <input id="montoPagoOnline" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`;
    $("#html_montos_metodos_de_pago").append(html);
}

function MPdebitoAutomatico(){
    let html = `
    <div class="col form-group">
        <label>Monto Débito Automático</label>
        <input id="montoDebitoAutomatico" type="number" step="0.01" onkeyup="ValidacionMetodoPago()" class="form-control" min="0" max="1000000" value="0" style="background-color: #aaff2f;"/>
    </div>`;
    $("#html_montos_metodos_de_pago").append(html);
}