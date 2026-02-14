@extends('layout.main') @section('content')

@if (empty($lims_holidays))
<div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
        aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!'
    }}</div>
@endif

<section class="forms">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header mt-2">
                <h3 class="text-center">{{ trans('file.Report Holiday By Employee') }}</h3>
            </div>
            <div class="row mb-12">
                <div class="col-md-5 m-4">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <div class="input-group">
                                <input id="start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                    value="{{ $start_date }}" required onchange="consultar()">
                                <label class="d-tc mt-2" style="margin-left: 5px"><strong> A </strong> &nbsp;</label>
                                <input id="end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                    value="{{ $end_date }}" required onchange="consultar()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 m-4">
                    <div class="form-group row">
                        <label class="d-tc mt-2"><strong>{{ trans('file.Choose Employee') }}</strong> &nbsp;</label>
                        <div class="d-tc">
                            <input type="hidden" name="employee_id_hidden" value="{{ $employee_id }}" />
                            <select id="employee_id" name="employee_id" class="selectpicker form-control"
                                data-live-search="true" data-live-search-style="begins" onchange="consultar()">
                                <option value="0">{{ trans('file.All Employee') }}</option>
                                @foreach ($lims_employees_list as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-1 offset-md-0 m-4">
                    <div class="form-group">
                        <a id="consultabtn" class="btn btn-primary" href="#">{{trans('file.submit')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table id="report-table" class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ trans('file.date') }}</th>
                    <th>{{ trans('file.Employee') }}</th>
                    <th>{{ trans('file.From') }}</th>
                    <th>{{ trans('file.To') }}</th>
                    <th>{{ trans('file.Days') }}</th>
                    <th>{{ trans('file.Note') }}</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($lims_holidays))
                @foreach ($lims_holidays as $key=>$holiday)
                <tr>
                    <?php $key = $key + 1; ?>
                    <td>{{ $key }}</td>
                    <td>{{ date($general_setting->date_format, strtotime($holiday->created_at->toDateString())) }}
                    </td>
                    <td>{{ $holiday->user->name }}</td>
                    <td>{{ date($general_setting->date_format, strtotime($holiday->from_date)) }}</td>
                    <td>{{ date($general_setting->date_format, strtotime($holiday->to_date)) }}</td>
                    <td>{{ $holiday->days }}</td>
                    <td>{{ $holiday->note }}</td>
                </tr>
                @endforeach
                @endif
            </tbody>
            <tfoot>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th>Total</th>
                <th>0</th>
                <th></th>
            </tfoot>
        </table>
    </div>
</section>


<script type="text/javascript">
    $("ul#report").siblings('a').attr('aria-expanded', 'true');
    $("ul#report").addClass("show");
    $("ul#report #holiday-report-menu").addClass("active");

    $('#employee_id').val($('input[name="employee_id_hidden"]').val());
    $('.selectpicker').selectpicker('refresh');
    consultar()

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        error: function (xhr, status, error) {
            swal("Error", "Estado: " + status + " Error: " + error, "error");

        }
    });

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
            action: function (e, dt, button, config) {
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
                rows: ':visible'
            },
            action: function (e, dt, button, config) {
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
            action: function (e, dt, button, config) {
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
        drawCallback: function () {
            var api = this.api();
            datatable_sum(api, false);
        }
    });

    function datatable_sum(dt_selector, is_calling_first) {
        if (dt_selector.rows('.selected').any() && is_calling_first) {
            var rows = dt_selector.rows('.selected').indexes();
            $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                page: 'current'
            }).data().sum());
        } else {
            $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                page: 'current'
            }).data().sum());
        }
    }

    function consultar(){
        var dates = $('#start_date').val();
        var datee = $('#end_date').val();
        var user_id = $('#employee_id').val();
        var url = '<?php echo url('/'); ?>' + '/report/holiday-employee/'+dates+'/'+datee+"/"+ user_id;
        $("#consultabtn").attr("href", url)
    }
</script>
@endsection