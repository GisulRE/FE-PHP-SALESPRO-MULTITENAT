@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid">
            @if (in_array('returns-add', $all_permission))
                <a href="{{ route('return-sale.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Return') }}</a>
            @endif
            @if (in_array('notadebcred_siat', $all_permission))
                <button type="button" class="btn btn-info " onclick="modalBuscar()">
                    <i class="dripicons-search"></i> {{ trans('file.Search Sale Siat') }}</a>
                </button>
            @endif
        </div>
        <div class="table-responsive">
            <table id="return-table" class="table return-list">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Date') }}</th>
                        <th>{{ trans('file.reference') }}</th>
                        <th>{{ trans('file.Biller') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Warehouse') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>{{ trans('file.status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_return_all as $key => $return)
                        <tr class="return-link"
                            data-return='["{{ date($general_setting->date_format, strtotime($return->created_at->toDateString())) }}", "{{ $return->reference_no }}", "{{ $return->warehouse->name }}", "{{ $return->biller->name }}", "{{ $return->biller->company_name }}","{{ $return->biller->email }}", "{{ $return->biller->phone_number }}", "{{ $return->biller->address }}", "{{ $return->biller->city }}", "{{ $return->customer->name }}", "{{ $return->customer->phone_number }}", "{{ $return->customer->address }}", "{{ $return->customer->city }}", "{{ $return->id }}", "{{ $return->total_tax }}", "{{ $return->total_discount }}", "{{ $return->total_price }}", "{{ $return->order_tax }}", "{{ $return->order_tax_rate }}", "{{ $return->grand_total }}", "{{ $return->return_note }}", "{{ $return->staff_note }}", "{{ $return->user->name }}", "{{ $return->user->email }}"]'>
                            <td>{{ $key }}</td>
                            <td>{{ date($general_setting->date_format, strtotime($return->created_at->toDateString())) . ' ' . $return->created_at->toTimeString() }}
                            </td>
                            <td>{{ $return->reference_no }}</td>
                            <td>{{ $return->biller->name }}</td>
                            <td>{{ $return->customer->name }}</td>
                            <td>{{ $return->warehouse->name }}</td>
                            <td class="grand-total">{{ $return->grand_total }}</td>
                            @if ($return->is_active)
                                <td>
                                    <div class="badge badge-success">Activo</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Anulado</div>
                                </td>
                            @endif
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
                                        <li>
                                            <button type="button" class="btn btn-link view"><i class="fa fa-eye"></i>
                                                {{ trans('file.View') }}</button>
                                        </li>
                                        @if (in_array('notadebcred_siat', $all_permission))
                                            @if ($return->customer_sale_id)
                                                <li>
                                                    <button type="button" class="imprimir-factura-modal btn btn-link"
                                                        data-id="{{ $return->customer_sale_id }}" data-toggle="modal"
                                                        data-target="#imprimir-factura-modal"><i class="fa fa-print"></i>
                                                        Imprimir Nota Crédito/Débito</button>
                                                </li>
                                                @if ($return->is_active)
                                                    <li>
                                                        <button type="button" class="anula-factura-modal btn btn-link"
                                                            data-id="{{ $return->id }}" data-toggle="modal"
                                                            data-target="#anula-factura-modal"><i
                                                                class="dripicons-trash"></i>
                                                            Anular Nota Fiscal</button>
                                                    </li>
                                                @endif
                                            @endif
                                        @endif
                                        @if (in_array('returns-edit', $all_permission) && $return->customer_sale_id == null && $return->is_active)
                                            <li>
                                                <a href="{{ route('return-sale.edit', $return->id) }}"
                                                    class="btn btn-link"><i class="dripicons-document-edit"></i>
                                                    {{ trans('file.edit') }}</a>
                                            </li>
                                        @endif
                                        <li class="divider"></li>
                                        @if (in_array('returns-delete', $all_permission) && $return->customer_sale_id == null && $return->is_active)
                                            {{ Form::open(['route' => ['return-sale.destroy', $return->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmDelete()"><i class="dripicons-trash"></i>
                                                    {{ trans('file.delete') }}</button>
                                            </li>
                                            {{ Form::close() }}
                                        @endif

                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="tfoot active">
                    <th></th>
                    <th>{{ trans('file.Total') }}</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
        <div id="return-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="container mt-3 pb-2 border-bottom">
                        <div class="row">
                            <div class="col-md-3">
                                <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                        class="dripicons-print"></i> {{ trans('file.Print') }}</button>
                                {{ Form::open(['route' => 'return-sale.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                                <input type="hidden" name="return_id">
                                <button class="btn btn-default btn-sm d-print-none"><i class="dripicons-mail"></i>
                                    {{ trans('file.Email') }}</button>
                                {{ Form::close() }}
                            </div>
                            <div class="col-md-6">
                                <h3 id="exampleModalLabel" class="modal-title text-center container-fluid">
                                    {{ $general_setting->site_title }}</h3>
                            </div>
                            <div class="col-md-3">
                                <button type="button" id="close-btn" data-dismiss="modal" aria-label="Close"
                                    class="close d-print-none"><span aria-hidden="true"><i
                                            class="dripicons-cross"></i></span></button>
                            </div>
                            <div class="col-md-12 text-center">
                                <i style="font-size: 15px;">{{ trans('file.Return Details') }}</i>
                            </div>
                        </div>
                    </div>
                    <div id="return-content" class="modal-body">
                    </div>
                    <br>
                    <table class="table table-bordered product-return-list">
                        <thead>
                            <th>#</th>
                            <th>{{ trans('file.product') }}</th>
                            <th>{{ trans('file.Qty') }}</th>
                            <th>{{ trans('file.Unit Price') }}</th>
                            <th>{{ trans('file.Tax') }}</th>
                            <th>{{ trans('file.Discount') }}</th>
                            <th>{{ trans('file.Subtotal') }}</th>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="return-footer" class="modal-body"></div>
                </div>
            </div>
        </div>

        <!--  Modal Buscar Facturas SIAT -->
        <div id="searchFacturas" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true"
            class="modal fade text-left">
            <div role="document" class="modal-dialog modal-lg">
                <div class="modal-content modal-lg">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Buscar Factura</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        <div class="row">
                            <div class="form-group col">
                                <label>Documento Sector</label>
                                <select name="documentoSector" id="documentoSector" class="form-control"
                                    title="Seleccionar...">
                                    <option value="1" selected>FACTURA COMPRA/VENTA</option>
                                    <option value="2">FACTURA ALQUILER</option>
                                    <option value="13">FACTURA SERVICIOS BASICOS</option>
                                    <option value="24">NOTA DE CREDITO/DEBITO</option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label>Fecha inicio</strong> </label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control"
                                    min="{{ date('Y-m-d', strtotime(' -30 day')) }}" value="{{ $fecha_actual }}">
                            </div>
                            <div class="form-group col">
                                <label>Fecha fin</strong> </label>
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control"
                                    min="{{ date('Y-m-d', strtotime(' -30 day')) }}"
                                    value="{{ date('Y-m-d', strtotime(' +1 day')) }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col">
                                <label>Buscar Por</label>
                                <select name="buscarFiltro" id="buscarFiltro" class="form-control"
                                    title="Seleccionar...">
                                    <option value="razonSocial" selected>Razón Social</option>
                                    <option value="numeroDocumento">Numero NIT/CI</option>
                                </select>
                            </div>
                            <div class="form-group col">
                                <label> &nbsp;</label>
                                <input id="textoBuscar" type="text" class="form-control" name="textoBuscar"
                                    placeholder="Ingrese Nit/CI o Razón Social...">
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
                                <select name="codigo_punto_venta" id="codigo_punto_venta"
                                    class="form-control selectpicker" title="Seleccionar...">
                                </select>
                            </div>
                        </div>

                        <div class="form-group mt-3">
                            <input type="submit" value="Buscar" class="btn btn-primary" onclick="filtrarFacturas()">
                        </div>
                        <div class="row">
                            <div class="form-group col">
                                <span>Lista de Facturas Filtradas</span>
                            </div>
                        </div>
                        <div class="table-responsive">
                            @include('layout.partials.spinner-ajax')
                            <table id="facturas_modal" class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Nro Factura</th>
                                        <th>Documento Sector </th>
                                        <th>Nro Documento/NIT</th>
                                        <th>Nombre/Razón Social</th>
                                        <th>Monto</th>
                                        <th>Fecha Emisión </th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  Modal Imprimir Nota Crédito/Débito SIAT -->
        <div id="imprimir-factura-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true" class="modal fade text-left">
            <div class="modal-dialog ">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title"> Imprimir Nota Crédito/Débito </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true">
                                <i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                        </p>
                        <object>
                            <embed id="pdfID" type="text/html" width="780" height="450" src="" />
                        </object>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary"
                                data-dismiss="modal">{{ __('file.Close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--  Modal Anular Nota Crédito/Débito SIAT -->
        <div id="anula-factura-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel"
            aria-hidden="true" class="modal fade bd-example-modal-sm">
            <div role="document" class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Anular Nota Crédito/Débito</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12 form-group">
                            <select id="motivo" class="form-control selectpicker" name="motivo" required
                                data-live-search="true" data-live-search-style="begins"
                                title="Seleccione Motivo de Anulacion...">
                                @foreach ($lims_motivos as $motivo)
                                    <option value="{{ $motivo->codigo_clasificador }}">{{ $motivo->descripcion }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <input type="hidden" name="return_anula_id" required>
                            <button id="btn_submit" class="btn btn-danger"><i class="dripicons-trash"></i> Anular
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script type="text/javascript">
        $("ul#return").siblings('a').attr('aria-expanded', 'true');
        $("ul#return").addClass("show");
        $("ul#return #sale-return-menu").addClass("active");

        var all_permission = <?php echo json_encode($all_permission); ?>;
        var return_id = [];
        $("select[name='sucursal']").val(0);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function loadPuntoVenta() {
            var id = $("select[name='sucursal']").val();
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
        }

        function confirmDelete() {
            if (confirm("¿Esta seguro de eliminar esto?")) {
                return true;
            }
            return false;
        }

        $("tr.return-link td:not(:first-child, :last-child)").on("click", function() {
            var returns = $(this).parent().data('return');
            returnDetails(returns);
        });

        $(".view").on("click", function() {
            var returns = $(this).parent().parent().parent().parent().parent().data('return');
            returnDetails(returns);
        });

        $("#print-btn").on("click", function() {
            var divToPrint = document.getElementById('return-details');
            var newWin = window.open('', 'Print-Window');
            newWin.document.open();
            newWin.document.write(
                '<link rel="stylesheet" href="/public/vendor/bootstrap/css/bootstrap.min.css" type="text/css"><style type="text/css">@media print {.modal-dialog { max-width: 1000px;} }</style><body onload="window.print()">' +
                divToPrint.innerHTML + '</body>');
            newWin.document.close();
            setTimeout(function() {
                newWin.close();
            }, 10);
        });

        $('#return-table').DataTable({
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
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 7]
                },
                {
                    'render': function(data, type, row, meta) {
                        if (type === 'display') {
                            data =
                                '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>';
                        }

                        return data;
                    },
                    'checkboxes': {
                        'selectRow': true,
                        'selectAllRender': '<div class="checkbox"><input type="checkbox" class="dt-checkboxes"><label></label></div>'
                    },
                    'targets': [0]
                }
            ],
            'select': {
                style: 'multi',
                selector: 'td:first-child'
            },
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    extend: 'pdf',
                    text: '{{ trans('file.PDF') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible'
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.print.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        return_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                var returns = $(this).closest('tr').data('return');
                                return_id[i - 1] = returns[13];
                            }
                        });
                        if (return_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'return-sale/deletebyselection',
                                data: {
                                    returnIdArray: return_id
                                },
                                success: function(data) {
                                    swal("Mensaje", data, "info");
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!return_id.length)
                            swal("Error", "Ninguna Fila seleccionado!", "error");
                    }
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });


        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        function returnDetails(returns) {
            $('input[name="return_id"]').val(returns[13]);
            var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + returns[0] +
                '<br><strong>{{ trans('file.reference') }}: </strong>' + returns[1] +
                '<br><strong>{{ trans('file.Warehouse') }}: </strong>' + returns[2] +
                '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' + returns[
                    3] + '<br>' + returns[4] + '<br>' + returns[5] + '<br>' + returns[6] + '<br>' + returns[7] + '<br>' +
                returns[8] +
                '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                returns[9] + '<br>' + returns[10] + '<br>' + returns[11] + '<br>' + returns[12] + '</div></div></div>';
            $.get('return-sale/product_return/' + returns[13], function(data) {
                $(".product-return-list tbody").remove();
                var name_code = data[0];
                var qty = data[1];
                var unit_code = data[2];
                var tax = data[3];
                var tax_rate = data[4];
                var discount = data[5];
                var subtotal = data[6];
                var newBody = $("<tbody>");
                $.each(name_code, function(index) {
                    var newRow = $("<tr>");
                    var cols = '';
                    cols += '<td><strong>' + (index + 1) + '</strong></td>';
                    cols += '<td>' + name_code[index] + '</td>';
                    cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                    cols += '<td>' + (subtotal[index] / qty[index]) + '</td>';
                    cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                    cols += '<td>' + discount[index] + '</td>';
                    cols += '<td>' + subtotal[index] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                });

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=4><strong>{{ trans('file.Total') }}:</strong></td>';
                cols += '<td>' + returns[14] + '</td>';
                cols += '<td>' + returns[15] + '</td>';
                cols += '<td>' + returns[16] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                cols += '<td>' + returns[17] + '(' + returns[18] + '%)' + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                var newRow = $("<tr>");
                cols = '';
                cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                cols += '<td>' + returns[19] + '</td>';
                newRow.append(cols);
                newBody.append(newRow);

                $("table.product-return-list").append(newBody);
            });
            var htmlfooter = '<p><strong>{{ trans('file.Return Note') }}:</strong> ' + returns[20] +
                '</p><p><strong>{{ trans('file.Staff Note') }}:</strong> ' + returns[21] +
                '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + returns[22] + '<br>' + returns[23];
            $('#return-content').html(htmltext);
            $('#return-footer').html(htmlfooter);
            $('#return-details').modal('show');
        }

        if (all_permission.indexOf("returns-delete") == -1)
            $('.buttons-delete').addClass('d-none');


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

        $('#facturas_modal').DataTable({
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
                [5, 10, 50, -1],
                [5, 10, 50, "All"]
            ]
        });


        function modalBuscar() {
            $('#searchFacturas').modal({
                backdrop: 'static',
                keyboard: false
            });
            loadPuntoVenta();
            $('#searchFacturas').modal('show');
        }



        function filtrarFacturas() {
            var start_date_get = $("#fecha_inicio").val();
            var end_date_get = $("#fecha_fin").val();
            var puntoVenta = $("select[name='codigo_punto_venta']").val();
            console.log(start_date_get + " | " + end_date_get + " | " + puntoVenta);
            if (puntoVenta == "") {
                swal("Mensaje", "Seleccione un Punto de Venta", "info");
                return;
            }
            $('#facturas_modal').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "return-sale/list_invoice",
                    dataType: "json",
                    data: {
                        fechaInc: start_date_get,
                        fechaFin: end_date_get,
                        documentoSector: $("select[name='documentoSector']").val(),
                        opcion: $("select[name='buscarFiltro']").val(),
                        sucursal: $("select[name='sucursal']").val(),
                        puntoVenta: $("select[name='codigo_punto_venta']").val(),
                        valor: $("#textoBuscar").val()
                    },
                    type: "post"
                },
                "createdRow": function(row, data, dataIndex) {
                    $(row).addClass('invoice-link');
                    //$(row).attr('data-presale', data['id']);
                },
                "columns": [{
                        "data": "numeroFactura"
                    },
                    {
                        "data": "documentoSector"
                    },
                    {
                        "data": "numeroDocumento"
                    },
                    {
                        "data": "nombreRazonSocial"
                    },
                    {
                        "data": "montoTotalMoneda"
                    },
                    {
                        "data": "fechaEmision"
                    },
                    {
                        "data": "options"
                    },
                ],
                'columnDefs': [{
                    "orderable": true,
                    'targets': [0, 1, 2, 4, 5]
                }],
                'language': {

                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                    "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                    "search": '{{ trans('file.Search') }}',
                    'paginate': {
                        'previous': '<i class="dripicons-chevron-left"></i>',
                        'next': '<i class="dripicons-chevron-right"></i>'
                    }
                },
                order: [
                    ['0', 'desc']
                ],
                'lengthMenu': [
                    [5, 10, 50, -1],
                    [5, 10, 50, "All"]
                ]
            });
        }


        $(document).on("click", "table.return-list tbody .imprimir-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            var url = '{{ route('return.obtener_bytes_factura', ':id') }}';
            url = url.replace(':id', id);
            $.ajax({
                url: url,
                type: "GET",
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    $('#pdfID').attr('src', 'data:application/pdf;base64,' + data['bytes']);
                    console.log('data:application/pdf;base64,' + data['bytes']);
                    $('#imprimir-factura-modal').modal('show');
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte",
                        "error");
                }
            });
        });

        $(document).on("click", "table.return-list tbody .anula-factura-modal", function(event) {
            var id = $(this).data('id').toString();
            $('input[name="return_anula_id"]').val(id);
            $('#anula-factura-modal').modal('show');
        });

        $(document).on("click", "#btn_submit", function(event) {
            var id = $('input[name="return_anula_id"]').val();
            var id_motivo = $('select[name="motivo"]').val();
            if (id_motivo == null || id_motivo == '') {
                swal("Error", "Seleccione motivo antes de confirmar!", "error");
                return;
            }
            $.ajax({
                url: "return-sale/anula_nota/" + id + "/" + id_motivo,
                type: "GET",
                dataType: "json",
                async: false,
                success: function(data) {
                    console.log(data);
                    if (data.status) {
                        swal("Mensaje", data.mensaje, "success");
                        location.reload();
                    } else
                        swal("Error", "Error: " + data.mensaje, "error");
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    swal("Error",
                        "Ocurrio un error del servidor, intente de nuevo ó contacte a soporte \n" +
                        "error: " + errorThrown,
                        "error");
                }
            });
        });
    </script>
@endsection('content')
