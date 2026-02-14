@extends('layout.main')

@section('content')
    <style>
        .swal-modal {
            width: 850px !important;
        }
    </style>
    @include('layout.partials.session-flash')

    <section>
        <div class="container-fluid">

            <h4 class="fw-bold py-3 mb-4">
                <span class="text-muted fw-light">Facturas / Envío /</span> Masivo
            </h4>
            <div class="row">

                <div class="col-3">
                    <p>Está compuesta por una serie de Servicios Web habilitados para recibir facturas en forma masiva bajo
                        la modalidad Electrónica en Línea, los mismos se hallan publicados de forma diferenciada por tipo de
                        documentos sector. Dichos servicios reciben el paquete verificando que los parámetros enviados sean
                        válidos, verificando si el paquete recibido es correcto. </p>
                </div>

                <!-- FacturaMasiva -->
                <div class="col-6">
                    <div class="card mb-4">
                        <h5 class="card-header">Factura Masiva</h5>
                        <div class="card-body">
                            <p class="card-title">Se debe considerar lo siguiente:</p>
                            <p class="card-text">* Periodicidad con la que enviará: Diario, semanal o mensual.</p>
                            <p class="card-text">* Tamaño de los paquetes: máximo 1000.</p>

                            <button type="button" class="btn btn-danger " data-toggle="modal"
                                data-target="#addFacturaMasiva">
                                <i class="dripicons-plus"></i>Añadir Factura Masiva
                            </button>
                            <button class="btn btn-outline-success" type="button" onclick="getEstadoSIN()"><i
                                    class="dripicons-search"></i>
                                Verificar estado del SIN
                            </button>
                            <p class="card-text"><small class="text-muted">El archivo CSV no tiene límite.</small></p>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <p>Las facturas del archivo se almacenan en paquetes que posteriormente serán enviados a la
                        administración Tributaria.</p>
                </div>
            </div>

            <hr>
            <div class="table-responsive">
                <table id="modo-table" class="table">
                    <thead>
                        <tr>
                            <th>Sucursal</th>
                            <th>Punto de Venta</th>
                            <th>Glosa</th>
                            <th>Fecha inicio</th>
                            <th>Fecha fin</th>
                            <th>Tipo Emisión </th>
                            <th>Estado </th>
                            <th class="not-exported">{{ trans('file.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list_f_masivas as $key => $item)
                            <tr data-id="{{ $item->id }}">
                                <td>{{ $item->getNombreSucursal() }}</td>
                                <td>{{ $item->getNombrePuntoVenta() }}</td>
                                <td>{{ $item->glosa }}</td>
                                <td>{{ $item->getFechaInicio() }}</td>
                                <td>{{ $item->getFechaFin() }}</td>
                                <td>{{ $item->tipo_factura }}</td>
                                <td>{{ $item->estado }}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true"
                                            aria-expanded="false">{{ trans('file.action') }}
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                            user="menu">
                                            @if ($item->created_by == Auth::user()->id || Auth::user()->role_id == 1)
                                                @if ($item->estado == 'EN_PROCESO')
                                                    <li>
                                                        <button type="button" class="btn btn-link"
                                                            data-id="{{ $item->id }}" data-toggle="modal"
                                                            onclick="cargarArchivo({{ $item->id }});"
                                                            data-target="#cargar-archivo-modal">
                                                            <i class="fa fa-plus-square-o"></i>
                                                            Cargar Datos
                                                        </button>
                                                    </li>
                                                @endif
                                                @if ($item->estado == 'EVENTO_REGISTRADO')
                                                    <li>
                                                        <button type="button" class="enviar-paquete-modal btn btn-link"
                                                            data-id="{{ $item->id }}" data-toggle="modal"
                                                            data-target="#enviar-paquete-modal"><i
                                                                class="fa fa-plus-square-o"></i> Enviar Paquetes
                                                        </button>
                                                    </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </section>

    <!-- add addFacturaMasiva modal -->
    <div id="addFacturaMasiva" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'factura-masiva.store', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Registrar Factura Masiva</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="row">
                        <div class="form-group col-6">
                            <label>Tipo Factura *</label>
                            <select name="tipo_factura" id="tipo_factura" class="form-control" title="Seleccionar...">
                                <option value="compra-venta">Compra-Venta</option>
                                <option value="alquiler">Alquiler</option>
                                <option value="servicio-basico">Servicio Básico</option>
                            </select>
                        </div>
                        <div class="form-group col-6">
                            <label>Fecha inicio *</strong> </label>
                            <input type="datetime-local" name="fecha_inicio" class="form-control"
                                value="{{ $fecha_actual }}" max="{{ $fecha_actual }}"
                                min="{{ $fecha_actual->subDays(3) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label>Glosa *</strong> </label>
                            <input type="text" class="form-control" name="glosa" placeholder="Ingrese glosa..."
                                required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group col">
                            <label>Sucursal *</label>
                            <select name="sucursal" id="sucursal" class="form-control" title="Seleccionar...">
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->sucursal }}">{{ $sucursal->sucursal }} |
                                        {{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col">
                            <label>Codigo Punto Venta *</label>
                            <select name="codigo_punto_venta" id="codigo_punto_venta" class="form-control selectpicker"
                                title="Seleccionar...">
                            </select>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <input id="btn_newfactMasiva" type="submit" value="{{ trans('file.submit') }}"
                            class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- modal cargarArchivoCSV modal -->
    <div id="cargar-archivo-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div class="modal-dialog ">
            @include('layout.partials.spinner-0-ajax')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Importar Datos</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <form id="formCargarArchivo" action="" method="POST" class="form-upload"
                    enctype="multipart/form-data">
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        <p>{{ trans('file.The correct column order is') }} (por definir)
                            {{ trans('file.and you must follow this') }}.</p>
                        <div class="row">
                            <input type="hidden" id="id_facturamasiva" name="idfacturamasiva" value="0" />
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ trans('file.Upload CSV File') }} *</label>
                                    {{ Form::file('file', ['id' => 'file_csv', 'class' => 'form-control', 'required']) }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label> {{ trans('file.Sample File') }}</label>
                                    <a href="public/sample_file/sample_modelo_formato_servicios.xlsx"
                                        class="btn btn-info btn-block btn-md"><i class="dripicons-download"></i>
                                        {{ trans('file.Download') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div>
                            <b>
                                ¿Está seguro?
                            </b>
                        </div>
                        <div class="">
                            @method('POST')
                            @csrf
                            <input id="btn_upload" type="button" value="Confirmar" class="btn btn-primary btn-upload">
                        </div>
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- modal enviarPaquetesFacturaMasiva modal -->
    <div id="enviar-paquete-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div class="modal-dialog " style="max-width: 910px">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> Enviar Paquetes Factura Masiva</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            Este proceso enviará los siguientes paquetes al SIN.*
                        </small>
                    </p>
                    {{-- Insertando datos en forma de tabla --}}
                    <div id="paquete_modal" class="table-responsive">
                        @include('layout.partials.spinner-ajax')
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Paquete/Facturas</th>
                                    <th>Cantidad </th>
                                    <th>Paso 0 </th>
                                    <th>Paso 1 </th>
                                    <th>Paso 2 </th>
                                    <th>Estado </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Llenado por ajax jquery --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <div class="">
                            <form id="formCerrarEvento" action="{{ route('factura-masiva.cerrar_evento', 0) }}"
                                method="POST" data-action="{{ route('factura-masiva.cerrar_evento', 0) }}">
                                @method('GET')
                                @csrf
                                <input hidden id="cerrarFacturaMasiva" type="submit" value="Confirmar cierre"
                                    class="btn btn-danger">
                            </form>
                        </div>
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal lista las ventas con NIT inexistentes en el paquete modal -->
    <div id="lista-nit-inexistente-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
        aria-hidden="true" class="modal fade text-left">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content" style=" !important; margin-left: 10px; margin-right: 10px;">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title"> Lista de Ventas Facturadas con NIT inexistentes </h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                        <span aria-hidden="true">
                            <i class="dripicons-cross"></i></span>
                    </button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            Este proceso permitirá confirmar el envío de NIT inexistentes al Servicio de Impuestos
                            Nacionales.*
                        </small>
                    </p>
                    <p id="opcionesMarcarCheck" class="italic">

                    </p>
                    {{-- Insertando datos en forma de tabla --}}
                    <div id="tabla_lista_nit_modal" class="table-responsive">
                        @include('layout.partials.spinner-1-ajax')
                        <table class="table table-sm table-striped ">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Nro. Venta</th>
                                    <th>Monto </th>
                                    <th>Tipo Documento</th>
                                    <th>Valor Documento</th>
                                    <th>Estado </th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Llenado por ajax jquery --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #factura-masiva-menu").addClass("active");

        var MAX_paquetes = 0;
        var CANT_paquetes_enviados = 0;

        var LIST_ventas_nit_excepcion = [];

        $('#modo-table').DataTable({
            "order": [],
            'language': {
                'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            'lengthMenu': [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "All"]
            ],
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function getEstadoSIN() {
            var url = '{{ route('estado_servicios_sin') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal("Servicios en línea!", "verdadero!", "success");
                    } else {
                        swal("Servicios caídos!", "falso!", "warning");
                    }
                }
            });
        }


        // modo FacturaMasiva tipo Servicios, registrar el archivo csv
        function cargarArchivo(id) {
            $('#id_facturamasiva').val(id);
            $('#cargar-archivo-modal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#cargar-archivo-modal').modal('show');
        };

        $('.btn-upload').on('click', function() {
            $("#spinner-div-0").show();
            var file_data = $('#file_csv').prop('files')[0];
            var form_data = new FormData();
            form_data.append('file', file_data);
            form_data.append('idfacturamasiva', $('#id_facturamasiva').val());
            console.log(form_data);
            $('#btn_upload').addClass('disabled noselect');
            $.ajax({
                url: '{{ route('factura-masiva.verificar_archivo') }}',
                dataType: 'text',
                cache: false,
                contentType: false,
                processData: false,
                data: form_data,
                type: 'post',
                timeout: 0,
                success: function(response) {
                    $("#spinner-div-0").hide();
                    console.log(response);
                    result = JSON.parse(response);
                    if (result.status) {
                        swal("Mensaje", "" + result.message, "success").then((value) => {
                            location.reload();
                        });
                    } else {
                        swal("Error Verificar archivo", "Lista de Errores: " + result.message, "error");
                        $("#btn_upload").removeClass("disabled noselect");
                        $('#file_csv').val(null);
                    }
                }
            });
        });

        // PASO - Modal para mostrar los paquetes que se enviaran
        $(document).on("click", ".enviar-paquete-modal", function(event) {
            var id = $(this).data('id').toString();
            // obtener y rellenar tabla en el modal de información
            var url = '{{ route('factura-masiva.obtener_paquetes', ':id') }}';
            url = url.replace(':id', id);
            console.log(url);

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    $("#paquete_modal").find("tr:gt(0)").remove();

                    $.each(data, function(key, value) {
                        var estadoPaquete = value['estado'];
                        var btnHtmlEstadoPaso0 = "";
                        var btnHtmlEstadoPaso1 = "";
                        var btnHtmlEstadoPaso2 = "";
                        var btnHtmlEstadoPaso3 = "";
                        if (estadoPaquete == "VALIDADA") {
                            btnHtmlEstadoPaso0 = '<td> <button id="paso_cero_' + value['id'] +
                                '" type="button" class="validar_datos_paquete btn btn-warning" data-id = "' +
                                value['id'] + '" disabled ><i class="fa fa-refresh"></i> ' +
                                "Validar Datos" + '</button> </td>';
                            btnHtmlEstadoPaso1 = '<td> <button id="paso_one_' + value['id'] +
                                '" type="button" class="enviar-paquete btn btn-danger" data-id = "' +
                                value['id'] +
                                '" disabled ><i class="fa fa-paper-plane-o"></i> ' +
                                "Enviar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso2 = '<td> <button id="paso_dos_' + value['id'] +
                                '" type="button" class="validar-paquete btn btn-info" data-id = "' +
                                value['id'] + '" disabled ><i class="fa fa-search"></i> ' +
                                "Validar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso3 = '<td> <button id="paso_tres_' + value['id'] +
                                '" type="button" class="actualizar-arreglo-venta btn btn-success" data-id = "' +
                                value['id'] + '" disabled ><i class="fa fa-repeat"></i> ' +
                                "Actualizar Ventas" + '</button> </td>';
                            CANT_paquetes_enviados++;
                        } else if (estadoPaquete == "OBSERVADA") {
                            btnHtmlEstadoPaso0 = '<td> <button id="paso_cero_' + value['id'] +
                                '" type="button" class="validar_datos_paquete btn btn-warning" data-id = "' +
                                value['id'] + '" disabled><i class="fa fa-refresh"></i> ' +
                                "Validar Datos" + '</button> </td>';
                            btnHtmlEstadoPaso1 = '<td> <button id="paso_one_' + value['id'] +
                                '" type="button" class="enviar-paquete btn btn-danger" data-id = "' +
                                value['id'] + '" ><i class="fa fa-paper-plane-o"></i> ' +
                                "Enviar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso2 = '<td> <button id="paso_dos_' + value['id'] +
                                '" type="button" class="validar-paquete btn btn-info" data-id = "' +
                                value['id'] + '"disabled><i class="fa fa-search"></i> ' +
                                "Validar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso3 = '<td> <button id="paso_tres_' + value['id'] +
                                '" type="button" class="actualizar-arreglo-venta btn btn-success" data-id = "' +
                                value['id'] + '" disabled ><i class="fa fa-repeat"></i> ' +
                                "Actualizar Ventas" + '</button> </td>';
                        } else {
                            btnHtmlEstadoPaso0 = '<td> <button id="paso_cero_' + value['id'] +
                                '" type="button" class="validar_datos_paquete btn btn-warning" data-id = "' +
                                value['id'] + '" ><i class="fa fa-refresh"></i> ' +
                                "Validar Datos" + '</button> </td>';
                            btnHtmlEstadoPaso1 = '<td> <button id="paso_one_' + value['id'] +
                                '" type="button" class="enviar-paquete btn btn-danger" data-id = "' +
                                value['id'] +
                                '" disabled><i class="fa fa-paper-plane-o"></i> ' +
                                "Enviar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso2 = '<td> <button id="paso_dos_' + value['id'] +
                                '" type="button" class="validar-paquete btn btn-info" data-id = "' +
                                value['id'] + '"disabled><i class="fa fa-search"></i> ' +
                                "Validar Paquete" + '</button> </td>';
                            btnHtmlEstadoPaso3 = '<td> <button id="paso_tres_' + value['id'] +
                                '" type="button" class="actualizar-arreglo-venta btn btn-success" data-id = "' +
                                value['id'] + '" disabled ><i class="fa fa-repeat"></i> ' +
                                "Actualizar Ventas" + '</button> </td>';
                        }
                        var htmlTags = '<tr>' +
                            '<td>' + value['glosa_nro_factura_inicio_a_fin'] + '</td>' +
                            '<td>' + value['cantidad_ventas'] + '</td>' +
                            btnHtmlEstadoPaso0 +
                            btnHtmlEstadoPaso1 +
                            btnHtmlEstadoPaso2 +
                            btnHtmlEstadoPaso3 +
                            '<td id="estado_' + value['id'] + '">' + value['estado'] + '</td>' +
                            '</tr>';

                        $('#paquete_modal tbody').append(htmlTags);

                        // asignamos la cantidad de paquetes
                        MAX_paquetes = data.length;
                    });
                }
            });
            // Boton para cerrar el evento
            action = $('#formCerrarEvento').attr('data-action').slice(0, -1)
            $('#formCerrarEvento').attr('action', action + id)
            $('#enviar-paquete-modal').modal('show');
        });

        // PASO 0 - Recorrido de las ventas del paquete seleccionado, donde se verifica si NIT, y corregir el codigo excepcion
        $(document).on("click", ".validar_datos_paquete", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.validar_datos_paquete', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div").show();
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    LIST_ventas_nit_excepcion.splice(0, LIST_ventas_nit_excepcion.length);
                    $("#tabla_lista_nit_modal").find("tr:gt(0)").remove();
                    $("#opcionesMarcarCheck").empty();
                    console.log(data);
                    var count = data.length;
                    if (count > 0) {
                        var opcionesCheckMarcarTodosHmtl =
                            '<label">Seleccionar Todos </label> <input type="checkbox" name="marcarTodos" id="marcarTodos"><button data-id = "' +
                            id +
                            '" class="confirmarExcepcionTodos btn btn-success ml-1">Confirmar Excepción Todos</button>';
                        $('#opcionesMarcarCheck').append(opcionesCheckMarcarTodosHmtl);

                        $.each(data, function(key, value) {
                            if (value['estado'] == 1) {
                                spanHtmlEstado =
                                    '<td> <input type="checkbox" checked disabled class="confirmar-excepcion" data-id="' +
                                    value['sale_id'] + '"> </td>';

                            } else {
                                spanHtmlEstado =
                                    '<td> <input type="checkbox"  class="confirmar-excepcion" data-id="' +
                                    value['sale_id'] + '"> </td>';
                            }
                            LIST_ventas_nit_excepcion[key] = value['sale_id'];
                            var htmlTags = '<tr>' +
                                '<td>' + value['reference_no'] + '</td>' +
                                '<td>' + value['grand_total'] + '</td>' +
                                '<td>' + 'NIT' + '</td>' +
                                '<td>' + value['valor_documento'] + '</td>' +
                                spanHtmlEstado +
                                '</tr>';

                            $('#tabla_lista_nit_modal tbody').append(htmlTags);

                            $('#lista-nit-inexistente-modal').modal('show');
                        });
                    } else {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', false);
                        swal('Información: ', 'No existen registros que requieran atención.');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide();
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        // Paso 0.1 función ajax para cambiar o actualizar código excepción de determinada tupla
        $(document).on("click", ".confirmar-excepcion", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.confirmar_excepcion_nit', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div-1").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    swal('Información: ', data);
                    $("#paso_cero_" + id).prop('disabled', true);
                    $("#paso_one_" + id).prop('disabled', false);
                },
                complete: function() {
                    $("#spinner-div-1").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });


        // Procedimiento para enviar paquete individual por ajax (segundo plano) mientras mostramos un loading, cuando termine el proceso
        // ocultar el spinner
        $(document).on("click", ".enviar-paquete", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.enviar_paquetes', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    $("#estado_" + id).text(data);
                    swal('Estado de envio: ', data);
                    if (data == "Error: Servicios caídos") {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', false);
                        $("#paso_dos_" + id).prop('disabled', true);
                    } else {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', true);
                        $("#paso_dos_" + id).prop('disabled', false);
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        // Botón para permitir validar/verificar si el paquete está validado | observado | rechazado 
        $(document).on("click", ".validar-paquete", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.verificar_paquete', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    $("#estado_" + id).text(data);
                    if (data === 'VALIDADA') {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', true);
                        $("#paso_dos_" + id).prop('disabled', true);
                        $("#paso_tres_" + id).prop('disabled', false);
                    }
                    if (data === 'OBSERVADA') {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', false);
                        $("#paso_dos_" + id).prop('disabled', true);
                        $("#paso_tres_" + id).prop('disabled', true);
                        mostrarLogErroresModal(id);
                    }
                    swal('Estado del paquete: ', data).then((value) => {
                        // mostrando solo mensaje
                    });
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        // Botón para actualizar las ventas cuf y xml de customer_sales del determinado paquete
        $(document).on("click", ".actualizar-arreglo-venta", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.obtener_arreglo_ventas_paquete', ':id') }}";
            url_data = url_data.replace(':id', id);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    var mensaje;
                    if (data == 1) {
                        mensaje = "ACTUALIZADO";
                    } else {
                        mensaje = "NO ACTUALIZADO";
                    }
                    $("#estado_" + id).text(mensaje);
                    if (data == 1) {
                        $("#paso_cero_" + id).prop('disabled', true);
                        $("#paso_one_" + id).prop('disabled', true);
                        $("#paso_dos_" + id).prop('disabled', true);
                        $("#paso_tres_" + id).prop('disabled', true);
                        CANT_paquetes_enviados++;
                    }
                    swal('Estado del paquete: ', mensaje).then((value) => {
                        console.log('se procede a cerrarElEncabezado');
                        triggerCerrarContingencia();
                    });
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });

        function triggerCerrarContingencia() {
            if (CANT_paquetes_enviados == MAX_paquetes) {
                $("#spinner-div").show();
                $("#cerrarFacturaMasiva").trigger("click");
            }
        }


        // Paso 0.3 Solo visual, checkbox MarcarTodos
        $(document).on("click", "#marcarTodos", function(event) {
            if ($(this).is(':checked')) {
                console.log("Document Checkbox Seleccionado");
                $('.confirmar-excepcion').prop('checked', true).change();
            } else {
                console.log("Document Checkbox Deseleccionado");
                $('.confirmar-excepcion').prop('checked', false).change();
            }
        });

        // Paso 0.1.1 botón confirmarExcepcionTodos
        $(document).on("click", ".confirmarExcepcionTodos", function(event) {
            var id = $(this).data('id').toString();
            var url_data = "{{ route('factura-masiva.confirmar_excepcion_todos_nit') }}";

            $("#spinner-div-1").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    arreglo_ventas: LIST_ventas_nit_excepcion,
                },
                success: function(data) {
                    $("#paso_cero_" + id).prop('disabled', true);
                    $("#paso_one_" + id).prop('disabled', false);
                    swal('Información: ', data).then((value) => {
                        $('#lista-nit-inexistente-modal').modal('hide');
                    });
                },
                complete: function() {
                    $("#spinner-div-1").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        });




        // Cuando se seleccione una sucursal, mostrar sus puntos de ventas respectivos. 
        $('#sucursal').on('change', function() {
            var id = $(this).val();
            var url = '{{ route('getPuntosVentas', ':id') }}';
            url = url.replace(':id', id);

            $("select[name='codigo_punto_venta']").empty();

            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    for (let i = 0; i < data.length; i++) {
                        $("select[name='codigo_punto_venta']").append('<option value="' + data[i]
                            .codigo_punto_venta + '">' + data[i].codigo_punto_venta + ' - ' + data[
                                i].nombre_punto_venta + '</option>');
                    };
                    $('.selectpicker').selectpicker('refresh');

                }
            });
        })

        $('#codigo_punto_venta').on('change', function() {
            var id = $(this).val();
            var sucursal = $("select[name='sucursal']").val();
            $.ajax({
                url: "factura-masiva/validar_registro/" + sucursal + "/" + id,
                type: "GET",
                success: function(data) {
                    console.log(data);
                    if (data.estado) {
                        swal('Advertencia',
                            'No permitido crear nueva Factura Masiva, Sucursal y Punto de Venta en uso termine el proceso anterior e intente nuevamente',
                            'warning');
                        $("#btn_newfactMasiva").prop('disabled', true);
                    } else {
                        $("#btn_newfactMasiva").prop('disabled', false);
                    }
                }
            });
        })

        function mostrarLogErroresModal(paquete_id) {
            var url_data = "{{ route('factura-masiva.obtener_logs_errores', ':id') }}";
            url_data = url_data.replace(':id', paquete_id);

            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    swal('Advertencia',
                        'Se han presentado errores en el envío, revise su carpeta de descarga para más información.',
                        'warning');

                    const a = document.createElement("a");
                    var archivoTxt = new Blob(["Logs de errores, " + data['glosa_nro_factura_inicio_a_fin'],
                        "\n", data['log_errores']
                    ], {
                        type: "text/plain;charset=utf-8"
                    });
                    const url = URL.createObjectURL(archivoTxt);
                    a.href = url;
                    a.download = 'logs_errores.txt';
                    a.click();
                    URL.revokeObjectURL(url);
                }
            });
        }
    </script>
@endsection
