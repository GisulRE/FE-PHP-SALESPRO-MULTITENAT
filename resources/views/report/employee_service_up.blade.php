@extends('layout.main') @section('content')

    @if (empty($comisiones))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ 'No Data exist between this date range!' }}</div>
    @endif

    <section class="forms">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header mt-2">
                    <h3 class="text-center">{{ trans('file.Service Employee Report') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.employeeService', 'method' => 'post']) !!}
                <div class="row mb-12">
                    <div class="col-md-6 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input id="input_start_date" name="start_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
                                        value="{{ $start_date }}" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                    <label class="d-tc mt-2" style="margin-left: 5px"><strong> A </strong> &nbsp;</label>
                                    <input id="input_end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY" type="date"
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
                    <div class="col-md-4 mt-3" style="margin-left: 3.333%;">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Employee') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <input type="hidden" name="employee_id_hidden" value="{{ $employee_id }}" />
                                <select id="employee_id" name="employee_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0">{{ trans('file.All Employee') }}</option>
                                    @foreach ($lims_employees_list as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
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
                <div class="row mb-3 px-3">
                    <div class="col-md-12 d-flex flex-wrap align-items-center">
                        <div class="btn-group" role="group" aria-label="Acciones de reporte izquierda">
                            <button type="button" id="btn-add-payroll" class="btn btn-success mr-2">
                                <i class="dripicons-wallet"></i> Agregar nómina
                            </button>
                            <button type="button" id="btn-add-adjustment" class="btn btn-warning mr-2">
                                <i class="dripicons-document-edit"></i> Ajuste Pago QR
                            </button>
                            <div class="btn-group mr-2" role="group">
                                <button id="btn-print-format" type="button" class="btn btn-primary dropdown-toggle"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="dripicons-print"></i> Imprimir
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btn-print-format">
                                    <a class="dropdown-item" href="#" id="print-format-a4">Formato A4</a>
                                    <a class="dropdown-item" href="#" id="print-format-ticket">Formato Ticket</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="report-table" class="table table-hover">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.reference') }}</th>
                        <th>{{ trans('file.Service') }}</th>
                        <th>{{ trans('file.Employee') }}</th>
                        <th>{{ trans('file.date') }}</th>
                        <th>{{ trans('file.grand total') }} Bs.</th>
                        <th>{{ trans('file.total comision') }} Bs.</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($comisiones))
                        @foreach ($comisiones as $comision)
                            <tr>
                                <td>{{ $comision->id }}</td>
                                <td>{{ $comision->reference_no }}</td>
                                <td>{{ $comision->name }}</td>
                                <?php
                                $employee = DB::table('employees')->find($comision->employee_id);
                                if ($employee->percentage == 0) {
                                    $total = (float) $comision->total;
                                } else {
                                    $total = ((float) $employee->percentage * (float) $comision->total) / 100;
                                }
                                ?>
                                <td>{{ $employee->name }}</td>
                                <td>{{ date('d/m/Y H:i:s', strtotime($comision->date_sell)) }}</td>
                                <td>{{ number_format((float) $comision->total, 2, '.', '') }}</td>
                                <td>{{ number_format((float) $total, 2, '.', '') }}</td>
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
                </tfoot>
            </table>
        </div>

        <div id="add-payroll-modal" tabindex="-1" role="dialog" aria-hidden="true" class="modal fade text-left">
            <div role="document" class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('file.Add Payroll') }}</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close">
                            <span aria-hidden="true"><i class="dripicons-cross"></i></span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p class="italic">
                            <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                        </p>
                        <div class="alert alert-info" id="payroll-info" style="display:none;">
                            <strong>Período:</strong> del <span id="modal-start-date"></span> al <span
                                id="modal-end-date"></span><br>
                            <strong>Comisión Calculada:</strong> <span id="modal-amount"></span> Bs.
                        </div>

                        <form id="payroll-form">
                            <input type="hidden" id="payroll-start-date" name="start_date">
                            <input type="hidden" id="payroll-end-date" name="end_date">

                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label>{{ trans('file.Employee') }} *</label>
                                    <select class="form-control selectpicker" id="payroll-employee-id" name="employee_id"
                                        required data-live-search="true" data-live-search-style="begins"
                                        title="Seleccione Empleado...">
                                        @foreach ($lims_employees_list as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ trans('file.Account') }} *</label>
                                    <select class="form-control selectpicker" id="payroll-account-id" name="account_id"
                                        required>
                                        @php
                                            $lims_account_list = \App\Account::where('is_active', true)->get();
                                        @endphp
                                        @foreach ($lims_account_list as $account)
                                            @if ($account->is_default)
                                                <option selected value="{{ $account->id }}">{{ $account->name }}
                                                    [{{ $account->account_no }}]</option>
                                            @else
                                                <option value="{{ $account->id }}">{{ $account->name }} [{{ $account->account_no }}]
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ trans('file.Amount') }} *</label>
                                    <input type="number" step="any" id="payroll-amount" name="amount" class="form-control"
                                        required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label>{{ trans('file.Method') }} *</label>
                                    <select class="form-control selectpicker" id="payroll-paying-method"
                                        name="paying_method" required>
                                        <option value="0">Efectivo</option>
                                        <option value="1">Cheque</option>
                                        <option value="2">Transferencia</option>
                                    </select>
                                </div>
                                <div class="col-md-12 form-group">
                                    <label>{{ trans('file.Note') }}</label>
                                    <textarea id="payroll-note" name="note" rows="3" class="form-control"></textarea>
                                </div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary" id="btn-save-payroll">
                                    {{ trans('file.submit') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>


    <script type="text/javascript">
        $("ul#report").siblings('a').attr('aria-expanded', 'true');
        $("ul#report").addClass("show");
        $("ul#report #servicemp-report-menu").addClass("active");

        $('#employee_id').val($('input[name="employee_id_hidden"]').val());
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
            } else {
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(6).footer()).html(dt_selector.column(6, {
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

        function toNumber(value) {
            if (value === null || value === undefined) {
                return 0;
            }
            var clean = value.toString().replace(/[^0-9.-]/g, '');
            var parsed = parseFloat(clean);
            return isNaN(parsed) ? 0 : parsed;
        }

        function getComissionRowsForPrint() {
            var rows = [];
            reportTable.rows({ search: 'applied' }).every(function () {
                var data = this.data();
                rows.push({
                    reference_no: data[1],
                    service: data[2],
                    employee: data[3],
                    date: data[4],
                    total: data[5],
                    comision: data[6]
                });
            });
            return rows;
        }

        function printComissionTicket() {
            var selectedEmployeeId = $('#employee_id').val();
            var employeeName = $('#employee_id option:selected').text();
            var startDate = $('input[name="start_date"]').val();
            var endDate = $('input[name="end_date"]').val();

            var rows = getComissionRowsForPrint();
            if (!rows.length) {
                alert('No hay datos para imprimir.');
                return;
            }

            var detailHtml = '';
            var totalComission = 0;
            var groupedRows = {};

            rows.forEach(function (row) {
                var employeeKey = row.employee || 'Sin empleado';
                if (!groupedRows[employeeKey]) {
                    groupedRows[employeeKey] = [];
                }
                groupedRows[employeeKey].push(row);
            });

            Object.keys(groupedRows).forEach(function (empName) {
                var subtotalByEmployee = 0;
                detailHtml += '<tr><td colspan="5" style="font-weight:700; border-top:1px dashed #000;">Empleado: ' + empName + '</td></tr>';

                groupedRows[empName].forEach(function (row) {
                    var rowComission = toNumber(row.comision);
                    totalComission += rowComission;
                    subtotalByEmployee += rowComission;
                    detailHtml += '<tr>' +
                        '<td>' + row.reference_no + '</td>' +
                        '<td>' + row.service + '</td>' +
                        '<td>' + row.date + '</td>' +
                        '<td class="text-right">' + row.total + '</td>' +
                        '<td class="text-right">' + row.comision + '</td>' +
                        '</tr>';
                });

                detailHtml += '<tr><td colspan="4" class="text-right" style="font-weight:700;">Subtotal ' + empName + '</td>' +
                    '<td class="text-right" style="font-weight:700;">' + subtotalByEmployee.toFixed(2) + '</td></tr>';
            });

            var printWindow = window.open('', '_blank', 'width=460,height=760');
            if (!printWindow) {
                alert('No se pudo abrir la ventana de impresión. Verifique el bloqueador de ventanas.');
                return;
            }

            var html = '<!doctype html><html><head><meta charset="utf-8"><title>Comisión de Empleado</title>' +
                '<style>' +
                'body{font-family:Arial, sans-serif; margin:0; padding:10px; color:#000; width:80mm;}' +
                '.title{text-align:center; font-weight:700; font-size:16px; margin-bottom:8px;}' +
                '.meta{font-size:12px; margin-bottom:8px;}' +
                '.line{border-top:1px dashed #000; margin:8px 0;}' +
                'table{width:100%; border-collapse:collapse; font-size:10px;}' +
                'th,td{padding:3px 2px; border-bottom:1px solid #ddd; vertical-align:top;}' +
                'th{text-align:left; font-size:9px;}' +
                '.text-right{text-align:right;}' +
                '.total{margin-top:8px; font-size:14px; font-weight:700; text-align:right;}' +
                '@media print { @page { size: 80mm auto; margin: 4mm; } body{width:auto;} }' +
                '</style></head><body>' +
                '<div class="title">Comisión de Empleado</div>' +
                '<div class="meta"><strong>Nombre del Empleado:</strong> ' + employeeName + '<br>' +
                '<strong>Rango de Fechas:</strong> ' + formatDate(startDate) + ' a ' + formatDate(endDate) + '</div>' +
                '<div class="line"></div>' +
                '<table><thead><tr>' +
                '<th>Nro. Pre-Venta</th>' +
                '<th>Servicio</th>' +
                '<th>Fecha</th>' +
                '<th class="text-right">Total</th>' +
                '<th class="text-right">Comisión</th>' +
                '</tr></thead><tbody>' + detailHtml + '</tbody></table>' +
                '<div class="total">TOTAL: Bs. ' + totalComission.toFixed(2) + '</div>' +
                '</body></html>';

            printWindow.document.open();
            printWindow.document.write(html);
            printWindow.document.close();
            printWindow.focus();
            setTimeout(function () {
                printWindow.print();
            }, 300);
        }

        $('#print-format-a4').on('click', function (e) {
            e.preventDefault();
            $('.buttons-print').click();
        });

        $('#print-format-ticket').on('click', function (e) {
            e.preventDefault();
            printComissionTicket();
        });

        function formatDate(dateString) {
            if (!dateString) return '';
            var parts = dateString.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dateString;
        }

        $('#btn-add-payroll').on('click', function (e) {
            e.preventDefault();

            var employeeId = $('#employee_id').val();
            if (!employeeId || employeeId == '0') {
                alert('Elija un empleado específico para agregar nómina');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            var startDateInput = document.getElementById('input_start_date');
            var endDateInput = document.getElementById('input_end_date');
            var startDate = startDateInput ? startDateInput.value : '';
            var endDate = endDateInput ? endDateInput.value : '';
            var employeeName = $('#employee_id option:selected').text();

            console.log('=== AGREGAR NÓMINA ===');
            console.log('Empleado:', employeeName, '(ID:', employeeId + ')');
            console.log('Fecha inicio:', startDate);
            console.log('Fecha fin:', endDate);

            if (!startDate || !endDate) {
                alert('Por favor seleccione las fechas de inicio y fin');
                $btn.prop('disabled', false);
                return;
            }

            $.ajax({
                type: 'POST',
                url: "{{ route('report.employeeService') }}",
                data: {
                    start_date: startDate,
                    end_date: endDate,
                    employee_id: employeeId,
                    guess: 'true',
                    start: 0,
                    length: 0,
                    draw: 1
                },
                success: function (resp) {
                    console.log('Respuesta del servidor:', resp);
                    console.log('total_com recibido:', resp.total_com);

                    try {
                        var amount = 0;
                        if (resp && typeof resp === 'object' && resp.total_com !== undefined) {
                            amount = parseFloat(resp.total_com) || 0;
                        } else if (typeof resp === 'string') {
                            var parsed = JSON.parse(resp);
                            amount = parseFloat(parsed.total_com) || 0;
                        }

                        console.log('Monto comisión ganada calculado:', amount);

                        if (amount <= 0) {
                            if (!confirm('El total de comisión ganada es 0. ¿Desea continuar para registrar la nómina?')) {
                                $btn.prop('disabled', false);
                                return;
                            }
                        }

                        var startDateFormatted = formatDate(startDate);
                        var endDateFormatted = formatDate(endDate);

                        $('#modal-start-date').text(startDateFormatted);
                        $('#modal-end-date').text(endDateFormatted);
                        $('#modal-amount').text(amount.toFixed(2));
                        $('#payroll-info').show();

                        $('#payroll-employee-id').val(employeeId);
                        $('#payroll-employee-id').selectpicker('refresh');
                        $('#payroll-start-date').val(startDate);
                        $('#payroll-end-date').val(endDate);
                        $('#payroll-amount').val(amount.toFixed(2));

                        var noteText = 'Pago de comisión por servicios del período ' + startDateFormatted + ' al ' + endDateFormatted + '. Empleado: ' + employeeName;
                        $('#payroll-note').val(noteText);

                        $('#add-payroll-modal').modal('show');
                        $btn.prop('disabled', false);

                        console.log('=== FIN ===');
                    } catch (e) {
                        console.error('Error procesando respuesta:', e);
                        alert('No se pudo obtener el total actualizado. Error: ' + e.message);
                        $btn.prop('disabled', false);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error en AJAX:', { xhr: xhr, status: status, error: error });
                    alert('Error al consultar el total actualizado.');
                    $btn.prop('disabled', false);
                }
            });
        });

        $('#payroll-form').on('submit', function (e) {
            e.preventDefault();

            var formData = {
                employee_id: $('#payroll-employee-id').val(),
                account_id: $('#payroll-account-id').val(),
                amount: $('#payroll-amount').val(),
                paying_method: $('#payroll-paying-method').val(),
                note: $('#payroll-note').val()
            };

            console.log('Datos a enviar:', formData);

            if (!formData.employee_id || !formData.account_id || !formData.amount || !formData.paying_method) {
                swal("Error", "Por favor complete todos los campos obligatorios", "error");
                return;
            }

            $('#btn-save-payroll').prop('disabled', true).text('Guardando...');

            $.ajax({
                type: 'POST',
                url: "{{ route('payroll.store') }}",
                data: formData,
                success: function (response) {
                    console.log('Respuesta:', response);
                    $('#add-payroll-modal').modal('hide');
                    $('#btn-save-payroll').prop('disabled', false).text('{{ trans("file.submit") }}');

                    $('#payroll-form')[0].reset();
                    $('.selectpicker').selectpicker('refresh');

                    swal({
                        title: "¡Éxito!",
                        text: "El pago de nómina se ha registrado correctamente",
                        icon: "success",
                        buttons: {
                            cancel: {
                                text: "Quedarse aquí",
                                value: false,
                                visible: true,
                                closeModal: true,
                            },
                            confirm: {
                                text: "Ir a Nóminas",
                                value: true,
                                visible: true,
                                closeModal: true
                            }
                        }
                    }).then((willRedirect) => {
                        if (willRedirect) {
                            window.location.href = "{{ route('payroll.index') }}";
                        } else {
                            location.reload();
                        }
                    });
                },
                error: function (xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    $('#btn-save-payroll').prop('disabled', false).text('{{ trans("file.submit") }}');
                    var errorMsg = "No se pudo guardar el pago.";
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg += " " + xhr.responseJSON.message;
                    }
                    swal("Error", errorMsg, "error");
                }
            });
        });

        $('#btn-add-adjustment').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            $btn.prop('disabled', true);

            var startDateInput = document.getElementById('input_start_date');
            var endDateInput = document.getElementById('input_end_date');
            var startDate = startDateInput ? startDateInput.value : '';
            var endDate = endDateInput ? endDateInput.value : '';
            var employeeId = $('#employee_id').val() || '0';

            console.log('=== CREAR AJUSTE DE CUENTA ===');
            console.log('Fecha inicio:', startDate);
            console.log('Fecha fin:', endDate);
            console.log('Empleado:', employeeId);

            if (!startDate || !endDate) {
                alert('Por favor seleccione las fechas de inicio y fin');
                $btn.prop('disabled', false);
                return;
            }

            var employeeName = $('#employee_id option:selected').text();
            var amount = 0;
            var rows = getComissionRowsForPrint();
            rows.forEach(function (row) {
                amount += toNumber(row.comision);
            });

            if (amount === 0) {
                if (!confirm('El total de comisión es 0. ¿Desea continuar?')) {
                    $btn.prop('disabled', false);
                    return;
                }
            }

            var params = new URLSearchParams({
                amount: amount.toFixed(2),
                start_date: startDate,
                end_date: endDate,
                employee_id: employeeId,
                from_report: 'service_commission_qr'
            });

            var finalUrl = "{{ route('adjustment_account.create') }}" + '?' + params.toString();
            console.log('URL de redirección:', finalUrl);

            window.location.href = finalUrl;
            $btn.prop('disabled', false);
        });
    </script>
@endsection
