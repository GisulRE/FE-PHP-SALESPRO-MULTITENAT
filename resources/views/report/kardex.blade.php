@extends('layout.main') @section('content')
    @if (isset($message))
        <div class="alert {{ isset($message['success']) ? 'alert-success' : 'alert-danger' }} alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $message['success'] ?? $message['alert'] }}
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">KARDEX</h3>
                </div>
                {!! Form::open(['route' => 'kardex.search', 'method' => 'post', 'id' => 'search_form']) !!}
                <div class="px-5 mt-3 mb-3">
                    <div class="form-group row">
                        <div class="col-md-4">
                            <label>Desde</label>
                            <input name="start_date" id="start_date" class="form-control" format="dd/mm/yyyy"
                                placeholder="DD/MM/YYYY" type="date" value="{{ $start_date ?? '' }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>Hasta</label>
                            <input name="end_date" id="end_date" class="form-control" format="dd/mm/yyyy"
                                placeholder="DD/MM/YYYY" type="date" value="{{ $end_date ?? '' }}" required>
                        </div>
                        <div class="col-md-4">
                            <label>{{ trans('file.Warehouse') }}</label>
                            <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                data-live-search="true" data-live-search-style="begins" required>
                                <option value="">Seleccionar Almacen</option>
                                @foreach ($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label>{{ trans('file.Select Product') }}</label>
                            <div class="search-box input-group">
                                <button class="btn btn-secondary"><i class="fa fa-barcode"></i></button>
                                <input type="hidden" name="lims_productcode" id="lims_productcode"
                                    value="{{ $lims_productcode ?? '' }}" required />
                                <input type="text" name="product_code_name" id="lims_productcodeSearch"
                                    placeholder="Please type product code and select..." class="form-control"
                                    value="{{ $product_code_name ?? '' }}" required />
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end justify-content-center">

                            <button class="btn btn-primary fa fa-search" type="submit" style="margin-left: 5px">
                                {{ trans('file.Search') }}</button>
                        </div>
                        @if (isset($report_data_list) && isset($lims_productcode))
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn btn-success fa fa-check" type="button" style="margin-left: 5px"
                                    id="make_control">
                                    Control Unitario
                                </button>
                            </div>
                        @endif
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-secondary buttons-print fa fa-check" type="submit"
                                style="margin-left: 5px" id="make_warehouse_control">
                                Control Almacen
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="table-responsive">
                <table id="report-table" class="table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ trans('file.date') }}</th>
                            <th>Tipo</th>
                            <th>{{ trans('file.product') }}</th>
                            <th>{{ trans('file.Warehouse') }}</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Saldo</th>
                            <th>{{ trans('file.Cost') }}</th>
                            <th>{{ trans('file.Total Cost') }}</th>
                            <th> De / A </th>
                            <th class="not-exported">{{ trans('file.action') }}</th>

                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($prev_balance) && $prev_balance != null && $prev_balance->product != null)
                            <tr style="font-weight: 600;color: #01099c">
                                <td>1</td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($start_date)) }}</td>
                                <td>Saldo anterior</td>
                                <td>{{ $prev_balance->product }}</td>
                                <td>{{ $prev_balance->warehouse }}</td>
                                <td>0</td>
                                <td>0</td>
                                <td>{{ $prev_balance->saldo }}</td>
                                <td>{{ $prev_balance->cost }}</td>
                                <td>{{ $prev_balance->cost * $prev_balance->saldo }}</td>
                                <td></td>
                                <td></td>
                            </tr>
                        @endif

                        @if (isset($prev_balance) && count($report_data_list) > 0)
                            @foreach ($report_data_list as $key => $transaction)
                                <?php
                                $totalBalance = $calcTotalBalance($prev_balance, $transaction, $totalBalance, $key);
                                
                                $key = $key + 2;
                                $url = null;
                                if ($transaction->from_to):
                                    $from_to = $lims_warehouse_list->find($transaction->from_to)->name;
                                else:
                                    $from_to = '';
                                endif;
                                ?>
                                @if ($transaction->transaction_type == 'INIT')
                                    <tr style="font-weight: 600;color: #01099c">
                                    @else
                                    <tr>
                                @endif
                                <td>{{ $key }}</td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($transaction->date)) }}</td>
                                @if ($transaction->transaction_type == 'INIT')
                                    <td>Punto de Control</td>
                                @else
                                    <td>{{ $transaction->transaction_type }}</td>
                                @endif
                                <td>{{ $transaction->product }}</td>

                                <td>{{ $transaction->warehouse }}</td>
                                <td>{{ $transaction->entrada }}</td>
                                <td>{{ $transaction->salida }}</td>
                                <td>{{ $transaction->display_warehouse_qty_after ?? $transaction->warehouse_qty_after }}</td>
                                <td>{{ $transaction->cost }}</td>
                                <td>{{ $totalBalance }}</td>
                                <td style="font-size: 12px; width:120px">
                                    {{ $from_to }}
                                    @if (!empty($transaction->transfer_status_label))
                                        <br>
                                        <span class="badge {{ $transaction->transfer_status_class }}">{{ $transaction->transfer_status_label }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($transaction->transaction_id != 0 && $transaction->transaction_type != 'AJUSTE')
                                        <button data-id={{ $transaction->transaction_id }}
                                            data-type="{{ $transaction->transaction_type }}" type="button"
                                            class="btn btn-link view"><i class="fa fa-eye"></i>
                                            {{ trans('file.View') }}
                                        </button>
                                    @endif
                                </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tfoot>
                </table>
            </div>
    </section>
    <div id="sale-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>

                            {{ Form::open(['route' => 'sale.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
                            <input type="hidden" name="sale_id">
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
                            <i style="font-size: 15px;">{{ trans('file.Sale Details') }}</i>
                        </div>
                    </div>
                </div>
                <div id="sale-content" class="modal-body">
                </div>
                <br>
                <table class="table table-bordered product-sale-list">
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
                <div id="sale-footer" class="modal-body"></div>
            </div>
        </div>
    </div>
    <div id="purchase-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>
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
                            <i style="font-size: 15px;">{{ trans('file.Purchase Details') }}</i>
                        </div>
                    </div>
                </div>
                <div id="purchase-content" class="modal-body"></div>
                <br>
                <table class="table table-bordered product-purchase-list">
                    <thead>
                        <th>#</th>
                        <th>{{ trans('file.product') }}</th>
                        <th>Qty</th>
                        <th>{{ trans('file.Unit Cost') }}</th>
                        <th>{{ trans('file.Tax') }}</th>
                        <th>{{ trans('file.Discount') }}</th>
                        <th>{{ trans('file.Subtotal') }}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="purchase-footer" class="modal-body"></div>
            </div>
        </div>
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
    <div id="transfer-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>
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
                            <i style="font-size: 15px;">{{ trans('file.Transfer Details') }}</i>
                        </div>
                    </div>
                </div>
                <div id="transfer-content" class="modal-body">
                </div>
                <br>
                <table class="table table-bordered product-transfer-list">
                    <thead>
                        <th>#</th>
                        <th>{{ trans('file.product') }}</th>
                        <th>Qty</th>
                        <th>{{ trans('file.Unit Cost') }}</th>
                        <th>{{ trans('file.Tax') }}</th>
                        <th>{{ trans('file.Subtotal') }}</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
                <div id="transfer-footer" class="modal-body"></div>
            </div>
        </div>
    </div>
    <div id="p_return-details" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="container mt-3 pb-2 border-bottom">
                    <div class="row">
                        <div class="col-md-3">
                            <button id="print-btn" type="button" class="btn btn-default btn-sm d-print-none"><i
                                    class="dripicons-print"></i> {{ trans('file.Print') }}</button>
                            {{ Form::open(['route' => 'return-purchase.sendmail', 'method' => 'post', 'class' => 'sendmail-form']) }}
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
                        <th>{{ trans('file.Unit Cost') }}</th>
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


    <script type="text/javascript">
        $("ul#product").siblings('a').attr('aria-expanded', 'true');
        $("ul#product").addClass("show");
        $("ul#product #kardex-menu").addClass("active");
        $('#warehouse_id').val({{ $warehouse_id ?? '' }});
        let start_date = dateFormat($('#start_date').val());
        let end_date = dateFormat($('#end_date').val());

        $('.selectpicker').selectpicker('refresh');
        let fechaActual = new Date();
        let opcionesFecha = {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        let fechaTexto = fechaActual.toLocaleDateString('es-ES', opcionesFecha);
        let almacen = '{{ ucfirst(strtolower($report_data_list[0]->warehouse ?? '')) }}';
        let producto = '{{ ucfirst(strtolower($report_data_list[0]->product ?? '')) }}                      ';

        function calculateTotalAndDisplay() {
            let tableData = $('#report-table').DataTable().rows().data();

            let totalEntradas = 0;
            let totalSalidas = 0;


            tableData.each(function(row) {
                totalEntradas += parseInt(row[5]);
                totalSalidas += parseInt(row[6]);
            });
            const saldoFinal = (parseInt(tableData[0][7]) + totalEntradas) - totalSalidas;
            return {
                totalEntradas,
                totalSalidas,
                saldoFinal
            };
        }

        function reportHeadFormat(doc) {
            console.log(doc.content[1].table)
            doc.content.splice(1, 0, {
                text: `${fechaTexto}`,
                alignment: 'right',
                margin: [0, 0, 0, 12],
                bold: true
            });
            doc.content.splice(2, 0, {
                text: [{
                        text: 'PRODUCTO: ',
                        bold: true
                    },
                    {
                        text: producto,
                        bold: false
                    },
                    {
                        text: 'ALMACEN: ',
                        bold: true
                    },
                    {
                        text: almacen,
                        bold: false
                    }
                ],
                alignment: 'left',
                margin: [0, 0, 0, 12]

            });
            doc.content.splice(3, 0, {
                text: [{
                        text: 'FECHA INICIAL: ',
                        bold: true
                    },
                    {
                        text: start_date,
                        bold: false
                    },
                    {
                        text: '                         FECHA FINAL: ',
                        bold: true
                    },
                    {
                        text: end_date,
                        bold: false
                    }
                ],
                alignment: 'left',
                margin: [0, 0, 0, 12]
            });

            let tbodyEndIndex = doc.content.findIndex(item => item.table && item.table.body);
            const space = " "
            const footerValues = calculateTotalAndDisplay();
            // Insert your content after the end of tbody
            if (tbodyEndIndex !== -1) {

                doc.content.splice(tbodyEndIndex + 1, 0, {
                    canvas: [{
                        type: 'rect',
                        x: 0,
                        y: 0,
                        w: 516,
                        h: 4,
                        color: '#2d4154',
                    }],
                }, {
                    text: [{
                            text: 'Total entradas: ',
                            bold: true,
                            marin: [0, 0, 0, 15]
                        },
                        {
                            text: footerValues.totalEntradas,
                            bold: false,
                        },
                        {
                            text: `${space.repeat(38)}Total salidas:  `,
                            bold: true
                        },
                        {
                            text: footerValues.totalSalidas,
                            bold: false,
                        },
                        {
                            text: `${space.repeat(38)}Saldo final: `,
                            bold: true
                        },
                        {
                            text: footerValues.saldoFinal,
                            bold: false
                        }
                    ],
                    alignment: 'left',
                    margin: [0, 10, 0, 10],
                    fontSize: 12,
                    color: 'black'
                }, {
                    canvas: [{
                        type: 'rect',
                        x: 0,
                        y: 0,
                        w: 516,
                        h: 4,
                        color: '#2d4154',
                    }],
                });
            }
            doc.footer = function() {
                return {
                    text: `Saldo final = (Saldo inicial + Total entradas) - Total salidas`,
                    alignment: 'center'
                };
            };
        }

        $('#report-table').DataTable({
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
                "orderable": true,
                "target": 1
            }],
            'order': [
                [1, 'desc']
            ],
            'lengthMenu': [
                [5, 15, 25, -1],
                [5, 15, 25, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    title: 'Kardex',
                    extend: 'pdf',
                    customize: (doc) => reportHeadFormat(doc),
                    text: '{{ trans('file.PDF') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: false,

                },
                {
                    title: 'Kardex',
                    extend: 'excel',
                    text: '<span class="fa fa-file-excel-o"> Excel</span>',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: false
                },
                {
                    title: 'Kardex',
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                },
                {
                    title: 'Kardex',
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: true
                },
                {
                    title: 'Kardex',
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                }
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            const footer = (col) => $(dt_selector.column(col).footer())
            if (dt_selector.rows('.selected').any() && is_calling_first) {

                let rows = dt_selector.rows('.selected').indexes();
                const data = (col) => dt_selector.cells(rows, col, {
                    page: 'current'
                }).data();

                footer(5).html(data(5).sum().toFixed(2))
                footer(6).html(data(6).sum().toFixed(2))
                footer(7).html(data(7).sum().toFixed(2))
                footer(8).html(data(8).sum().toFixed(2))
                footer(9).html(data(9).sum().toFixed(2))

            } else {
                const data = (col) => dt_selector.column(col, {
                    page: 'current'
                }).data();
                footer(5).html(parseFloat(data(5).sum().toFixed(2)) + parseFloat(data(7)[0]));
                footer(6).html(data(6).sum().toFixed(2));
                footer(7).html(data(7)[data(7).length - 1]);
                footer(8).html(data(8)[data(8).length - 1]);
                footer(9).html(data(9)[data(9).length - 1]);

            }
        }

        var lims_productcodeSearch = $('#lims_productcodeSearch');

        lims_productcodeSearch.autocomplete({
            minLength: 0,
            source: "{{ route('purchases.getproducts') }}",
            focus: function(event, ui) {
                $("#lims_productcodeSearch").val(ui.item.value);
                return false;
            },
            response: function(event, ui) {

                if (ui.content.length == 1) {
                    var data = ui.content[0].value;
                    $("#lims_productcodeSearch").val(data);
                    $("#lims_productcode").val(data.split(' (')[0]);
                    $(this).autocomplete("close");

                };
            },
            select: function(event, ui) {
                var data = ui.item.value;
                $("#lims_productcodeSearch").val(data);
                $("#lims_productcode").val(data.split(' (')[0]);


            }
        }).autocomplete("instance")._renderItem = function(ul, item) {
            return $("<li>")
                .append("<div>" + item.value + "</div>")
                .appendTo(ul);
        };

        $(document).on("click", ".view", function() {

            switch ($(this).data('type')) {
                case 'VENTA':
                    saleDetails($(this).data('id'), $(this).data('type'));

                    break;

                case 'COMPRA':
                    purchaseDetails($(this).data('id'), $(this).data('type'));
                    break;

                case 'RETURN':
                    returnDetails($(this).data('id'), $(this).data('type'));
                    break;

                case 'TRANSFER':
                    transferDetails($(this).data('id'), $(this).data('type'));
                    break;

                case 'COMPRA RETURN':
                    purchaseReturnDetails($(this).data('id'), "COMPRA_RETURN");
                    break;
            }

        });

        function saleDetails(id, type) {

            $.get(`kardex/${type}/${id}`, function(sale) {

                var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + sale[0] +
                    '<br><strong>{{ trans('file.reference') }}: </strong>' + sale[1] +
                    '<br><strong>{{ trans('file.Warehouse') }}: </strong>' + sale[27] +
                    '<br><strong>{{ trans('file.Sale Status') }}: </strong>' + sale[2] +
                    '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                    sale[
                        3] + '<br>' + sale[4] + '<br>' + sale[5] + '<br>' + sale[6] + '<br>' + sale[7] + '<br>' +
                    sale[8] +
                    '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                    sale[9] + '<br>' + sale[10] + '<br>' + sale[11] + '<br>' + sale[12] + '</div></div></div>';


                $.get('sales/product_sale/' + id, function(data) {
                    $(".product-sale-list tbody").remove();
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
                        cols += '<td>' + parseFloat(subtotal[index] / qty[index]).toFixed(2) +
                            '</td>';
                        cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                        cols += '<td>' + discount[index] + '</td>';
                        cols += '<td>' + subtotal[index] + '</td>';
                        newRow.append(cols);
                        newBody.append(newRow);
                    });
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=4><strong>{{ trans('file.Total') }}:</strong></td>';
                    cols += '<td>' + sale[14] + '</td>';
                    cols += '<td>' + sale[15] + '</td>';
                    cols += '<td>' + sale[16] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                    cols += '<td>' + sale[17] + '(' + sale[18] + '%)' + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Order Discount') }}:</strong></td>';
                    cols += '<td>' + (sale[19] || 0) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    if (sale[28]) {
                        var newRow = $("<tr>");
                        cols = '';
                        cols += '<td colspan=6><strong>{{ trans('file.Coupon Discount') }} [' + sale[28] +
                            ']:</strong></td>';
                        cols += '<td>' + sale[29] + '</td>';
                        newRow.append(cols);
                        newBody.append(newRow);
                    }

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Shipping Cost') }}:</strong></td>';
                    cols += '<td>' + (sale[20] || 0) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    if (sale[30] > 0) {
                        var newRow = $("<tr>");
                        cols = '';
                        cols += '<td colspan=6><strong>Propinas:</strong></td>';
                        cols += '<td>' + sale[30] + '</td>';
                        newRow.append(cols);
                        newBody.append(newRow);
                    }

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                    cols += '<td>' + sale[21] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Paid Amount') }}:</strong></td>';
                    cols += '<td>' + sale[22] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Due') }}:</strong></td>';
                    cols += '<td>' + parseFloat(sale[21] - sale[22]).toFixed(2) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);
                    $("table.product-sale-list").append(newBody);
                });
                var htmlfooter = '<p><strong>{{ trans('file.Sale Note') }}:</strong> ' + (sale[23] || '') +
                    '</p><p><strong>{{ trans('file.Staff Note') }}:</strong> ' + (sale[24] || '') +
                    '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + sale[25] + '<br>' + sale[26];
                $('#sale-content').html(htmltext);
                $('#sale-footer').html(htmlfooter);
                $('#sale-details').modal('show');
            });
        }

        function purchaseDetails(id, type) {

            $.get(`kardex/${type}/${id}`, function(purchase) {

                var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + purchase[0] +
                    '<br><strong>{{ trans('file.reference') }}: </strong>' + purchase[1] +
                    '<br><strong>{{ trans('file.Purchase Status') }}: </strong>' + purchase[2] +
                    '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                    purchase[
                        4] + '<br>' + purchase[5] + '<br>' + purchase[6] +
                    '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                    purchase[7] + '<br>' + purchase[8] + '<br>' + purchase[9] + '<br>' + purchase[10] + '<br>' +
                    purchase[11] +
                    '<br>' + purchase[12] + '</div></div></div>';

                $.get('purchases/product_purchase/' + id, function(data) {
                    $(".product-purchase-list tbody").remove();
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
                    cols += '<td>' + purchase[13] + '</td>';
                    cols += '<td>' + purchase[14] + '</td>';
                    cols += '<td>' + purchase[15] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                    cols += '<td>' + purchase[16] + '(' + purchase[17] + '%)' + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Order Discount') }}:</strong></td>';
                    cols += '<td>' + (purchase[18] || 0) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Shipping Cost') }}:</strong></td>';
                    cols += '<td>' + (purchase[19] || 0) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                    cols += '<td>' + purchase[20] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Paid Amount') }}:</strong></td>';
                    cols += '<td>' + purchase[21] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Due') }}:</strong></td>';
                    cols += '<td>' + (purchase[20] - purchase[21]) + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    $("table.product-purchase-list").append(newBody);
                });

                var htmlfooter = '<p><strong>{{ trans('file.Note') }}:</strong> ' + (purchase[22] || "") +
                    '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + purchase[23] + '<br>' + purchase[
                        24];

                $('#purchase-content').html(htmltext);
                $('#purchase-footer').html(htmlfooter);
                $('#purchase-details').modal('show');
            });
        }

        function returnDetails(id, type) {
            $.get(`kardex/${type}/${id}`, function(returns) {
                var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + returns[0] +
                    '<br><strong>{{ trans('file.reference') }}: </strong>' + returns[1] +
                    '<br><strong>{{ trans('file.Warehouse') }}: </strong>' + returns[2] +
                    '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                    returns[
                        3] + '<br>' + returns[4] + '<br>' + returns[5] + '<br>' + returns[6] + '<br>' + returns[7] +
                    '<br>' +
                    returns[8] +
                    '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                    returns[9] + '<br>' + returns[10] + '<br>' + returns[11] + '<br>' + returns[12] +
                    '</div></div></div>';
                $.get('return-sale/product_return/' + id, function(data) {
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
                    '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + returns[22] + '<br>' + returns[
                        23];
                $('#return-content').html(htmltext);
                $('#return-footer').html(htmlfooter);
                $('#return-details').modal('show');
            });
        }

        function transferDetails(id, type) {
            $.get(`kardex/${type}/${id}`, function(transfer) {

                var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + transfer[0] +
                    '<br><strong>{{ trans('file.reference') }}: </strong>' + transfer[1] +
                    '<br><strong> {{ trans('file.Transfer') }} {{ trans('file.Status') }}: </strong>' +
                    transfer[2] +
                    '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                    transfer[
                        4] + '<br>' + transfer[5] + '<br>' + transfer[6] +
                    '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                    transfer[7] + '<br>' + transfer[8] + '<br>' + transfer[9] + '</div></div></div>';

                $.get('transfers/product_transfer/' + id, function(data) {
                    $(".product-transfer-list tbody").remove();
                    var name_code = data[0];
                    var qty = data[1];
                    var unit_code = data[2];
                    var tax = data[3];
                    var tax_rate = data[4];
                    var subtotal = data[5];
                    var newBody = $("<tbody>");
                    $.each(name_code, function(index) {
                        var newRow = $("<tr>");
                        var cols = '';
                        cols += '<td><strong>' + (index + 1) + '</strong></td>';
                        cols += '<td>' + name_code[index] + '</td>';
                        cols += '<td>' + qty[index] + ' ' + unit_code[index] + '</td>';
                        cols += '<td>' + (subtotal[index] / qty[index]) + '</td>';
                        cols += '<td>' + tax[index] + '(' + tax_rate[index] + '%)' + '</td>';
                        cols += '<td>' + subtotal[index] + '</td>';
                        newRow.append(cols);
                        newBody.append(newRow);
                    });

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=4><strong>{{ trans('file.Total') }}:</strong></td>';
                    cols += '<td>' + transfer[10] + '</td>';
                    cols += '<td>' + transfer[11] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=5><strong>{{ trans('file.Shipping Cost') }}:</strong></td>';
                    cols += '<td>' + transfer[12] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=5><strong>{{ trans('file.grand total') }}:</strong></td>';
                    cols += '<td>' + transfer[13] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    $("table.product-transfer-list").append(newBody);
                });

                var htmlfooter = '<p><strong>{{ trans('file.Note') }}:</strong> ' + transfer[14] +
                    '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + transfer[15] + '<br>' + transfer[
                        16];

                $('#transfer-content').html(htmltext);
                $('#transfer-footer').html(htmlfooter);
                $('#transfer-details').modal('show');
            });
        }

        function purchaseReturnDetails(id, type) {
            $.get(`kardex/${type}/${id}`, function(returns) {

                var htmltext = '<strong>{{ trans('file.Date') }}: </strong>' + returns[0] +
                    '<br><strong>{{ trans('file.reference') }}: </strong>' + returns[1] +
                    '<br><br><div class="row"><div class="col-md-6"><strong>{{ trans('file.From') }}:</strong><br>' +
                    returns[
                        2] + '<br>' + returns[3] + '<br>' + returns[4] +
                    '</div><div class="col-md-6"><div class="float-right"><strong>{{ trans('file.To') }}:</strong><br>' +
                    returns[5] + '<br>' + returns[6] + '<br>' + returns[7] + '<br>' + returns[8] + '<br>' + returns[
                        9] + ', ' +
                    returns[10] + '</div></div></div>';
                $.get('return-purchase/product_return/' + id, function(data) {
                    console.log(data)
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
                    cols += '<td>' + returns[12] + '</td>';
                    cols += '<td>' + returns[13] + '</td>';
                    cols += '<td>' + returns[14] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.Order Tax') }}:</strong></td>';
                    cols += '<td>' + returns[15] + '(' + returns[16] + '%)' + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    var newRow = $("<tr>");
                    cols = '';
                    cols += '<td colspan=6><strong>{{ trans('file.grand total') }}:</strong></td>';
                    cols += '<td>' + returns[17] + '</td>';
                    newRow.append(cols);
                    newBody.append(newRow);

                    $("table.product-return-list").append(newBody);
                });
                var htmlfooter = '<p><strong>{{ trans('file.Return Note') }}:</strong> ' + (returns[18] || '') +
                    '</p><p><strong>{{ trans('file.Staff Note') }}:</strong> ' + (returns[19] || '') +
                    '</p><strong>{{ trans('file.Created By') }}:</strong><br>' + (returns[20] || '') + '<br>' +
                    returns[
                        21];
                $('#return-content').html(htmltext);
                $('#return-footer').html(htmlfooter);
                $('#return-details').modal('show');
            });




        }
        $('#make_control').on('click', function() {

            $("#search_form").attr('action', "{{ route('kardex.control') }}")
            $("#search_form").submit()
        })

        $('#make_warehouse_control').on('click', function(e) {

            $('#lims_productcode').attr('required', false)
            $('#lims_productcodeSearch').attr('required', false)
            $('#start_date').attr('required', false)
            $('#end_date').attr('required', false)
            $("#search_form").attr('action', "{{ route('kardex.warehouseControl') }}")
        })


        function dateFormat(startDateString) {
            let startDate = new Date(startDateString);
            let formattedStartDate = startDate.toLocaleDateString('es-ES');
            console.log(formattedStartDate)
            return formattedStartDate;
        }
    </script>
@endsection
