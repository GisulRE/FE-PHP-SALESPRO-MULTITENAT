@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('message') }}</div>
    @endif

    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif
    <section class="forms">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <h4>{{ trans('file.POS Setting') }}</h4>
                        </div>
                        <div class="card-body">
                            <p class="italic">
                                <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                            </p>
                            {!! Form::open(['route' => 'setting.posStore', 'method' => 'post']) !!}
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Customer') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="customer_id_hidden"
                                                value="{{ $lims_pos_setting_data->customer_id }}">
                                        @endif
                                        <select required name="customer_id" id="customer_id"
                                            class="selectpicker form-control" data-live-search="true"
                                            data-live-search-style="begins" title="Select customer...">
                                            @foreach ($lims_customer_list as $customer)
                                                <option value="{{ $customer->id }}">
                                                    {{ $customer->name . ' (' . $customer->phone_number . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Biller') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="biller_id_hidden"
                                                value="{{ $lims_pos_setting_data->biller_id }}">
                                        @endif
                                        <select required name="biller_id" class="selectpicker form-control"
                                            data-live-search="true" data-live-search-style="begins"
                                            title="Select Biller...">
                                            @foreach ($lims_biller_list as $biller)
                                                <option value="{{ $biller->id }}">
                                                    {{ $biller->name . ' (' . $biller->company_name . ')' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tasa de Cambio</label>
                                        <input type="text" name="t_c" class="form-control"
                                            value="@if ($lims_pos_setting_data) {{ $lims_pos_setting_data->t_c }} @endif" />
                                    </div>
                                    <div class="form-group">
                                        <label>Impresora Pre-Orden *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="type_printorder_id_hidden"
                                                value="{{ $lims_pos_setting_data->print_order }}">
                                        @endif
                                        <select required name="type_printorder_id" class="selectpicker form-control"
                                            title="Seleccione Impresora...">
                                            <option value="0">Desactivado</option>
                                            @foreach ($lims_printer_list as $print)
                                                <option value="{{ $print->id }}">{{ $print->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="customer_sucursal"
                                                value="{{ $lims_pos_setting_data->customer_sucursal }}"
                                                @if ($lims_pos_setting_data->customer_sucursal == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Filtrar Clientes por Sucursal?</strong></label>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="user_category"
                                                value="{{ $lims_pos_setting_data->user_category }}"
                                                @if ($lims_pos_setting_data->user_category == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Filtrar Categorias por Usuario?</strong></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ trans('file.Default Warehouse') }} *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="warehouse_id_hidden"
                                                value="{{ $lims_pos_setting_data->warehouse_id }}">
                                        @endif
                                        <select required name="warehouse_id" class="selectpicker form-control"
                                            data-live-search="true" data-live-search-style="begins"
                                            title="Select warehouse...">
                                            @foreach ($lims_warehouse_list as $warehouse)
                                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Formato de Impresion *</label>
                                        @if ($lims_pos_setting_data)
                                            <input type="hidden" name="type_print_id_hidden"
                                                value="{{ $lims_pos_setting_data->type_print }}">
                                        @endif
                                        <select required name="type_print_id" class="form-control"
                                            title="Seleccione formato...">
                                            @foreach ($lims_formatprint_list as $formatprint)
                                                <option value="{{ $formatprint['id'] }}">{{ $formatprint['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ trans('file.Displayed Number of Product Row') }} *</label>
                                        <input type="number" name="product_number" class="form-control"
                                            value="{{ $lims_pos_setting_data->product_number }}" required />
                                    </div>
                                    <div class="form-group">
                                        <label>Cantidad Decimales en POS *</label>
                                        <input type="number" name="cant_decimal" class="form-control" min="0"
                                            max="5" value="{{ $lims_pos_setting_data->cant_decimal }}" required />
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data && $lims_pos_setting_data->keybord_active)
                                            <input class="mt-2" type="checkbox" name="keybord_active" value="1"
                                                checked>
                                        @else
                                            <input class="mt-2" type="checkbox" name="keybord_active" value="1">
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Touchscreen keybord') }}
                                                Venta</label>
                                        &nbsp;&nbsp;
                                        @if ($lims_pos_setting_data && $lims_pos_setting_data->keybord_presale)
                                            <input class="mt-2" type="checkbox" name="keybord_presale" value="1"
                                                checked>
                                        @else
                                            <input class="mt-2" type="checkbox" name="keybord_presale" value="1">
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Touchscreen keybord') }}
                                                Pre-Venta</label>
                                    </div>
                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="print"
                                                value="{{ $lims_pos_setting_data->print }}"
                                                @if ($lims_pos_setting_data->print == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Print Function') }}?</label>
                                        &nbsp;&nbsp;
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="print_presale"
                                                value="{{ $lims_pos_setting_data->print_presale }}"
                                                @if ($lims_pos_setting_data->print_presale == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>{{ trans('file.Print PreSale') }}?</label>
                                    </div>

                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="date_sell"
                                                value="{{ $lims_pos_setting_data->date_sell }}"
                                                @if ($lims_pos_setting_data->date_sell == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Mantener Fecha de Venta?</strong></label>
                                    </div>

                                    <div class="form-group">
                                        @if ($lims_pos_setting_data)
                                            <input class="mt-2" type="checkbox" name="require_transfer_authorization"
                                                value="{{ $lims_pos_setting_data->require_transfer_authorization }}"
                                                @if ($lims_pos_setting_data->require_transfer_authorization == 1) checked @endif>
                                        @endif
                                        <label class="mt-2"><strong>Requerir Autorización de
                                                transferencia?</strong></label>
                                    </div>
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Información SIAT.</small></p>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>Facturación SIAT</label>
                                    @if ($lims_pos_setting_data)
                                        <input type="hidden" name="facturacion_id_hidden"
                                            value="{{ $lims_pos_setting_data->facturacion_id }}">
                                    @endif
                                    <select required name="facturacion_id" class="selectpicker form-control"
                                        title="Seleccione facturación...">
                                        <option value="0">Facturar Opcional</option>
                                        <option value="1">Facturar Siempre</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tipo Emisión</label>
                                    @if ($lims_pos_setting_data)
                                        <input type="hidden" name="codigo_emision_hidden"
                                            value="{{ $lims_pos_setting_data->codigo_emision }}">
                                    @endif
                                    <select required name="codigo_emision" class="form-control"
                                        title="Seleccione emisión...">
                                        @foreach ($tipo_emision_list as $tipo_emision)
                                            <option value="{{ $tipo_emision['id'] }}">{{ $tipo_emision['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Tipo Moneda </label>
                                    <input type="hidden" name="tipo_moneda_id_hidden"
                                        value="{{ $lims_pos_setting_data->tipo_moneda_siat }}">
                                    <select name="tipo_moneda_siat" class="selectpicker form-control"
                                        title="Seleccione moneda...">
                                        @include('setting.partials-tipo_moneda')
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>NIT Emisor </label>
                                    <input type="text" class="form-control" name="nit_emisor"
                                        value="{{ $lims_pos_setting_data->nit_emisor }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Razón Social </label>
                                    <input type="text" class="form-control" name="razon_social_emisor"
                                        value="{{ $lims_pos_setting_data->razon_social_emisor }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Dirección </label>
                                    <textarea name="direccion_emisor" id="direccion_emisor" rows="2" class="form-control">{{ $lims_pos_setting_data->direccion_emisor }}</textarea>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="modal-tabla-cufd btn btn-warning " data-toggle="modal"
                                        data-target="#modal-tabla-cufd">
                                        <i class="dripicons-toggles"></i> Información de CUFD
                                    </button>
                                </div>

                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Credenciales SIAT.</small></p>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label>User </label>
                                    <input type="text" class="form-control" name="user_siat"
                                        value="{{ $lims_pos_setting_data->user_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Pass </label>
                                    <input type="text" class="form-control" name="pass_siat"
                                        value="{{ $lims_pos_setting_data->pass_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Token</label>
                                    <input type="text" class="form-control" name="url_siat"
                                        value="{{ $lims_pos_setting_data->url_siat }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Siat </label>
                                    <input type="text" class="form-control" name="url_operaciones"
                                        value="{{ $lims_pos_setting_data->url_operaciones }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Optimo </label>
                                    <input type="text" class="form-control" name="url_optimo"
                                        value="{{ $lims_pos_setting_data->url_optimo }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Cobranza </label>
                                    <input type="text" class="form-control" name="url_cobranza"
                                        value="{{ $lims_pos_setting_data->url_cobranza }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Url Whatsapp</label>
                                    <input type="text" class="form-control" name="url_whatsapp"
                                        value="@if ($lims_pos_setting_data) {{ $lims_pos_setting_data->url_whatsapp }} @endif">
                                </div>
                                <div class="form-group col-md-6">
                                    @if ($lims_pos_setting_data)
                                        <input class="mt-2" type="checkbox" name="cufd_centralizado"
                                            value="{{ $lims_pos_setting_data->cufd_centralizado }}"
                                            @if ($lims_pos_setting_data->cufd_centralizado == 1) checked @endif>
                                    @endif
                                    <label class="mt-2"><strong>CUFD Centralizado</strong></label>
                                </div>

                            </div>
                            <div class="row">
                                <div class="form-group col mt-3">
                                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                                </div>
                            </div>
                            <div class="dropdown-divider"></div>
                            <p class="italic"><small>Credenciales WhatsApp.</small></p>
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="modal-tabla-cufd btn btn-success " data-toggle="modal"
                                        data-target="#modal-whatsapp">
                                        <i class="dripicons-toggles"></i> Información de WhatsApp
                                    </button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal WhatsApp -->
    <div id="modal-whatsapp" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Información WhatsApp</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            Este proceso consiste en vincular el numero de WhatsApp al sistema.
                        </small>
                    </p>
                    <div class="row justify-content-center">
                        <div class="col">
                            <button type="button" class="iniciar-servicio-whatsapp btn btn-success">Iniciar
                                Servicio</button>
                        </div>
                        <div class="col">
                            <button type="button" class="obtener-qr btn btn-success">Obtener QR</button>
                        </div>
                    </div>

                    <div id="results" class="hidden mt-3" style="display: none;">
                        <p class="text-center">Escanee el codigo QR acontinuacion para habilitar el servicio de WhatsApp.
                        </p>
                        <p class="text-center">El codigo QR tiene limite de tiempo, con la opcion Obtener QR se puede
                            obtener el mas reciente.</p>
                        <p id="whatsappStatus" class="text-center small text-muted" style="margin-bottom: 6px;"></p>
                        <div class="text-center">
                            <img id="resultImage" src="" class="img-fluid" alt="QR code">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger"
                            data-dismiss="modal">{{ __('file.Close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal-tabla-cufd" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Información CUFD</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>
                            Este proceso consiste en renovar los CUFD de los Puntos de Venta.*
                        </small>
                    </p>
                    <div class="row justify-content-center">
                        {{-- <a href="{{route('')}}" type="button" class="btn btn-secondary">botón Renovar CUFD</a> --}}
                        <div class="col">
                            <button type="button" class="forzar-renovar-cufd btn btn-warning">Renovar CUFD
                                (Todos)</button>
                        </div>
                        <div class="col">
                            <button type="button" class="tarea-programada-cufd btn btn-warning">Activar Tarea
                                Programada</button>
                        </div>
                    </div>
                    {{-- Insertando datos en forma de tabla --}}
                    <div id="tabla_modal" class="table-responsive">
                        @include('layout.partials.spinner-ajax')
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nombre Punto Venta </th>
                                    <th>Sucursal</th>
                                    <th>Punto de Venta </th>
                                    <th>Estado Contingencia </th>
                                    <th>Fecha Vigencia CUFD</th>
                                    <th></th>
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

    <script type="text/javascript">
        $("ul#setting").siblings('a').attr('aria-expanded', 'true');
        $("ul#setting").addClass("show");
        $("ul#setting #pos-setting-menu").addClass("active");


        $('select[name="type_print_id"]').val($("input[name='type_print_id_hidden']").val());
        $('select[name="type_printorder_id"]').val($("input[name='type_printorder_id_hidden']").val());
        $('select[name="customer_id"]').val($("input[name='customer_id_hidden']").val());
        $('select[name="biller_id"]').val($("input[name='biller_id_hidden']").val());
        $('select[name="warehouse_id"]').val($("input[name='warehouse_id_hidden']").val());
        $('select[name="facturacion_id"]').val($("input[name='facturacion_id_hidden']").val());
        $('select[name="codigo_emision"]').val($("input[name='codigo_emision_hidden']").val());
        $('select[name="tipo_moneda_siat"]').val($("input[name='tipo_moneda_id_hidden']").val());
        $('.selectpicker').selectpicker('refresh');


        // Modal para mostrar el registro de los puntos de venta con CUIS
        $(document).on("click", ".modal-tabla-cufd", function(event) {
            // obtener y rellenar tabla en el modal de información
            var url = '{{ route('setting.lista_puntos_venta') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#tabla_modal").find("tr:gt(0)").remove();
                    $.each(data, function(key, value) {
                        console.log("la data es => " + data);
                        var estado = value['modo_contingencia'];
                        if (estado == true) {
                            textHtmlEstadoContingencia = '<td> Activado </td>';
                        } else {
                            textHtmlEstadoContingencia = '<td> Desactivado </td>';
                        }
                        var codigo = value['id'];
                        var htmlTags = '<tr>' +
                            '<td>' + value['nombre_punto_venta'] + '</td>' +
                            '<td>' + value['sucursal'] + '</td>' +
                            '<td>' + value['codigo_punto_venta'] + '</td>' +
                            textHtmlEstadoContingencia +
                            '<td>' + value['fecha_vencimiento'] + '</td>';
                        if (value['is_siat'] == true && value['is_active'] == true)
                            htmlTags +=
                            '<td> <button type="button" class="btn btn-warning" onclick="renovarPuntoV(' +
                            codigo +
                            ')" title="Renovar CUFD"> <i class = "fa fa-refresh" > </i></button > </td>';

                        htmlTags += '</tr>';

                        $('#tabla_modal tbody').append(htmlTags);
                    });
                }
            });
            $('#enviar-evento-modal').modal('show');
        });

        function refrescarDatosTablaCUFD() {
            var url = '{{ route('setting.lista_puntos_venta') }}';
            $.ajax({
                url: url,
                type: "GET",
                success: function(data) {
                    $("#tabla_modal").find("tr:gt(0)").remove();
                    $.each(data, function(key, value) {
                        var estado = value['modo_contingencia'];
                        if (estado == true) {
                            textHtmlEstadoContingencia = '<td> Activado </td>';
                        } else {
                            textHtmlEstadoContingencia = '<td> Desactivado </td>';
                        }
                        var codigo = value['id'];
                        var htmlTags = '<tr>' +
                            '<td>' + value['nombre_punto_venta'] + '</td>' +
                            '<td>' + value['sucursal'] + '</td>' +
                            '<td>' + value['codigo_punto_venta'] + '</td>' +
                            textHtmlEstadoContingencia +
                            '<td>' + value['fecha_vencimiento'] + '</td>';
                        if (value['is_siat'] == true && value['is_active'] == true)
                            htmlTags +=
                            '<td> <button type="button" class="btn btn-warning" onclick="renovarPuntoV(' +
                            codigo +
                            ')" title="Renovar CUFD"> <i class = "fa fa-refresh" > </i></button > </td>';

                        htmlTags += '</tr>';
                        $('#tabla_modal tbody').append(htmlTags);
                    });
                }
            });
        }

        // Botón ajax para forzar la renovación de los cufd.
        $(document).on("click", ".forzar-renovar-cufd", function(event) {
            var url_data = "{{ route('forzar_renovar_cufd') }}";

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'Cufd renovados para todos los puntos de venta!');
                        refrescarDatosTablaCUFD();
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
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

        // Botón ajax para la iniciación del servicio de WhatsApp.
        $(document).on("click", ".iniciar-servicio-whatsapp", function(event) {
            // var url_data = "{{ env('API_BASE_URL') }}/start-client";
            var url_data = "{{ route('whatsapp.session.start', [], false) }}";

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            fetch(url_data, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    // 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                // body: JSON.stringify({
                //     image_url: imageUrl
                // })
            })
            .then(async (response) => {
                let data = null;
                try {
                    data = await response.json();
                } catch (e) {
                    // ignore json parse errors
                }

                if (!response.ok) {
                    const msg = (data && (data.message || data.error)) ? (data.message || data.error) : ("Error iniciando servicio (HTTP " + response.status + ")");
                    throw new Error(msg);
                }

                console.log(data);
                // swal('Servicio de WhatsApp', 'El servicio de WhatsApp esta iniciando, en un momento estara disponible con la opcion: Obtener QR.');
                startWhatsAppPolling();
                return downloadQR();
            })
            .catch(err => {
                document.getElementById('results').style.display = 'none';
                swal('Servicio de WhatsApp', err.message);
                })
                .finally(() => {
                    // loading.classList.add('hidden');
                    // loadBtn.disabled = false;
                    $("#spinner-div").hide();
                });
        });

        // Botón ajax para obtener qr img file.
        $(document).on("click", ".obtener-qr", function(event) {
            startWhatsAppPolling();
            downloadQR();
        });

        function downloadQR(silent) {
            // var url_data = "{{ env('API_BASE_URL') }}/qr";
            var url_data = "{{ route('whatsapp.session.qr', [], false) }}";

            if (!silent) $("#spinner-div").show();
            return fetch(url_data, {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => {
                    if (response.status == 404) throw new Error("WhatsApp esta generando el QR, en un momento estara diponible con la opcion: Obtener QR.");
                    else if (response.status == 400) throw new Error("Servicio WhatsApp requiere la opcion: Iniciar Servicio.");
                    else if (!response.ok) throw new Error("Error obteniendo QR (HTTP " + response.status + ")");
                    else if (response.status == 400) throw new Error(
                        "Servicio WhatsApp requiere la opcion: Iniciar Servicio.");
                    else return response.blob();
                })
                .then(imageBlob => {
                    const url = URL.createObjectURL(imageBlob);
                    if (imageBlob != null) this.showResults(url);
                    // const a = document.createElement('a');
                    // a.href = url;
                    // a.download = 'downloaded_image.jpg'; // Specify the desired filename
                    // document.body.appendChild(a); // Append to body to make it clickable
                    // a.click(); // Programmatically click the link
                    // document.body.removeChild(a); // Remove the link
                    // URL.revokeObjectURL(url); // Release the object URL
                })
                .catch(err => {
                    document.getElementById('results').style.display = 'none';
                    if (!silent) swal('Servicio de WhatsApp', err.message);
                })
                .finally(() => {
                    if (!silent) $("#spinner-div").hide();
                });
        }

        function downloadStatus() {
            var url_data = "{{ route('whatsapp.session.status', [], false) }}";
            return fetch(url_data, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(async (response) => {
                var data = null;
                try {
                    data = await response.json();
                } catch (e) {
                    data = null;
                }

                if (!response.ok) {
                    var msg = (data && (data.message || data.error)) ? (data.message || data.error) : ("Error consultando status (HTTP " + response.status + ")");
                    throw new Error(msg);
                }

                var status = data ? (data.status || (data.upstreamBody && data.upstreamBody.data && data.upstreamBody.data.status)) : null;
                if (status) {
                    if (isWhatsAppAuthenticated(status)) {
                        setWhatsAppStatusText('Estado: ' + status + ' (Sesión iniciada)', true);
                        stopWhatsAppPolling();
                    } else {
                        setWhatsAppStatusText('Estado: ' + status, null);
                    }
                } else {
                    setWhatsAppStatusText('Estado: (sin datos)', null);
                }

                return data;
            })
            .catch(err => {
                setWhatsAppStatusText(err.message, false);
            });
        }

        var whatsappTimers = {
            qr: null,
            status: null,
        };

        function stopWhatsAppPolling() {
            if (whatsappTimers.qr) {
                clearInterval(whatsappTimers.qr);
                whatsappTimers.qr = null;
            }
            if (whatsappTimers.status) {
                clearInterval(whatsappTimers.status);
                whatsappTimers.status = null;
            }
        }

        function setWhatsAppStatusText(text, isOk) {
            var el = document.getElementById('whatsappStatus');
            if (!el) return;

            el.textContent = text || '';
            el.classList.remove('text-muted', 'text-success', 'text-danger');
            if (isOk === true) el.classList.add('text-success');
            else if (isOk === false) el.classList.add('text-danger');
            else el.classList.add('text-muted');
        }

        function isWhatsAppAuthenticated(status) {
            if (!status) return false;
            var s = String(status).toLowerCase();
            return (s === 'authenticated' || s === 'connected' || s === 'open' || s === 'ready');
        }

        function startWhatsAppPolling() {
            if (whatsappTimers.qr || whatsappTimers.status) return;

            whatsappTimers.status = setInterval(function() {
                downloadStatus();
            }, 5000);

            whatsappTimers.qr = setInterval(function() {
                if (document.getElementById('results').style.display === 'block') {
                    downloadQR(true);
                }
            }, 60000);

            downloadStatus();
        }

        $('#modal-whatsapp').on('hidden.bs.modal', function() {
            stopWhatsAppPolling();
            setWhatsAppStatusText('', null);
        });

        function showResults(objectURL) {
            document.getElementById('resultImage').src = objectURL;
            document.getElementById('results').style.display = 'block';
        }

        function renovarPuntoV(codigo) {
            var url_data = "{{ route('setting.renovar_puntoventa', ':id') }}";
            url_data = url_data.replace(':id', codigo);

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'Cufd renovado para el punto de venta: ' + codigo);
                        refrescarDatosTablaCUFD();
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
                    }
                },
                complete: function() {
                    $("#spinner-div").hide(); //Ocultar icon spinner de cargando
                },
                error: function() {
                    swal('Error', 'error en el servicio');
                },
            });
        }

        // Botón ajax activar la tarea programada del kernel.
        $(document).on("click", ".tarea-programada-cufd", function(event) {
            var url_data = "{{ route('run_tarea_programada') }}";

            $("#spinner-div").show(); //Mostrar icon spinner de cargando
            $.ajax({
                url: url_data,
                type: "GET",
                success: function(data) {
                    if (data == true) {
                        swal('Renovación Exitosa', 'tarea programada realizada!');
                    } else {
                        swal('Error', 'no se logró renovar los cufd');
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
    </script>
@endsection
