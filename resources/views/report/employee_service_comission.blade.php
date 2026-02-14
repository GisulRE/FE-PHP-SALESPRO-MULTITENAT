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
                    <h3 class="text-center">{{ trans('file.Service Commission Employee Report') }}</h3>
                </div>
                {!! Form::open(['route' => 'report.employeeComissionService', 'method' => 'post']) !!}
                <div class="row mb-12 align-items-end px-3">
                    <div class="col-md-6 mt-3">
                        <div class="form-group row">
                            <label class="d-tc mt-2"><strong>{{ trans('file.Choose Your Date') }}</strong> &nbsp;</label>
                            <div class="d-tc">
                                <div class="input-group">
                                    <input id="input_start_date" name="start_date" class="form-control"
                                        placeholder="DD/MM/YYYY" type="date" value="{{ $start_date }}" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                    <label class="d-tc mt-2" style="margin-left: 5px"><strong> A </strong> &nbsp;</label>
                                    <input id="input_end_date" name="end_date" class="form-control" placeholder="DD/MM/YYYY"
                                        type="date" value="{{ $end_date }}" required>
                                    <div class="input-group-prepend">
                                        <div class="input-group-text">
                                            <i class="dripicons-calendar tx-10 lh-0 op-4"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mt-3">
                        <div class="form-group d-flex align-items-center flex-wrap">
                            <label class="mb-0 mr-2"><strong>{{ trans('file.Choose Employee') }}</strong></label>
                            <input type="hidden" name="employee_id_hidden" value="{{ $employee_id }}" />
                            <div class="d-inline-block" style="min-width: 260px;">
                                <select id="employee_id" name="employee_id" class="selectpicker form-control"
                                    data-live-search="true" data-live-search-style="begins">
                                    <option value="0">{{ trans('file.All Employee') }}</option>
                                    @foreach ($lims_employees_list as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-primary ml-2" type="submit">{{ trans('file.submit') }}</button>
                        </div>
                    </div>


                </div>
                <!-- Action buttons aligned: left group + right-aligned QR commission -->
                <div class="row mb-3 px-3">
                    <div class="col-md-12 d-flex flex-wrap align-items-center">
                        <!-- Left buttons group -->
                        <div class="btn-group" role="group" aria-label="Acciones de reporte izquierda">
                            <button type="button" id="btn-add-payroll" class="btn btn-success mr-2">
                                <i class="dripicons-wallet"></i> Agregar nómina
                            </button>
                            <button type="button" id="btn-add-adjustment" class="btn btn-warning mr-2">
                                <i class="dripicons-document-edit"></i> Ajuste Pago QR
                            </button>
                        </div>
                        <!-- Right-aligned QR commission button -->
                        <div class="btn-group ml-auto" role="group" aria-label="Acción de configuración">
                            <button class="btn btn-info" type="button" onclick="setting_qrcommission()">
                                <i class="dripicons-gear"></i> Comision QR
                            </button>
                        </div>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        @php
            $__total_bs = isset($total_grand) ? number_format((float) $total_grand, 2, '.', '') : '0.00';
            $__total_qr = isset($total_qr) ? number_format((float) $total_qr, 2, '.', '') : '0.00';
            $__total_com = isset($total_com) ? number_format((float) $total_com, 2, '.', '') : '0.00';
        @endphp
        <div class="row" style="margin: 0 15px 15px 15px;">
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">Total Ventas (Bs.)</h6>
                        <h4 class="mb-0 font-weight-bold">{{ $__total_bs }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-danger mb-3 shadow-lg border-0">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">Comisión Ganada (Bs.)</h6>
                        <h3 id="total-commission" class="mb-0 font-weight-bold">{{ $__total_com }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1">Total Comisión por QR (Bs.)</h6>
                        <h4 class="mb-0 font-weight-bold">{{ $__total_qr }}</h4>
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
                        <th>%</th>
                        <th>Comision QR Bs.</th>
                        <th>{{ trans('file.total comision') }} Bs.</th>
                    </tr>
                </thead>
                <tbody>
                    @if (!empty($comisiones))
                        <?php    $lims_pos_setting_data = App\PosSetting::latest()->first(); ?>
                        @foreach ($comisiones as $comision)
                                    <tr>
                                        <td>{{ $comision->id }}</td>
                                        <td>{{ $comision->reference_no }}</td>
                                        <td>{{ $comision->name }}</td>
                                        <?php
                            if ($comision->commission_percentage == 0) {
                                $total = (float) $comision->total;
                            } else {
                                $total = ((float) $comision->commission_percentage * (float) $comision->total) / 100;
                            }
                            $payments = App\Payment::where([['sale_id', $comision->sale_id], ['paying_method', 'Qr_simple']])->get();
                            foreach ($payments as $payment) {
                                $total = $total - $lims_pos_setting_data->qr_commission;
                            }
                                                                                                                                                                                                                                                            ?>
                                        <td>{{ $comision->employee->name }}</td>
                                        <td>{{ date('d/m/Y H:i:s', strtotime($comision->date_sell)) }}</td>
                                        <td>{{ number_format((float) $comision->total, 2, '.', '') }}</td>
                                        <td>{{ number_format((float) $comision->commission_percentage, 2, '.', '') }}%</td>
                                        @if (sizeof($payments) > 0)
                                            <td>{{ number_format((float) $lims_pos_setting_data->qr_commission, 2, '.', '') }}</td>
                                        @else
                                            <td>{{ number_format((float) 0, 2, '.', '') }}</td>
                                        @endif
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
                    <th></th>
                    <th>0</th>
                    <th>0</th>
                </tfoot>
            </table>
        </div>

        <div id="setting-qr-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
            class="modal fade bd-example-modal-sm">
            <div role="document" class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 id="exampleModalLabel" class="modal-title">Comision QR</h5>
                        <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                                aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12 form-group">
                            <label class="d-tc mt-2"><strong>Monto Comision Bs.</strong> &nbsp;</label>
                            <input id="qr_commission" type="number" name="qr_commission" class="form-control"
                                style="text-align: end;" />
                        </div>
                        <div class="col-md-12 form-group">
                            <button id="btn_updatepos" class="btn btn-success"><i class="dripicons-clockwise"></i>Actualizar
                                Comision QR
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para Agregar Nómina -->
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
        $("ul#report #servicempcom-report-menu").addClass("active");

        $('#employee_id').val($('input[name="employee_id_hidden"]').val());
        $('.selectpicker').selectpicker('refresh');

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
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.cells(rows, 7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.cells(rows, 8, {
                    page: 'current'
                }).data().sum().toFixed(2));
            } else {
                $(dt_selector.column(5).footer()).html(dt_selector.column(5, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(7).footer()).html(dt_selector.column(7, {
                    page: 'current'
                }).data().sum().toFixed(2));
                $(dt_selector.column(8).footer()).html(dt_selector.column(8, {
                    page: 'current'
                }).data().sum().toFixed(2));
            }
        }

        function setting_qrcommission() {
            $("#qr_commission").empty();
            $.get('../setting/pos_settingjson', function (data) {
                if (data) {
                    $("input[name='qr_commission']").val(data.qr_commission);
                } else {

                    $("input[name='qr_commission']").val(0);
                }
                $('#setting-qr-modal').modal('show');
            });
        }

        $('#btn_updatepos').on('click', function () {
            var qrcommission = $("input[name='qr_commission']").val();
            $.ajax({
                type: 'POST',
                url: '../setting/pos_setting_update',
                data: {
                    qr_commission: qrcommission
                },
                success: function (response) {
                    //console.log(response);
                    //location.reload();
                    swal("Mensaje",
                        "Se actualizo el monto de comision QR, actualice el resultado de reporte",
                        "success");
                    $('#setting-qr-modal').modal('hide')
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                },
                error: function (response) {
                    //console.log(response);
                    swal("Error",
                        "Error en servidor o datos, Intente nuevamente ó contacte con soporte",
                        "error");
                },
            });
        });

        $(".daterangepicker-field").daterangepicker({
            callback: function (startDate, endDate, period) {
                var start_date = startDate.format('YYYY-MM-DD');
                var end_date = endDate.format('YYYY-MM-DD');
                var title = start_date + ' To ' + end_date;
                $(this).val(title);
                $('input[name="start_date"]').val(start_date);
                $('input[name="end_date"]').val(end_date);
            }
        });

        // Trigger to add payroll from this report
        $('#btn-add-payroll').on('click', function (e) {
            e.preventDefault();

            var employeeId = $('#employee_id').val();
            if (!employeeId || employeeId == '0') {
                alert('Elija un empleado específico para agregar nómina');
                return;
            }

            var $btn = $(this);
            $btn.prop('disabled', true);

            // Capturar las fechas DIRECTAMENTE del DOM usando getElementById
            var startDateInput = document.getElementById('input_start_date');
            var endDateInput = document.getElementById('input_end_date');
            var startDate = startDateInput ? startDateInput.value : '';
            var endDate = endDateInput ? endDateInput.value : '';
            var employeeName = $('#employee_id option:selected').text();

            console.log('=== AGREGAR NÓMINA ===');
            console.log('Empleado:', employeeName, '(ID:', employeeId + ')');
            console.log('Fecha inicio capturada del input:', startDate);
            console.log('Fecha fin capturada del input:', endDate);

            if (!startDate || !endDate) {
                alert('Por favor seleccione las fechas de inicio y fin');
                $btn.prop('disabled', false);
                return;
            }

            console.log('Consultando al servidor con estas fechas...');

            // Consultar al backend para obtener el total_com del empleado en ese rango
            $.ajax({
                type: 'POST',
                url: "{{ route('report.employeeComissionService') }}",
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

                        // Formatear fechas para mostrar
                        var startDateFormatted = formatDate(startDate);
                        var endDateFormatted = formatDate(endDate);

                        // Llenar datos del modal
                        $('#modal-start-date').text(startDateFormatted);
                        $('#modal-end-date').text(endDateFormatted);
                        $('#modal-amount').text(amount.toFixed(2));
                        $('#payroll-info').show();

                        $('#payroll-employee-id').val(employeeId);
                        $('#payroll-employee-id').selectpicker('refresh');
                        $('#payroll-start-date').val(startDate);
                        $('#payroll-end-date').val(endDate);
                        $('#payroll-amount').val(amount.toFixed(2));

                        // Generar nota automática
                        var noteText = 'Pago de comisión por servicios del período ' + startDateFormatted + ' al ' + endDateFormatted + '. Empleado: ' + employeeName;
                        $('#payroll-note').val(noteText);

                        // Mostrar modal
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

        // Función para formatear fechas de YYYY-MM-DD a DD/MM/YYYY
        function formatDate(dateString) {
            if (!dateString) return '';
            var parts = dateString.split('-');
            if (parts.length === 3) {
                return parts[2] + '/' + parts[1] + '/' + parts[0];
            }
            return dateString;
        }

        // Manejar el envío del formulario de nómina
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

                    // Limpiar el formulario
                    $('#payroll-form')[0].reset();
                    $('.selectpicker').selectpicker('refresh');

                    // Mostrar mensaje de éxito con opción de ir a nóminas
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
                            // Recargar la página para actualizar los datos
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

        // Trigger to add adjustment account from this report
        $('#btn-add-adjustment').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $btn = $(this);
            $btn.prop('disabled', true);

            // Capturar las fechas DIRECTAMENTE del DOM usando getElementById
            var startDateInput = document.getElementById('input_start_date');
            var endDateInput = document.getElementById('input_end_date');
            var startDate = startDateInput ? startDateInput.value : '';
            var endDate = endDateInput ? endDateInput.value : '';
            var employeeId = $('#employee_id').val() || '0';

            console.log('=== CREAR AJUSTE DE CUENTA ===');
            console.log('Fecha inicio capturada del input:', startDate);
            console.log('Fecha fin capturada del input:', endDate);
            console.log('Empleado:', employeeId);

            if (!startDate || !endDate) {
                alert('Por favor seleccione las fechas de inicio y fin');
                $btn.prop('disabled', false);
                return;
            }

            console.log('Consultando al servidor con estas fechas...');

            // Consultar al backend para obtener el total_qr con las fechas de los inputs
            $.ajax({
                type: 'POST',
                url: "{{ route('report.employeeComissionService') }}",
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
                    console.log('total_qr recibido:', resp.total_qr);

                    try {
                        var amount = 0;
                        if (resp && typeof resp === 'object' && resp.total_qr !== undefined) {
                            amount = parseFloat(resp.total_qr) || 0;
                        } else if (typeof resp === 'string') {
                            var parsed = JSON.parse(resp);
                            amount = parseFloat(parsed.total_qr) || 0;
                        }

                        console.log('Monto total QR calculado:', amount);

                        if (amount === 0) {
                            if (!confirm('El total de comisión QR es 0. ¿Desea continuar?')) {
                                $btn.prop('disabled', false);
                                return;
                            }
                        }

                        // Construir URL con los parámetros
                        var params = new URLSearchParams({
                            amount: amount.toFixed(2),
                            start_date: startDate,
                            end_date: endDate,
                            employee_id: employeeId,
                            from_report: 'service_commission_qr'
                        });

                        var finalUrl = "{{ route('adjustment_account.create') }}" + '?' + params.toString();
                        console.log('URL de redirección:', finalUrl);
                        console.log('=== FIN ===');

                        window.location.href = finalUrl;
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
    </script>
@endsection