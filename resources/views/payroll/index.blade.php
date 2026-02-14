@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif
    <section>
        <div class="container-fluid">
            <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i>
                {{ trans('file.Add Payroll') }} </button>
        </div>
        <div class="table-responsive">
            <table id="payroll-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.date') }}</th>
                        <th>{{ trans('file.reference') }}</th>
                        <th>{{ trans('file.Employee') }}</th>
                        <th>{{ trans('file.Account') }}</th>
                        <th>{{ trans('file.Amount') }}</th>
                        <th>{{ trans('file.Method') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th>Total:</th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>

    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Payroll') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i
                                class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    {!! Form::open(['route' => 'payroll.store', 'method' => 'post', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Employee') }} *</label>
                            <select class="form-control selectpicker" name="employee_id" required data-live-search="true"
                                data-live-search-style="begins" title="Seleccione Empleado...">
                                @foreach ($lims_employee_list as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label> {{ trans('file.Account') }} *</label>
                            <select class="form-control selectpicker" name="account_id">
                                @foreach ($lims_account_list as $account)
                                    @if ($account->is_default)
                                        <option selected value="{{ $account->id }}">{{ $account->name }}
                                            [{{ $account->account_no }}]</option>
                                    @else
                                        <option value="{{ $account->id }}">{{ $account->name }}
                                            [{{ $account->account_no }}]</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Amount') }} *</label>
                            <input type="number" step="any" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Method') }} *</label>
                            <select class="form-control selectpicker" name="paying_method" required>
                                <option value="0">Efectivo</option>
                                <option value="1">Cheque</option>
                                <option value="2">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Payroll') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span aria-hidden="true"><i
                                class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    {!! Form::open(['route' => ['payroll.update', 1], 'method' => 'put', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="payroll_id">
                            <label>{{ trans('file.Employee') }} *</label>
                            <select class="form-control selectpicker" name="employee_id" required data-live-search="true"
                                data-live-search-style="begins" title="Seleccione Empleado...">
                                @foreach ($lims_employee_list as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label> {{ trans('file.Account') }} *</label>
                            <select class="form-control selectpicker" name="account_id">
                                @foreach ($lims_account_list as $account)
                                    @if ($account->is_default)
                                        <option selected value="{{ $account->id }}">{{ $account->name }}
                                            [{{ $account->account_no }}]</option>
                                    @else
                                        <option value="{{ $account->id }}">{{ $account->name }}
                                            [{{ $account->account_no }}]</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Amount') }} *</label>
                            <input type="number" step="any" name="amount" class="form-control" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Method') }} *</label>
                            <select class="form-control selectpicker" name="paying_method" required>
                                <option value="0">Efectivo</option>
                                <option value="1">Cheque</option>
                                <option value="2">Transferencia</option>
                            </select>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>{{ trans('file.Note') }}</label>
                            <textarea name="note" rows="3" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">{{ trans('file.submit') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#hrm").siblings('a').attr('aria-expanded', 'true');
        $("ul#hrm").addClass("show");
        $("ul#hrm #payroll-menu").addClass("active");

        var payroll_id = [];

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        function openDialog(idp) {
            var url = "payroll/"
            var id = idp;
            url = url.concat(id).concat("/edit");

            $.get(url, function (data) {
                $("#editModal input[name='payroll_id']").val(data['id']);
                $("#editModal select[name='employee_id']").val(data['employee_id']);
                $("#editModal select[name='account_id']").val(data['account_id']);
                $("#editModal input[name='amount']").val(data['amount']);
                $("#editModal select[name='paying_method']").val(data['paying_method']);
                $("#editModal textarea[name='note']").val(data['note']);
                $('.selectpicker').selectpicker('refresh');
            });

        }

        $('#payroll-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "payroll/payroll-data",
                data: {},
                dataType: "json",
                type: "post",
                /*success:function(data){
                    console.log(data);
                }*/
            },
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass('payroll-link');
                $(row).attr('data-id', data['id']);
            },
            "columns": [{
                "data": "key"
            },
            {
                "data": "date"
            },
            {
                "data": "reference_no"
            },
            {
                "data": "employee"
            },
            {
                "data": "account"
            },
            {
                "data": "amount"
            },
            {
                "data": "paying_method"
            },
            {
                "data": "options"
            },
            ],
            'language': {
                    /*'searchPlaceholder': "{{ trans('file.Type date or purchase reference...') }}",*/
                    'lengthMenu': '_MENU_ {{ trans('file.records per page') }}',
                "info": '<small>{{ trans('file.Showing') }} _START_ - _END_ (_TOTAL_)</small>',
                "search": '{{ trans('file.Search') }}',
                'paginate': {
                    'previous': '<i class="dripicons-chevron-left"></i>',
                    'next': '<i class="dripicons-chevron-right"></i>'
                }
            },
            order: [
                ['1', 'desc']
            ],
            'columnDefs': [{
                "orderable": true,
                'targets': [0, 1, 4]
            },
            {
                'render': function (data, type, row, meta) {
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
                    rows: ':visible',
                },
                action: function (e, dt, button, config) {
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
                    rows: ':visible',
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
                    columns: ':visible:Not(.not-exported)',
                    rows: ':visible',
                },
                action: function (e, dt, button, config) {
                    datatable_sum(dt, true);
                    $.fn.dataTable.ext.buttons.csvHtml5.action.call(this, e, dt, button, config);
                    datatable_sum(dt, false);
                },
                footer: true
            },
            {
                text: '{{ trans('file.delete') }}',
                className: 'buttons-delete',
                action: function (e, dt, node, config) {
                    payroll_id.length = 0;
                    $(':checkbox:checked').each(function (i) {
                        if (i) {
                            payroll_id[i - 1] = $(this).closest('tr').data('id');
                        }
                    });
                    if (payroll_id.length && confirm("Are you sure want to delete?")) {
                        $.ajax({
                            type: 'POST',
                            url: 'payroll/deletebyselection',
                            data: {
                                payrollIdArray: payroll_id
                            },
                            success: function (data) {
                                alert(data);
                            }
                        });
                        dt.rows({
                            page: 'current',
                            selected: true
                        }).remove().draw(false);
                    } else if (!payroll_id.length)
                        alert('No payroll is selected!');
                }
            },
            {
                extend: 'colvis',
                text: '{{ trans('file.Column visibility') }}',
                columns: ':gt(0)'
            },
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
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(5).footer()).html(dt_selector.cells(rows, 5, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        // If redirected from service commission report, open Add Payroll modal prefilled
        (function prefillFromQuery() {
            try {
                var params = new URLSearchParams(window.location.search);
                if (params.get('open_add') === '1') {
                    var employeeId = params.get('employee_id') || '';
                    var amount = params.get('amount') || '';
                    var startDate = params.get('start_date') || '';
                    var endDate = params.get('end_date') || '';
                    var employeeName = params.get('employee_name') || '';

                    // Open modal
                    $('#createModal').modal('show');

                    // Set fields after modal is shown to ensure DOM is ready
                    var noteText = '';
                    if (startDate || endDate) {
                        noteText = 'Comisión por servicios ' +
                            (employeeName ? ('de ' + employeeName + ' ') : '') +
                            'del ' + (startDate || '...') + ' al ' + (endDate || '...');
                    }

                    $('#createModal').on('shown.bs.modal', function () {
                        var $modal = $(this);
                        if (employeeId) {
                            $modal.find("select[name='employee_id']").val(employeeId);
                        }
                        if (amount) {
                            $modal.find("input[name='amount']").val(parseFloat(amount).toFixed(2));
                        }
                        // Default paying method to Efectivo
                        $modal.find("select[name='paying_method']").val('0');
                        if (noteText) {
                            $modal.find("textarea[name='note']").val(noteText);
                        }
                        $('.selectpicker').selectpicker('refresh');
                    });
                }
            } catch (e) {
                // no-op
            }
        })();
    </script>
@endsection