@extends('layout.main') @section('content')

    <section class="forms">
        <div class="card">
            <div class="container-fluid">
                <h3>Arqueo de Caja</h3>
            </div>
            {!! Form::open(['route' => 'accounts.balancesheetaccount', 'method' => 'post']) !!}
            <div class="row mb-12">
                <div class="col-md-4 mt-3" style="margin-left: 3.333%;">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input id="start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY"
                                    type="date" value="{{ $start_date }}" required>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mt-3" style="margin-left: 3.333%;">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{ trans('file.Choose Biller') }}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="hidden" name="biller_id_hidden" value="{{ $biller->id }}" />
                            <input type="hidden" name="account_id_hidden" value="{{ $biller->account_id }}" />
                            <select id="biller_id" name="biller_id" class="selectpicker form-control"
                                data-live-search="true" data-live-search-style="contains">
                                @foreach ($lims_biller_list as $biller)
                                    <option value="{{ $biller->id }}">{{ $biller->name }}
                                        [PV-{{ $biller->punto_venta_siat }}]</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 offset-md-0 mt-3">
                    <div class="form-group">
                        <button class="btn btn-primary" type="submit">{{ trans('file.submit') }}</button>
                    </div>
                </div>
                <div class="col-md-1 offset-md-0 mt-3">
                    <div class="form-group">
                        @if ($cashier_open)
                            <button type="button" class="btn btn-danger" onclick="openDialog('{{ date('d-m-Y H:i:s') }}')"
                                data-toggle="modal" data-target="#editModal"><i class="dripicons-closed"></i> Cerrar
                                Caja</button>
                        @endif

                    </div>
                </div>
            </div>
            {!! Form::close() !!}
            <div class="row mb-12">

            </div>
            <div class="table-responsive mb-2">
                <table id="resume-table" class="box-table"
                    style="border: 1px solid black;top:50%; left:50%; margin-left:-15%; position:relative;">
                    <thead>
                    </thead>
                    <tbody>
                        <tr style="border: 1px solid black;">
                            <td style="font-weight: bold;">SALDO ANTERIOR (A) </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold;"> Saldo Anterior (A) : </td>
                            <td style="font-weight: bold;text-align: right;"><span id="totalsaldant"></span></td>
                        </tr>
                        <tr style="border: 1px solid black;">
                            <td style="font-weight: bold;"> INGRESOS (B) </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Efectivo (B1) : </td>
                            <td style="text-align: right;"><span id="totalingbs"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Tarjeta : </td>
                            <td style="text-align: right;"><span id="totaltarjcred"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Qr : </td>
                            <td style="text-align: right;"><span id="totalqr"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Cheques : </td>
                            <td style="text-align: right;"><span id="totalcheq"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Depositos : </td>
                            <td style="text-align: right;"><span id="totaldep"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Vales : </td>
                            <td style="text-align: right;"><span id="totalvale"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Otros : </td>
                            <td style="text-align: right;"><span id="totalotros"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Ajustes (B2) : </td>
                            <td style="text-align: right;"><span id="totalajusting"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold;">Total Ingresos : </td>
                            <td style="font-weight: bold;text-align: right;"><span id="totalingresos"></span></td>
                        </tr>
                        <tr style="border: 1px solid black;">
                            <td style="font-weight: bold;"> EGRESOS (C) </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Transferencias : </td>
                            <td style="text-align: right;"><span id="totaltrans"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Gastos : </td>
                            <td style="text-align: right;"><span id="totalgast"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Nominas : </td>
                            <td style="text-align: right;"><span id="totalnomi"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Compras : </td>
                            <td style="text-align: right;"><span id="totalcomp"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Devoluciones : </td>
                            <td style="text-align: right;"><span id="totaldevol"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>Ajustes : </td>
                            <td style="text-align: right;"><span id="totalajustegr"></span></td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold;">Total Egresos : </td>
                            <td style="font-weight: bold;text-align: right;"><span id="totalegresos"></span></td>
                        </tr>
                        <tr style="border: 1px solid black;">
                            <td style="font-weight: bold;"> TOTALES </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold;">Total General(A+B-C) : </td>
                            <td style="font-weight: bold;text-align: right;"><span id="totalgral"></span></td>
                        </tr>
                        <tr>
                            <td> </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td style="font-weight: bold;">Total Efectivo (A+B1+B2-C): </td>
                            <td style="font-weight: bold;text-align: right;"><span id="totalefectv"></span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="table-responsive mb-4">
            <h4 style="color: rgb(24, 206, 93)">INGRESOS - EGRESOS</h4>
            <table id="account-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Nro. Movimiento</th>
                        <th>Ingreso Efectivo Bs.</th>
                        <th>Egreso Efectivo Bs.</th>
                        <th>Tarjeta Crédito/Débito</th>
                        <th>Qr</th>
                        <th>Cheque</th>
                        <th>Deposito</th>
                        <th>Vale</th>
                        <th>Otros</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $totalingrbs = 0;
                    $totalegrebs = 0;
                    $totaltarjcred = 0;
                    $totalqr = 0;
                    $totalcheq = 0;
                    $totaldep = 0;
                    $totalvale = 0;
                    $totalotros = 0;
                    $totalajusting = 0;
                    ?>
                    @if ($accountfull != null)
                        @foreach ($accountfull as $key => $account)
                            <tr>
                                <?php
                                if (!array_key_exists('ajustement', $accountfull[$key][0])) {
                                    $totalingrbs = $totalingrbs + (float) $accountfull[$key][0]['amount'];
                                } else {
                                    $totalajusting = $totalajusting + (float) $accountfull[$key][0]['amount'];
                                }
                                if (!array_key_exists('ajustement', $accountfull[$key][0])) {
                                    $totalegrebs = $totalegrebs + (float) $accountfull[$key][1]['amount'];
                                }
                                if (isset($accountfull[$key][0]['sale'])) {
                                    $totaltarjcred = $totaltarjcred + (float) $accountfull[$key][3]['amount'];
                                    $totalqr = $totalqr + (float) $accountfull[$key][5]['amount'];
                                    $totalcheq = $totalcheq + (float) $accountfull[$key][2]['amount'];
                                    $totaldep = $totaldep + (float) $accountfull[$key][4]['amount'];
                                    $totalvale = $totalvale + (float) $accountfull[$key][6]['amount'];
                                    $totalotros = $totalotros + (float) $accountfull[$key][7]['amount'];
                                }
                                ?>
                                <td>{{ $key }}</td>
                                <td>{{ $accountfull[$key][0]['reference'] }}</td>
                                <td>{{ number_format((float) $accountfull[$key][0]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][1]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][3]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][5]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][2]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][4]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][6]['amount'], 2, '.', '') }}</td>
                                <td>{{ number_format((float) $accountfull[$key][7]['amount'], 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                    @endif
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
                    <th></th>
                    <th></th>
                </tfoot>
            </table>
        </div>
        <div class="table-responsive mb-4">
            <h4 style="color: rgb(59, 71, 235)">VENTAS POR COBRAR</h4>
            <table id="receivable-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Nro. Movimiento</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>{{ trans('file.Sale Status') }}</th>
                        <th>{{ trans('file.Payment Status') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                        <th>Cobrado</th>
                        <th>Por Cobrar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_sale_dues as $key => $sale)
                        <tr>
                            <td>{{ $sale->id }}</td>
                            <td>{{ $sale->reference_no }}</td>
                            <td>{{ $sale->customer->name }}</td>
                            @if ($sale->sale_status == 1)
                                <td>
                                    <div class="badge badge-success">{{ trans('file.Completed') }}</div>
                                </td>
                            @elseif($sale->sale_status == 4)
                                <td>
                                    <div class="badge badge-info">{{ trans('file.Receivable') }}</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Pending') }}</div>
                                </td>
                            @endif
                            @if ($sale->payment_status == 1)
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Pending') }}</div>
                                </td>
                            @elseif($sale->payment_status == 2)
                                <td>
                                    <div class="badge badge-danger">{{ trans('file.Due') }}</div>
                                </td>
                            @elseif($sale->payment_status == 3)
                                <td>
                                    <div class="badge badge-warning">{{ trans('file.Partial') }}</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-success">{{ trans('file.Paid') }}</div>
                                </td>
                            @endif
                            <td>{{ number_format($sale->grand_total, 2) }}</td>
                            <td> {{ number_format($sale->paid_amount, 2) }}</td>
                            <td> {{ number_format($sale->grand_total - $sale->paid_amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
            class="modal fade text-left bd-example-modal-sm">
            <div role="document" class="modal-dialog modal-sm" style="width: 45%;">
                <div class="modal-content">
                    {{ Form::open(['route' => ['accounts-cashier.close'], 'method' => 'PUT']) }}
                    <div class="modal-header">
                        <h5 id="title_cerrar" class="modal-title"> </h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Cuenta:</label>
                            <input required type="text" name="account_name" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Monto a Cerrar:</label>
                            <input required type="number" name="amount_end" step="any" class="form-control"
                                readonly>
                        </div>
                        <input type="hidden" name="account_id_cerrar">
                        <div class="form-group">
                            <input type="submit" value="Cerrar Ahora" class="btn btn-primary">
                        </div>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#account").siblings('a').attr('aria-expanded', 'true');
        $("ul#account").addClass("show");
        $("ul#account #account-mov-report-menu").addClass("active");

        $('#biller_id').val($('input[name="biller_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');
        var saldoant = <?php echo $saldoant; ?>;
        var totalingbs = <?php echo $totalingrbs; ?>;
        var totaliegbs = <?php echo $totalegrebs; ?>;
        var totaltarjcred = <?php echo $totaltarjcred; ?>;
        var totalqr = <?php echo $totalqr; ?>;
        var totalcheq = <?php echo $totalcheq; ?>;
        var totaldep = <?php echo $totaldep; ?>;
        var totalvale = <?php echo $totalvale; ?>;
        var totalotros = <?php echo $totalotros; ?>;
        var totalajusteing = <?php echo $totalajusting; ?>;
        var totalingresos = totalingbs + totaltarjcred + totalqr + totaldep + totalvale + totalajusteing;

        var totalcompras = <?php echo $compras; ?>;
        var totaltranfs = <?php echo $transferencias; ?>;
        var totalnominas = <?php echo $nominas; ?>;
        var totalgastos = <?php echo $gastos; ?>;
        var totaldevols = <?php echo $devolucion; ?>;
        var totalajustegr = <?php echo $ajustegr; ?>;

        var totalegresos = totalcompras + totaltranfs + totalnominas + totalgastos + totaldevols + totalajustegr;
        var totalgeneral = saldoant + totalingresos - totalegresos;
        var totalefectv = saldoant + totalingbs + totalajusteing - totalegresos;

        $('#totalsaldant').text(saldoant.toFixed(2));

        $('#totalingbs').text(totalingbs.toFixed(2));
        $('#totalegrbs').text(totaliegbs.toFixed(2));
        $('#totaltarjcred').text(totaltarjcred.toFixed(2));
        $('#totalqr').text(totalqr.toFixed(2));
        $('#totalcheq').text(totalcheq.toFixed(2));
        $('#totaldep').text(totaldep.toFixed(2));
        $('#totalvale').text(totalvale.toFixed(2));
        $('#totalotros').text(totalotros.toFixed(2));
        $('#totalajusting').text(totalajusteing.toFixed(2));
        $('#totalingresos').text(totalingresos.toFixed(2));

        $('#totalcomp').text(totalcompras.toFixed(2));
        $('#totaltrans').text(totaltranfs.toFixed(2));
        $('#totalgast').text(totalgastos.toFixed(2));
        $('#totalnomi').text(totalnominas.toFixed(2));
        $('#totaldevol').text(totaldevols.toFixed(2));
        $('#totalajustegr').text(totalajustegr.toFixed(2));
        $('#totalegresos').text(totalegresos.toFixed(2));
        $('#totalgral').text(totalgeneral.toFixed(2));
        $('#totalefectv').text(totalefectv.toFixed(2));

        var table = $('#account-table').DataTable({
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
                    'targets': 0
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
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
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

                $(dt_selector.column(2).footer()).html(dt_selector.cells(rows, 2, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(3).footer()).html(dt_selector.cells(rows, 3, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(4).footer()).html(dt_selector.cells(rows, 4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.cells(rows, 9, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(2).footer()).html(dt_selector.cells(rows, 2, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(3).footer()).html(dt_selector.cells(rows, 3, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(4).footer()).html(dt_selector.cells(rows, 4, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.cells(rows, 9, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        var table = $('#receivable-table').DataTable({
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
                    'targets': 0
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
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
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
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
            drawCallback: function() {
                var api = this.api();
                //datatable_sum(api, false);
            }
        });


        function openDialog(date_now) {
            //var date_now = <?php echo (string) date('d-m-Y H:i:s'); ?>;
            var id = $('input[name="account_id_hidden"]').val();
            var url = "cashier/total/" + id;
            $.get(url, function(data) {
                console.log(data);
                var start = data.start_date;
                $('#title_cerrar').text("Cerrar Desde: " + start + " Hasta: " + date_now);
                $('input[name="account_id_cerrar"]').val(id);
                $('input[name="amount_end"]').val(data.totalbalance);
                $('input[name="account_name"]').val(data.account.name + "[" + data.account.account_no + "]");
            });
        }
    </script>
@endsection
