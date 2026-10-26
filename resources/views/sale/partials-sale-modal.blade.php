<div class="row">
    <div class="form-group col-md-6">
        <label>Tipo de Factura</label>
        <div class="input-group">
            <select name="tipo_factura" id="tipo_factura_id" class="selectpicker form-control" title="Seleccione...">
                <option value="1" selected>FACTURA COMPRA-VENTA</option>
                <option value="2">FACTURA ALQUILER</option>
                <option value="13">FACTURA SERVICIO-BASICO</option>
            </select>
        </div>
    </div>
    <div id="glosa_tipo_factura" class="form-group col-md-6">
        <label>Período Facturado Alquiler</label>
        <input type="text" name="glosa_periodo_facturado" id="glosa_periodo_facturado" class="form-control"
            minlength="10" maxlength="50"
            onKeyUp="document.getElementById(this.id).value=document.getElementById(this.id).value.toUpperCase()">
    </div>
</div>
<div class="row">
    <div class="form-group col-md-6">
        <label>Casos especiales</label>
        <div class="input-group">
            <input type="hidden" name="sales_caso_especial_hidden">
            <select name="sales_caso_especial" id="sales_caso_especial_id" class="selectpicker form-control"
                title="Seleccione...">
                <option value="1" selected>Ninguna</option>
                <option value="2">99001 (Extranjero no inscrito)</option>
                <option value="3">99002 (Control Tributario)</option>
                <option value="4">99003 (Ventas Menores)</option>
            </select>
        </div>
    </div>
    @if ($lims_pos_setting_data->codigo_emision == 1)
        <div id="label_contingencia" class="form-group col text-center" style="display:none;">
            <div id="evento_contingencia_div" class="row">
                <div class="form-group col">
                    <label>Nro. Factura</label>
                    <input name="nro_factura_manual" onblur="consultarNroFacturaCorrelativo();" type="number"
                        min="0" step="1" class="form-control">
                </div>
                <div class="form-group col">
                    <label>Fecha emisión</label>
                    <input name="fecha_manual" onfocusout="revisarFechaManualCafc();" type="datetime-local"
                        step="any" class="form-control">
                </div>
            </div>
        </div>
    @endif
</div>
<div class="dropdown-divider"></div>


<div class="row">
    <div class="form-group col-md-6">
        <label>Tipo de Documento</label>
        <div class="input-group">
            <input type="hidden" name="sales_tipo_documento_hidden">
            <select name="sales_tipo_documento" id="sales_tipo_documento_id" class="selectpicker form-control"
                title="Seleccione documento...">
                @include('customer.partials-tipo-documentos')
            </select>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="setClientePredeterminado();"
                    id="botonSetClientePredeterminado"><i class="dripicons-pin"></i></button>
            </div>
        </div>
    </div>
    <div class="form-group col-md-4" id="sales_valor_documento">
        <label>Valor Documento</label>
        <div class="input-group">
            <input type="text" name="sales_valor_documento" class="form-control">
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" type="button" onclick="sales_consultarNIT();"
                    id="sales_verificarNIT"><i class="dripicons-search"></i></button>
            </div>
            <div class="invalid-feedback">
            </div>
            <div class="valid-feedback">
            </div>
        </div>
    </div>
    <div class="form-group col" id="sales_complemento">
        <label for="sales_complemento_documento">Complemento</label>
        <input type="text" name="sales_complemento_documento" class="form-control">
    </div>
</div>
<div class="row">
    <div class="col-md-5 mt-1">
        <label>Nombre Fiscal/Razón Social </label>
        <input type="text" name="sales_razon_social" id="sales_razon_social" class="form-control" maxlength="191" autocomplete="off">
    </div>
    <div class="col-md-5 mt-1" id="sales_correo_electronico">
        <label>Email 
            <span class="badge badge-secondary" onclick="insertarArroba();"> +@</span>
            <span class="badge badge-secondary" onclick="insertarCorreoPredeterminado();"> +set</span>
        </label>
        <input type="email" name="sales_email" class="form-control ">
    </div>
    <div class="col-md-2 mt-1">
        <label>Cod. Cliente/Fijo </label>
        <input type="text" name="codigo_fijo" class="form-control ">
    </div>
</div>

<div id="tab_basicservice" class="disabled" style="display:none;">
    <div class="dropdown-divider"></div>
    @include('sale.partials-sale-modal-serviciobasico')
</div>


<script>

    var temp_customer;
    // Caso 0, cliente varios, se llena los datos.
    
    $(document).ready(function() {
        var current_customer_id = $('#customer_id').val();
        if (current_customer_id) {
            consultarCliente();
        } else if ($('input[name="customer_id_hidden"]').val()) {
            setClientePredeterminado();
        }
        console.log("Ready!");
    });
    // Caso 1, el cliente seleccionado no tiene todos los datos, 
    // se setea los datos predeterminado para facturar 
    function setClientePredeterminado() {
        var id_cliente_predeterminado = $("input[name='customer_id_hidden']").val();
        $('.invalid-feedback').hide();
        if (!id_cliente_predeterminado) return;
        $.get('{{ url("sales/getcliente") }}/' + id_cliente_predeterminado, function(data) {
            $("input[name='sales_razon_social']").val(data.name);
            $("input[name='sales_email']").val(data.email);
            $('select[name=sales_tipo_documento]').val(data.tipo_documento);
            $('input[name=sales_valor_documento]').val(data.valor_documento);
            $('input[name=sales_complemento_documento]').val(data.complemento_documento);
            if(data.codigofijo != null){
                $("input[name='codigo_fijo']").val(data.codigofijo);
            }else{
                $("input[name='codigo_fijo']").val(data.id);
            }
            var status = data.tipo_documento;
            if (status) {
                $('#sales_verificarNIT').hide();
                $('#sales_valor_documento').show(300);
                $('#sales_complemento').show(300);
                sales_NIT(status);
                salesCarnetIdentidad(status);
            }
        });
        setValoresTipoDocumentoCasoEspecial()
    }

    function resetBillingFormFields() {
        // Consultar cliente actual o predeterminado
        var current_customer_id = $('#customer_id').val();
        if (current_customer_id) {
            if (typeof consultarCliente === 'function') {
                consultarCliente();
            } else if (typeof consultarClientePOS === 'function') {
                consultarClientePOS();
            }
        } else if ($('input[name="customer_id_hidden"]').val()) {
            setClientePredeterminado();
        }
    }



    $('#lims_customerSearch').on('change', consultarCliente);
    // permite rellenar los datos del cliente en el tabs Facturar
    function consultarCliente() {
        console.log("Cargando Cliente...");
        $('#sales_caso_especial_id').val("1");
        $('#sales_tipo_documento_id').prop("disabled", false);
        $('input[name=sales_valor_documento]').prop("readonly", false);
        $('input[name=sales_razon_social]').prop("readonly", false);
        $('#sales_correo_electronico').show();
        $('#botonSetClientePredeterminado').show();
        $('.invalid-feedback').hide();
        $('.valid-feedback').hide();
        var cliente_id = $('#customer_id').val();
        if (!cliente_id) return;
        $.get('{{ url("sales/getcliente") }}/' + cliente_id, function(data) {
            $("#lims_customerSearch").val(data.name);
            $("input[name='sales_razon_social']").val(data.name);
            $("input[name='sales_email']").val(data.email);
            $('select[name=sales_tipo_documento]').val(data.tipo_documento);
            $('input[name=sales_valor_documento]').val(data.valor_documento);
            $('input[name=sales_complemento_documento]').val(data.complemento_documento);
            if(data.codigofijo != null){
                $("input[name='codigo_fijo']").val(data.codigofijo);
            }else{
                $("input[name='codigo_fijo']").val(data.id);
            }
            var status = data.tipo_documento;
            bandera_tasadignidad = data.is_tasadignidad;
            bandera_ley1886 = data.is_ley1886;
            porcentaje_ley1886 = data.porcentaje_ley1886;
            porcentaje_tasadignidad = data.porcentaje_tasadignidad;
            temp_customer = data;
            if (status) {
                $('#sales_verificarNIT').hide();
                $('#sales_valor_documento').show(300);
                $('#sales_complemento').show(300);
                sales_NIT(status);
                salesCarnetIdentidad(status);
            }
        });
        setValoresTipoDocumentoCasoEspecial()
        console.log("tasaDignidad:" + bandera_tasadignidad + " - " + porcentaje_tasadignidad + "%");
        console.log("ley1886:" + bandera_ley1886 + " - " + porcentaje_ley1886 + "%");
    }

    $('#glosa_tipo_factura').hide();
    $('#tab_basicservice').hide();
    $('#sales_valor_documento').hide();
    $('#sales_complemento').hide();
    $('#sales_tipo_documento_id').on('change', mostrarSalesInput);

    function mostrarSalesInput() {
        // $("input[name='sales_valor_documento']").prop('required',true);
        $('#sales_complemento').hide();
        $('#sales_valor_documento').show(300);
        $('#sales_verificarNIT').hide();
        $('.invalid-feedback').hide();
        $('.valid-feedback').hide();
        var status = $(this).val();
        sales_NIT(status);
        salesCarnetIdentidad(status);
        setValoresTipoDocumentoCasoEspecial();
    }
    //mostrar complemento solo cuando es Carnet de Identidad
    function salesCarnetIdentidad(status) {
        if (status == 1) {
            $('#sales_complemento').show(300);
        }
    }
    //mostrar botón para sales_verificarNIT y oculta el complemento
    function sales_NIT(status) {
        if (status == 5) {
            $('#sales_complemento').hide();
            $('#sales_verificarNIT').show(300);
        }
        $('.selectpicker').selectpicker('refresh');
    }

    function sales_consultarNIT() {
        var url = '{{ route('customer.verificar_nit', ':id') }}';
        var nit = $("input[name='sales_valor_documento']").val();
        url = url.replace(':id', nit);
        $('#sales_verificarNIT').show(300);

        $.ajax({
            url: url,
            type: "GET",
            async: false,
            success: function(data) {
                console.log("operación sales_consultarNIT, respuesta => " + data['codigo'] + ' - ' + data[
                    'descripcion']);

                if (data['codigo'] == 994) {
                    // El NIT es INEXISTENTE
                    $("input[name='bandera_codigo_excepcion_hidden']").val(1);
                    $('.invalid-feedback').text('Respuesta Siat: ' + data['descripcion']);
                    $('.invalid-feedback').show();
                    $('.valid-feedback').hide();
                } else {
                    // El NIT es válido o inactivo
                    $("input[name='bandera_codigo_excepcion_hidden']").val(0);
                    $('.invalid-feedback').hide();
                    $('.valid-feedback').text('Respuesta Siat: ' + data['descripcion']);
                    $('.valid-feedback').show();
                }
            },
        });
        $('.selectpicker').selectpicker('refresh');
    }

    function insertarArroba() {
        var texto = $("input[name='sales_email']").val();
        texto = texto + '@';
        $("input[name='sales_email']").val(texto);
    }

    function insertarCorreoPredeterminado() {
        var id = $('select[name=biller_id]').val();
        var url = '{{ route('get_correo_biller', ':id') }}';
        url = url.replace(':id', id);
        var texto = "";
        $.ajax({
            url: url,
            type: "GET",
            async: false,
            success: function(data) {
                console.log('El correo del biller es => ' + data);
                texto = data;
            }
        });

        $("input[name='sales_email']").val(texto);
    }

    ///////////////////////////////////////////////////////////////////
    // casos especiales 
    $('#sales_caso_especial_id').on('change', mostrarCasoEspecial);

    function mostrarCasoEspecial() {
        tipo_caso_especial = $(this).val();

        if (tipo_caso_especial == "1") {
            // ninguna
            $('#sales_tipo_documento_id').prop("disabled", false);
            $('input[name=sales_valor_documento]').prop("readonly", false);
            $('input[name=sales_razon_social]').prop("readonly", false);

            $('#sales_correo_electronico').show();
            $('#botonSetClientePredeterminado').show();
            $('#sales_verificarNIT').hide();
            consultarCliente();
            $('.selectpicker').selectpicker('refresh');
        }
        if (tipo_caso_especial == "2") {
            // extrajero no inscrito
            $('#sales_tipo_documento_id').prop("disabled", true);
            $('select[name=sales_tipo_documento]').val(5);
            $('input[name=sales_valor_documento]').prop("readonly", true);
            $('input[name=sales_valor_documento]').val("99001");

            $('input[name=sales_razon_social]').prop("readonly", false);
            $("input[name='sales_razon_social']").val("");

            $('#sales_correo_electronico').show();
            $("input[name='sales_email']").val("");

            $('input[name=sales_complemento_documento]').val("");
            $('#sales_complemento').hide();
            $('#sales_verificarNIT').hide();
            $('.invalid-feedback').hide();
            $('.valid-feedback').hide();

            $('#botonSetClientePredeterminado').hide();
            $('.selectpicker').selectpicker('refresh');
        }
        if (tipo_caso_especial == "3") {
            // control tributario
            $('#sales_tipo_documento_id').prop("disabled", true);
            $('select[name=sales_tipo_documento]').val(5);
            $('input[name=sales_valor_documento]').prop("readonly", true);
            $('input[name=sales_valor_documento]').val("99002");

            $('input[name=sales_razon_social]').prop("readonly", true);
            $("input[name='sales_razon_social']").val("CONTROL TRIBUTARIO");

            //$('#sales_correo_electronico').hide();
            $('#sales_correo_electronico').show();
            $("input[name='sales_email']").val("");
            $('input[name=sales_email]').prop("required", false);

            $('input[name=sales_complemento_documento]').val("");
            $('#sales_complemento').hide();
            $('#sales_verificarNIT').hide();
            $('.invalid-feedback').hide();
            $('.valid-feedback').hide();

            $('#botonSetClientePredeterminado').hide();
            $('.selectpicker').selectpicker('refresh');
        }
        if (tipo_caso_especial == "4") {
            // ventas menores
            $('#sales_tipo_documento_id').prop("disabled", true);
            $('select[name=sales_tipo_documento]').val(5);
            $('input[name=sales_valor_documento]').prop("readonly", true);
            $('input[name=sales_valor_documento]').val("99003");

            $('input[name=sales_razon_social]').prop("readonly", true);
            $("input[name='sales_razon_social']").val("VENTAS MENORES DEL DIA");

            $('#sales_correo_electronico').show();
            $("input[name='sales_email']").val("");

            $('input[name=sales_complemento_documento]').val("");
            $('#sales_complemento').hide();
            $('#sales_verificarNIT').hide();
            $('.invalid-feedback').hide();
            $('.valid-feedback').hide();

            $('#botonSetClientePredeterminado').hide();
            $('.selectpicker').selectpicker('refresh');
        }
        setValoresTipoDocumentoCasoEspecial()
    }
    ///////////////////////////////////////////////////////////////////


    // Caso Facturar, mostrar si desea facturar o no.
    // ademas de colocar como requeridos los campos en caso de facturar. 
    $('#toggle-event').change(function() {
        var isChecked = $(this).prop('checked');
        console.log('Toggle Facturar: ' + isChecked);
        if (isChecked) {
            $('#fact_manual').hide();
            $("input[name='bandera_factura_hidden']").val(1);
            $("input[name='bandera_codigo_excepcion_hidden']").val(0);
            $('input[name=sales_razon_social]').prop("required", true);
            $('input[name=sales_valor_documento]').prop("required", true);
            $('input[name=sales_email]').prop("required", true);
            $('#nav_tab_billing_li').show();
            $('#segundoTabContinue').show();
            if ($('#primerTab').hasClass('active') || $('#myTab .nav-link.active').attr('href') === '#primerTab') {
                $('#submit-btn').html('<i class="fa fa-arrow-right"></i> Siguiente');
            }
        } else {
            $('#fact_manual').show();
            $("input[name='bandera_factura_hidden']").val("0");
            $('input[name=sales_razon_social]').prop("required", false);
            $('input[name=sales_valor_documento]').prop("required", false);
            $('input[name=sales_email]').prop("required", false);
            $('#nav_tab_billing_li').hide();
            $('#segundoTabContinue').hide();
            if ($('#segundoTab').hasClass('active') || $('#myTab .nav-link.active').attr('href') === '#segundoTab') {
                $('#myTab a[href="#primerTab"]').tab('show');
            }
            $('#submit-btn').html('<i class="fa fa-check"></i> Confirmar Venta');
        }
    });

    // Nota: el cambio de modo Online/Offline es manejado por toggleModoPOS() en pos.blade.php

    // Inicializar Facturar en POS
    $("input[name='bandera_factura_hidden']").val(1);
    $("input[name='bandera_codigo_excepcion_hidden']").val(0);
    $("input[name='invoice_no']").val(0);

    // funciona para guardar los valores input hidden de TipoDocumento y CasoEspecial
    // razon a que los select no envia los datos por usar Disabled
    function setValoresTipoDocumentoCasoEspecial() {
        $("input[name='sales_caso_especial_hidden']").val($('select[name=sales_caso_especial]').val())
        $("input[name='sales_tipo_documento_hidden']").val($('select[name=sales_tipo_documento]').val())
    }


    // Función para buscar el nit-valor documento 
    function consultar_ValorDocumento(isLiveTyping) {
        if (typeof isLiveTyping === 'undefined') {
            isLiveTyping = false;
        }

        var query = $("input[name='sales_valor_documento']").val().trim();
        if (query === '') {
            $('.valid-feedback').hide();
            $('.invalid-feedback').hide();
            return;
        }

        $.ajax({
            url: "{{ route('searchNit') }}",
            method: 'GET',
            data: {
                query: query
            },
            dataType: 'json',
            success: function(data) {
                $('.valid-feedback').hide();
                $('.invalid-feedback').hide();

                // Caso no existe coincidencia
                if (!data.table_data || data.total_data == 0) {
                    if (isLiveTyping) {
                        $('.invalid-feedback').text('Documento nuevo / no registrado').show();
                    } else {
                        $('.invalid-feedback').text('No hay coincidencias para este documento').show();
                    }
                    if ($('select[name=sales_tipo_documento]').val() == 1) {
                        $('#sales_complemento').show();
                    }
                    return;
                }

                // Caso coincidencia exacta encontrada
                if (data.total_data == 1 || data.is_exact) {
                    var item = data.table_data[0];
                    $('.valid-feedback').text('Datos recuperados de la última factura').show();
                    if (item.razon_social) {
                        var razonRecuperada = String(item.razon_social).trim();
                        $("input[name='sales_razon_social']").val(razonRecuperada);
                    }
                    if (item.email) {
                        $("input[name='sales_email']").val(String(item.email).trim());
                    }
                    if (item.tipo_documento) {
                        $('select[name=sales_tipo_documento]').val(item.tipo_documento);
                    }
                    if (item.complemento_documento) {
                        $('input[name=sales_complemento_documento]').val(String(item.complemento_documento).trim());
                    }

                    if (item.tipo_documento == 1) {
                        $('#sales_complemento').show();
                    } else {
                        $('#sales_complemento').hide();
                        $('input[name=sales_complemento_documento]').val("");
                    }

                    if ($('select[name=sales_tipo_documento]').val() == 5) {
                        sales_consultarNIT();
                    }
                }
                // Caso múltiples coincidencias parciales
                else if (data.total_data > 1) {
                    if (isLiveTyping) {
                        // Mientras escribe no abrimos modal invasivo, solo informamos
                        $('.valid-feedback').text('Existen ' + data.total_data + ' coincidencias. Presione Enter para ver lista.').show();
                    } else {
                        // Enter o clic en buscar: abrir modal para seleccionar
                        $('.invalid-feedback').text('Existen ' + data.total_data + ' coincidencias').show();
                        $("#tabla_busqueda_modal").find("tr:gt(0)").remove();

                        $.each(data.table_data, function(key, value) {
                            var escRazon = String(value['razon_social'] || '').replace(/"/g, '&quot;');
                            var escEmail = String(value['email'] || '').replace(/"/g, '&quot;');
                            var escDoc = String(value['valor_documento'] || '').replace(/"/g, '&quot;');
                            var escComp = String(value['complemento_documento'] || '').replace(/"/g, '&quot;');
                            var escTipo = value['tipo_documento'] || 1;

                            var btnHtmlValorDocumento = '<td>' +
                                '<button class="item-selected-btn btn btn-link" type="button" ' +
                                'data-id="' + key + '" ' +
                                'data-razon_social="' + escRazon + '" ' +
                                'data-email="' + escEmail + '" ' +
                                'data-tipo_documento="' + escTipo + '" ' +
                                'data-valor_documento="' + escDoc + '" ' +
                                'data-complemento_documento="' + escComp + '" ' +
                                'data-toggle="modal" data-target="#busquedaModal">' +
                                '<i class="dripicons-document-edit"></i> ' +
                                escDoc +
                                '</button>' +
                                '</td>';

                            var text_complemento_documento = value['complemento_documento'] ? value['complemento_documento'] : '';

                            var htmlTags = '<tr>' +
                                '<td>' + (value['razon_social'] || '') + '</td>' +
                                '<td>' + (value['email'] || '') + '</td>' +
                                btnHtmlValorDocumento +
                                '<td>' + text_complemento_documento + '</td>' +
                                '<td>' + escTipo + '</td>' +
                                '</tr>';

                            $('#tabla_busqueda_modal tbody').append(htmlTags);
                        });

                        $('#modalBusquedaNit').modal('show');
                    }
                }

                setValoresTipoDocumentoCasoEspecial();
                $('.selectpicker').selectpicker('refresh');
            }
        });
    }

    // Escucha en tiempo real al digitar el Valor Documento con debounce
    var nitTypingDebounceTimer = null;
    $(document).on('input', 'input[name="sales_valor_documento"]', function() {
        clearTimeout(nitTypingDebounceTimer);
        var q = $(this).val().trim();
        if (q.length >= 3) {
            nitTypingDebounceTimer = setTimeout(function() {
                consultar_ValorDocumento(true);
            }, 350);
        } else {
            $('.valid-feedback').hide();
            $('.invalid-feedback').hide();
        }
    });

    $(document).on('keypress', 'input[name="sales_valor_documento"]', function(event) {
        var keycode = (event.keyCode ? event.keyCode : event.which);
        if (keycode == 13) {
            event.preventDefault();
            event.stopPropagation();
            clearTimeout(nitTypingDebounceTimer);
            consultar_ValorDocumento(false);
        }
    });

    $(document).on('blur', 'input[name="sales_valor_documento"]', function() {
        clearTimeout(nitTypingDebounceTimer);
        var q = $(this).val().trim();
        if (q.length >= 3 && $("input[name='sales_razon_social']").val().trim() === '') {
            consultar_ValorDocumento(true);
        }
    });

    // Rellenar datos del valor_documento seleccionado, desde "tabla modal con lista de coincidencias"
    $(document).on("click", ".item-selected-btn", function() {
        var selRazon = $(this).attr('data-razon_social') || $(this).data('razon_social') || '';
        var selEmail = $(this).attr('data-email') || $(this).data('email') || '';
        var selTipo = $(this).attr('data-tipo_documento') || $(this).data('tipo_documento') || 1;
        var selDoc = $(this).attr('data-valor_documento') || $(this).data('valor_documento') || '';
        var selComp = $(this).attr('data-complemento_documento') || $(this).data('complemento_documento') || '';

        $("input[name='sales_razon_social']").val(String(selRazon).trim());
        $("input[name='sales_email']").val(String(selEmail).trim());
        $('select[name=sales_tipo_documento]').val(selTipo);
        $('input[name=sales_valor_documento]').val(String(selDoc).trim());
        $('input[name=sales_complemento_documento]').val(String(selComp).trim());

        if (selTipo == 1) {
            $('#sales_complemento').show();
        } else {
            $('#sales_complemento').hide();
            $('input[name=sales_complemento_documento]').val("");
        }
        if (selTipo == 5) {
            sales_consultarNIT();
        }

        $('#modalBusquedaNit').modal('hide');
        setValoresTipoDocumentoCasoEspecial();
        $('.selectpicker').selectpicker('refresh');
    });

    // Escenario para Factura Alquiler, la glosa debe ser requerido
    $('#tipo_factura_id').on('change', function() {
        var estadoTipoFactura = $('select[name=tipo_factura]').val();
        console.log('El código de documento sector es => ' + estadoTipoFactura);
        $("input[name='bandera_codigo_documento_sector_hidden']").val(estadoTipoFactura);

        if (estadoTipoFactura == 1) {
            console.log('Tipo de Venta Factura Compra-Venta');
            $('#glosa_tipo_factura').hide();
            $('input[name=glosa_periodo_facturado]').prop("required", false);
            $("input[name='glosa_periodo_facturado']").val("");
            $('input[name=domicilio_cliente]').prop("required", false);
            $('input[name=ciudad]').prop("required", false);
            $('#tab_basicservice').hide();
            $("#tab_basicservice").addClass("disabled");
        }
        if (estadoTipoFactura == 2) {
            console.log('Tipo de Venta Factura Alquiler');
            $('#glosa_tipo_factura').show();
            $('input[name=glosa_periodo_facturado]').prop("required", true);
            $('input[name=domicilio_cliente]').prop("required", false);
            $('input[name=ciudad]').prop("required", false);
            $('#tab_basicservice').hide();
            $("#tab_basicservice").addClass("disabled");
        }
        if (estadoTipoFactura == 13) {
            console.log('Tipo de Venta Factura Servicio Basico');
            $('#glosa_tipo_factura').hide();
            $('input[name=glosa_periodo_facturado]').prop("required", false);
            $("input[name='glosa_periodo_facturado']").val("");
            $('input[name=domicilio_cliente]').prop("required", true);
            $('input[name=ciudad]').prop("required", true);
            if (temp_customer != null) {
                $("input[name='domicilio_cliente']").val(temp_customer.address);
                $("input[name='ciudad']").val(temp_customer.city);
            }
            $('#tab_basicservice').show();
            $("#tab_basicservice").removeClass("disabled");
        }
    });
</script>
