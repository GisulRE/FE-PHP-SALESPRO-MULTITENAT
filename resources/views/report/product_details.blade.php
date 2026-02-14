@extends('layout.main') @section('content')
    @if (empty($list_products))
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
                    <h3 class="text-center">Informe de Producto por Precios</h3>
                </div>
            </div>
        </div>
        <div class="table-responsive mb-4">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>Codigo</th>
                        <th style="width: 200px;">Producto</th>
                        <th>Categoria</th>
                        <th>Costo</th>
                        <th>Precio Defecto</th>
                        <th>Precio A</th>
                        <th>Precio B</th>
                        <th>Precio C</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($list_products as $key => $item)
                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ $item->code }}</td>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->category->name }}</td>
                            <td>{{ $item->cost }}</td>
                            <td>{{ $item->price }}</td>
                            <td>{{ $item->price_a }}</td>
                            <td>{{ $item->price_b }}</td>
                            <td>{{ $item->price_c }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #product-detail-report-menu").addClass("active");

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
                [10, 25, 50, "Todos"]
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
                //datatable_sum(api, false);
            }
        });

        function datatable_sum(dt_selector, is_calling_first) {
            if (dt_selector.rows('.selected').any() && is_calling_first) {
                var rows = dt_selector.rows('.selected').indexes();

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
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.column(7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.column(8, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(9).footer()).html(dt_selector.column(9, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }
    </script>
@endsection
