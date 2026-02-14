@extends('layout.main') @section('content')

    @if (empty($listResumen))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!' }}</div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">Reporte Cuenta Contable Ingresos</h3>
                </div>
                <div class="row mb-12">
                    <div class="col-md-2 mt-3" style="left: 10px;">
                        <div class="form-group">
                            <label class="label">Desde</label>
                            <input id="start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY"
                                type="date" onchange="consultar()" value="{{ $start_date }}" required>
                        </div>
                    </div>
                    <div class="col-md-2 mt-3">
                        <div class="form-group">
                            <label class="label">Hasta</label>
                            <input id="end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY"
                                type="date" onchange="consultar()" value="{{ $end_date }}" required>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3">
                        <div class="form-group">
                            <label class="label">Sucursal</label>
                            <input type="hidden" name="sucursal_id_hidden" value="{{ $sucursal }}" />
                            <select id="sucursal_id" name="sucursal_id" class="selectpicker form-control"
                                data-live-search="true" data-live-search-style="contains" onchange="consultar()">
                                <option value="-1">Todos</option>
                                @foreach ($lims_sucursal_list as $sucursal)
                                    <option value="{{ $sucursal->sucursal }}">{{ $sucursal->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3 mt-3">
                        <div class="form-group">
                            <label class="label">Cuenta Contable</label>
                            <input type="hidden" name="account_id_hidden" value="{{ $account_id }}" />
                            <select id="account_id" name="account_id" class="selectpicker form-control"
                                data-live-search="true" data-live-search-style="contains" onchange="consultar()">
                                <option value="0">Todos</option>
                                @foreach ($lims_account_list as $account)
                                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 p-5">

                        <a id="consultabtn" class="btn btn-primary" href="#">{{ trans('file.submit') }}</a>

                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th>Nro Cuenta</th>
                        <th>Cuenta</th>
                        <th>Monto Total</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($listResumen))
                        @foreach ($listResumen as $key => $custom)
                            <tr>
                                <td>{{ $custom->account_no }}</td>
                                <td>{{ $custom->name }}</td>
                                <td>{{ number_format((float) $custom->ingreso, 2, '.', '') }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <th></th>
                    <th>Total</th>
                    <th>0.00</th>
                </tfoot>
            </table>
        </div>
    </section>


    <script type="text/javascript">
        $("ul#account").siblings('a').attr('aria-expanded', 'true');
        $("ul#account").addClass("show");
        $("ul#account #account-resumen-menu").addClass("active");

        $('#account_id').val($('input[name="account_id_hidden"]').val());
        $('#sucursal_id').val($('input[name="sucursal_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');
        consultar();
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
            'select': {
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
                        columns: ':visible:Not(.not-exported)',
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

                $(dt_selector.column(2).footer()).html(dt_selector.cells(rows, 2, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(2).footer()).html(dt_selector.column(2, {
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

        function consultar() {
            var start = $('#start_date').val();
            var end = $('#end_date').val();
            var wr = $('#account_id').val();
            var bl = $('#sucursal_id').val();
            var url = '<?php echo url('/'); ?>' + '/report/resumen_account/' + start + '/' + end + '/' + wr + '/' + bl;
            $("#consultabtn").attr("href", url);
        }
    </script>
@endsection
