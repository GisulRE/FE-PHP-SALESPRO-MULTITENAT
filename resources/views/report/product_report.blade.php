@extends('layout.main') @section('content')
    @if (empty($list_report))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!' }}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Product Report') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.product', 'method' => 'post']) !!}
                <div class="row mb-12">
                    <div class="col-md-5 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Warehouse') }}</strong> &nbsp;</label>
                            <div class="d-tc" style="width: 50%;">
                                <input type="hidden" name="warehouse_id_hidden" value="{{ $warehouse_id }}" />
                                <select id="warehouse_id" name="warehouse_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    data-live-search="true" data-live-search-style="begins">
                                    @if (Auth::user()->role_id < 3)
                                        <option value="0" disabled>{{ trans('file.All Warehouse') }}</option>
                                    @endif
                                    @foreach ($lims_warehouse_list as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3" style="margin-left: -5%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>Con Stock</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="checkbox" id="con_stock" name="con_stock" value="{{ $stock }}"
                                    class="form-control" style="margin-top: 10px;" />
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
        <div class="table-responsive mb-4">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Codigo</th>
                        <th>Producto</th>
                        <th>Categoria</th>
                        <th>Marca</th>
                        <th>{{ trans('file.Unit') }}</th>
                        <th>Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list_report as $key => $item)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->product }}</td>
                            <td>{{ $item->category }}</td>
                            <td>{{ $item->brand }}</td>
                            <td>{{ $item->unit_code }}</td>
                            <td>{{ $item->qty }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <th></th>
                    <th>Total</th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th></th>
                    <th>0.00</th>
                </tfoot>
            </table>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #product-report-menu").addClass("active");
        var stock = $('#con_stock').val();
        $('#warehouse_id').val($('input[name="warehouse_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');
        var role_id = <?php echo json_encode(\Auth::user()->role_id); ?>;
        var biller = <?php echo json_encode(\Auth::user()->biller); ?>;
        if (role_id > 2 && biller) {

        } else {

        }

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
                    },
                    action: function(e, dt, button, config) {
                        datatable_sum(dt, true);
                        $.fn.dataTable.ext.buttons.pdfHtml5.action.call(this, e, dt, button, config);
                        datatable_sum(dt, false);
                    },
                    footer: true
                },
                {
                    extend: 'excel',
                    text: '<span class="fa fa-file-excel-o"> Excel</span>',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                    }
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
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

                $(dt_selector.column(6).footer()).html(dt_selector.cells(rows, 6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        console.log(stock);
        if (stock == 1) {
            $('#con_stock').prop('checked', true);
            $('#con_stock').attr('value', 'true');
        } else {
            $('#con_stock').prop('checked', false);
            $('#con_stock').attr('value', 'false');
        }

        $("#con_stock").on('change', function() {
            if ($(this).is(':checked')) {
                $(this).attr('value', 'true');
            } else {
                $(this).attr('value', 'false');
            }
        });
    </script>
@endsection
