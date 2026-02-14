@extends('layout.main') @section('content')
    @if (empty($report_data_list))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No existe datos en este rango de fechas!' }}
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Report Product Renueve') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.saleRenueve', 'method' => 'post']) !!}
                <div class="col-md-11 offset-md-1 mt-3 mb-3">
                    <div class="form-group row">
                        <div class="col-md-2">
                            <label>Desde</label>
                            <input name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                value="{{ $start_date }}" required>
                        </div>
                        <div class="col-md-2">
                            <label>Hasta</label>
                            <input name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                value="{{ $end_date }}" required>
                        </div>
                        <div class="col-md-3">
                            <label>{{ trans('file.Biller') }}</label>
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
                        <div class="col-md-3">
                            <label>{{ trans('file.Warehouse') }}</label>
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
                        <div class="col-md-1">
                            <label> &nbsp;</label>
                            <button class="btn btn-primary fa fa-search" type="submit" style="margin-left: 5px">
                                {{ trans('file.Search') }}</button>
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
                            <th>{{ trans('file.reference') }}</th>
                            <th>{{ trans('file.Warehouse') }}</th>
                            <th>{{ trans('file.Biller') }}</th>
                            <th>{{ trans('file.Customer') }}</th>
                            <th>Metodo Pago</th>
                            <th>{{ trans('file.product') }}</th>
                            <th>{{ trans('file.Qty') }}</th>
                            <th>{{ trans('file.Unit Price') }}</th>
                            <th>Descuento</th>
                            <th>Costo Total</th>
                            <th>Venta Total</th>
                            <th>Ganancia Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($report_data_list))
                            @foreach ($report_data_list as $key => $sale)
                                <?php $key = $key + 1; ?>
                                <tr>
                                    <td>{{ $key++ }}</td>
                                    <td>{{ $sale->date }}</td>
                                    <td>{{ $sale->reference_sale }}</td>
                                    <td>{{ $sale->warehouse }}</td>
                                    <td>{{ $sale->biller }}</td>
                                    <td>{{ $sale->customer }}</td>
                                    <td>{!! $sale->method !!}</td>
                                    <td>{{ $sale->product }}</td>
                                    <td>{{ $sale->qty }}</td>
                                    <td>{{ number_format($sale->unit_price, 2, '.', '') }}</td>
                                    <td>{{ number_format($sale->discount, 2, '.', '') }}</td>
                                    <td>{{ number_format($sale->cost * $sale->qty, 2, '.', '') }}</td>
                                    <td>{{ number_format($sale->total, 2, '.', '') }}</td>
                                    <td>{{ number_format($sale->total - $sale->cost_total, 2, '.', '') }}</td>
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
                        <th>Total</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                        <th>0.00</th>
                    </tfoot>
                </table>
            </div>
    </section>


    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #sale-renueve-report-menu").addClass("active");

        $('#warehouse_id').val({{ $warehouse_id }});
        $('#biller_id').val({{ $biller_id }});
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
            }, ],
            'lengthMenu': [
                [10, 25, 50, -1],
                [10, 25, 50, "All"]
            ],
            dom: '<"row"lfB>rtip',
            buttons: [{
                    title: '{{ trans('file.Report Product Renueve') }}',
                    extend: 'pdf',
                    text: '{{ trans('file.PDF') }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: true
                },
                {                    
                    title: '{{ trans('file.Report Product Renueve') }}',
                    extend: 'excel',
                    text: '<span class="fa fa-file-excel-o"> Excel</span>',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: true
                },
                {
                    title: '{{ trans('file.Report Product Renueve') }}',
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                },
                {
                    title: '{{ trans('file.Report Product Renueve') }}',
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    orientation: 'landscape',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    },
                    footer: true
                },
                {
                    title: '{{ trans('file.Report Product Renueve') }}',
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
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.cells(rows, 9, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(10).footer()).html(dt_selector.cells(rows, 10, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(11).footer()).html(dt_selector.cells(rows, 11, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(12).footer()).html(dt_selector.cells(rows, 12, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(13).footer()).html(dt_selector.cells(rows, 13, {
                    page: 'current'
                }).data().sum().toFixed(2));

            } else {
                $(dt_selector.column(8).footer()).html(dt_selector.column(8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.column(9, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(10).footer()).html(dt_selector.column(10, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(11).footer()).html(dt_selector.column(11, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(12).footer()).html(dt_selector.column(12, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(13).footer()).html(dt_selector.column(13, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }
    </script>
@endsection
