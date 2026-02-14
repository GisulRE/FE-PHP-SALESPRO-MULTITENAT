@extends('layout.main') @section('content')

    @if (empty($sales))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!' }}
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Sale Biller Report') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.saleBiller', 'method' => 'post']) !!}
                <div class="row mb-12">
                    <div class="col-md-6 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                        value="{{ $start_date }}" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                    <label class="d-tc mt-2" style="margin-left: 5px"><strong> A </strong> &nbsp;</label>
                                    <input name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                        value="{{ $end_date }}" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mb-12">
                        <div class="col-md-4 mt-3" style="margin-left: 3.333%;">
                            <div class="form-group row">
                                <label class="d-tc mt-2"><strong>{{ trans('file.Choose Biller') }}</strong> &nbsp;</label>
                                <div class="d-tc">
                                    <input type="hidden" name="biller_id_hidden" value="{{ $biller_id }}" />
                                    <select id="biller_id" name="biller_id" class="selectpicker form-control"
                                        data-live-search="true" data-live-search-style="begins">
                                        @if (Auth::user()->role_id < 3)
                                            <option value="0">{{ trans('file.All Billers') }}</option>
                                        @endif
                                        @foreach ($billers as $biller)
                                            <option value="{{ $biller->id }}">{{ $biller->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Warehouse') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="hidden" name="warehouse_id_hidden" value="{{ $warehouse_id }}" />
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    @if (Auth::user()->role_id < 3 || sizeof($lims_warehouse_list) > 1)
                                        <option value="0">{{ trans('file.All Warehouse') }}</option>
                                    @endif
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
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
                </div>

                {!! Form::close() !!}
            </div>
        </div>
        <div class="table-responsive">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.reference') }}/Nro. Factura</th>
                        <th>{{ trans('file.date') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th>{{ trans('file.customer') }}</th>
                        <th>Propinas</th>
                        <th>{{ trans('file.Discount') }}</th>
                        <th>{{ trans('file.grand total') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($sales))
                        @foreach ($sales as $sale)
                            <tr>
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->reference_no }}
                                    @if ($sale->invoice_no > 0)
                                        | {{ $sale->invoice_no }}
                                    @endif
                                </td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($sale->date_sell)) }}</td>
                                @if ($sale->sale_status == 1)
                                    <td>
                                        <div class="badge badge-success">{{ trans('file.Completed') }}</div>
                                    </td>
                                @else
                                    <td>
                                        <div class="badge badge-danger">{{ trans('file.Pending') }}</div>
                                    </td>
                                @endif
                                <td>{{ $sale->customer->name }}</td>
                                <td>{{ number_format((float) $sale->total_tips, 2) }}</td>
                                <td>{{ number_format((float) $sale->total_discount, 2) }}</td>
                                @if ($sale->invoice_no > 0)
                                    <?php $iva = (float) ($lims_tax->rate * $sale->grand_total) / 100; ?>
                                    <td>{{ number_format((float) $sale->grand_total - $iva, 2, '.', '') }}</td>
                                @else
                                    <td>{{ number_format((float) $sale->grand_total, 2, '.', '') }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <th></th>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>0</th>
                    <th>0</th>
                    <th>0</th>
                </tfoot>
            </table>
        </div>
    </section>


    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #sale-biller-report-menu").addClass("active");

        $('#warehouse_id').val($('input[name="warehouse_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');

        $('#biller_id').val($('input[name="biller_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');

        $('#report-table').DataTable({
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
                        columns: ':visible:not(.not-exported)',
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
                        columns: ':visible:not(.not-exported)',
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
                        columns: ':visible:not(.not-exported)',
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
                }
            ],
            drawCallback: function() {
                var api = this.api();
                datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));

            } else {
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.column(7, {
                    page: 'current'
                }).data().sum().toFixed(2));

            }
        }

        $(".daterangepicker-field").daterangepicker({
            callback: function(startDate, endDate, period) {
                var start_date = startDate.format('YYYY-MM-DD');
                var end_date = endDate.format('YYYY-MM-DD');
                var title = start_date + ' To ' + end_date;
                $(this).val(title);
                $('input[name="start_date"]').val(start_date);
                $('input[name="end_date"]').val(end_date);
            }
        });
    </script>
@endsection
