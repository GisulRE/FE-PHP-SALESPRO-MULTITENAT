<div class="row">
    <div class="form-group col-md-6">
        <label>Tipo de Factura</label>
        <div class="input-group">
            <select name="tipo_factura" id="tipo_factura_id" class="selectpicker form-control" title="Seleccione...">
                <option value="1" selected>FACTURA COMPRA-VENTA</option>
                <option value="2" disabled>FACTURA ALQUILER</option>
                <option value="13" disabled>FACTURA SERVICIO-BASICO</option>
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
        <input type="text" name="sales_razon_social" class="form-control ">
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


<script>

    var temp_customer;
    // Caso 0, cliente varios, se llena los datos.
    
    $(document).ready(function() {
        consultarCliente();
        console.log("Ready!");
    });
    // Caso 1, el cliente seleccionado no tiene todos los datos, 
    // se setea los datos predeterminado para facturar 
    function setClientePredeterminado() {
        var id_cliente_predeterminado = $("input[name='customer_id_hidden']").val();
        $('.invalid-feedback').hide();
        $('.valid-feedback').hide();
        $.get('sales/getcliente/' + id_cliente_predeterminado, function(data) {
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
        $.get('sales/getcliente/' + cliente_id, function(data) {
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
        console.log('Toggle: ' + $(this).prop('checked'))
        if ($(this).prop('checked') == true) {
            $('#ventana_nav').show();
            $('#segundoTabContinue').show();
            $('#fact_manual').hide();
            $("input[name='bandera_factura_hidden']").val(1);
            $("input[name='bandera_codigo_excepcion_hidden']").val(0);
            $('input[name=sales_razon_social]').prop("required", true);
            $('input[name=sales_valor_documento]').prop("required", true);
            $('input[name=sales_email]').prop("required", true);
            $("#submit-btn").addClass("disabled noselect");

        } else {
            $('#ventana_nav').hide();
            $('#segundoTabContinue').hide();
            $('#myTab a[href="#primerTab"]').tab('show');
            $('#fact_manual').show();
            $("input[name='bandera_factura_hidden']").val("")
            $('input[name=sales_razon_social]').prop("required", false);
            $('input[name=sales_valor_documento]').prop("required", false);
            $('input[name=sales_email]').prop("required", false);
            $("#submit-btn").removeClass("disabled noselect");
        }
    })

    // Caso Entrar a modo online, mostrar si desea entrar a modo online y enviar contigencia o no.
    $('#toggle-event-mode').change(function() {
        console.log('Toggle Mode: ' + $(this).prop('checked'))
        if ($(this).prop('checked') == true) {
            $("#spinner-contigencia-div").show();
            $('#toggle-event-mode').prop('disabled', (i, v) => !v);
            $("#submit-btn").addClass("disabled noselect");
            registraEventoContingencia();
        } else {
            $("#spinner-contigencia-div").hide();
            $("#submit-btn").removeClass("disabled noselect");
        }
    })

    // Según el valor que tenga el sistema POS SETTING 
    // lo mostramos el toggle factura, Facturar Siempre, o Facturar Opcional
    if ($("input[name='facturacion_id_hidden']").val() == 1) {
        $('#toggle-event').prop('checked', true).change()
        $("input[name='bandera_factura_hidden']").val(1);
        $("input[name='bandera_codigo_excepcion_hidden']").val(0);
        $("input[name='invoice_no']").val(0);
    } else {
        $('#toggle-event').prop('checked', false).change()
        $("input[name='bandera_factura_hidden']").val("")
    }

    // funciona para guardar los valores input hidden de TipoDocumento y CasoEspecial
    // razon a que los select no envia los datos por usar Disabled
    function setValoresTipoDocumentoCasoEspecial() {
        $("input[name='sales_caso_especial_hidden']").val($('select[name=sales_caso_especial]').val())
        $("input[name='sales_tipo_documento_hidden']").val($('select[name=sales_tipo_documento]').val())
    }


    // Función para buscar el nit-valor documento 
    function consultar_ValorDocumento() {
        $('.valid-feedback').hide();
        $('.invalid-feedback').hide();
        $('#sales_complemento').hide();
        $('#sales_verificarNIT').hide(300);
        var query = $("input[name='sales_valor_documento']").val();

        $.ajax({
            url: "{{ route('searchNit') }}",
            method: 'GET',
            data: {
                query: query
            },
            dataType: 'json',
            async: false,
            success: function(data) {
                // Caso no existe coincidencia
                if (data.total_data == 0) {
                    $('.invalid-feedback').text('No hay coincidencias');
                    $('.invalid-feedback').show();
                    if ($('select[name=sales_tipo_documento]').val() == 1) {
                        ('input[name=sales_complemento_documento]').val("");
                        $('#sales_complemento').show();
                    } else {
                        ('input[name=sales_complemento_documento]').val("");
                    }
                    $("input[name='sales_razon_social']").val("");
                    $("input[name='sales_email']").val("");
                }
                // Caso existe una coincidencia
                if (data.total_data == 1) {
                    $('.valid-feedback').text('Existe una coincidencia exacta');
                    $('.valid-feedback').show();
                    $("input[name='sales_razon_social']").val(data.table_data[0]['razon_social']);
                    $("input[name='sales_email']").val(data.table_data[0]['email']);
                    $('select[name=sales_tipo_documento]').val(data.table_data[0]['tipo_documento']);
                    $('input[name=sales_valor_documento]').val(data.table_data[0]['valor_documento']);
                    $('input[name=sales_complemento_documento]').val(data.table_data[0][
                        'complemento_documento'
                    ]);
                    if (data.table_data[0]['tipo_documento'] == 1) {
                        $('#sales_complemento').show();
                    } else {
                        ('input[name=sales_complemento_documento]').val("");
                    }
                    if ($('select[name=sales_tipo_documento]').val() == 5) {
                        sales_consultarNIT();
                    }
                }
                // Caso más de una coincidencia
                if (data.total_data > 1) {
                    $('.invalid-feedback').text('Existen ' + data.total_data + ' coincidencias');
                    $('.invalid-feedback').show();
                    $("#tabla_busqueda_modal").find("tr:gt(0)").remove();

                    $.each(data.table_data, function(key, value) {
                        btnHtmlValorDocumento = '<td>' +
                            '<button class="item-selected-btn btn btn-link" type="button" ' +
                            'data-id = "' + key + '"' +
                            'data-razon_social = "' + value['razon_social'] + '"' +
                            'data-email = "' + value['email'] + '"' +
                            'data-tipo_documento = "' + value['tipo_documento'] + '"' +
                            'data-valor_documento = "' + value['valor_documento'] + '"' +
                            'data-complemento_documento = "' + value['complemento_documento'] +
                            '"' +
                            'data-toggle="modal" data-target="#busquedaModal"' +
                            '>' +
                            '<i class="dripicons-document-edit"></i> ' +
                            value['valor_documento'] +
                            '</button> ' +

                            '</td>';

                        text_complemento_documento = '';
                        if (value['complemento_documento'] != null) {
                            text_complemento_documento = value['complemento_documento'];
                        }

                        var htmlTags = '<tr>' +
                            '<td>' + value['razon_social'] + '</td>' +
                            '<td>' + value['email'] + '</td>' +
                            btnHtmlValorDocumento +
                            '<td>' + text_complemento_documento + '</td>' +
                            '<td>' + value['tipo_documento'] + '</td>' +
                            '</tr>';

                        $('#tabla_busqueda_modal tbody').append(htmlTags);
                    });



                    $('#modalBusquedaNit').modal('show');
                }

                console.log('La cantidad de coincidencia es => ' + data.total_data)
                setValoresTipoDocumentoCasoEspecial();
                $('.selectpicker').selectpicker('refresh');

            }
        });
    }

    // Rellenar datos del valor_documento seleccionado, desde "tabla modal con lista de coincidencias"
    $(document).on("click", ".item-selected-btn", function() {
        $("input[name='sales_razon_social']").val($(this).data('razon_social'));
        $("input[name='sales_email']").val($(this).data('email'));
        $('select[name=sales_tipo_documento]').val($(this).data('tipo_documento'));
        $('input[name=sales_valor_documento]').val($(this).data('valor_documento'));
        $('input[name=sales_complemento_documento]').val($(this).data('complemento_documento'));

        if ($(this).data('tipo_documento') == 1) {
            $('#sales_complemento').show();
        }
        if ($('select[name=sales_tipo_documento]').val() == 5) {
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
            $("#tab_basicservice").addClass("disabled");
        }
        if (estadoTipoFactura == 2) {
            console.log('Tipo de Venta Factura Alquiler');
            $('#glosa_tipo_factura').show();
            $('input[name=glosa_periodo_facturado]').prop("required", true);
            $('input[name=domicilio_cliente]').prop("required", false);
            $('input[name=ciudad]').prop("required", false);
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
            $("#tab_basicservice").removeClass("disabled");
        }
    });
</script>
