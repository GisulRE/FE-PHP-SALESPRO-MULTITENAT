@extends('layout.main') @section('content')
    @if ($errors->has('name'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('name') }}
        </div>
    @endif
    @if ($errors->has('image'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('image') }}
        </div>
    @endif
    @if ($errors->has('email'))
        <div class="alert alert-danger alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ $errors->first('email') }}
        </div>
    @endif
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif
    <section>
        @if (in_array('employees-add', $all_permission))
            <div class="container-fluid">
                <a href="{{ route('employees.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Employee') }}</a>
            </div>
        @endif
        <div class="table-responsive">
            <table id="employee-table" class="table">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Image') }}</th>
                        <th>{{ trans('file.name') }}</th>
                        <th>{{ trans('file.Email') }}</th>
                        <th>{{ trans('file.Phone Number') }}</th>
                        <th>{{ trans('file.Department') }}</th>
                        <th>{{ trans('file.Pre Sale') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <!--<td><th>{{ trans('file.Address') }}</th>-->
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lims_employee_all as $key => $employee)
                        @php $department = \App\Department::find($employee->department_id); @endphp
                        <tr data-id="{{ $employee->id }}">
                            <td>{{ $key }}</td>
                            @if ($employee->image)
                                <td> <img src="{{ url('public/images/employee', $employee->image) }}" height="80"
                                        width="80">
                                </td>
                            @else
                                <td>No Image</td>
                            @endif
                            <td>{{ $employee->name }}</td>
                            <td>{{ $employee->email }}</td>
                            <td>{{ $employee->phone_number }}</td>
                            <td>{{ $department->name }}</td>
                            @if ($employee->pre_sale)
                                <td>
                                    <div class="badge badge-success">Activo</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Inactivo</div>
                                </td>
                            @endif
                            @if (isset($employee->is_public) && $employee->is_public)
                                <td>
                                    <div class="badge badge-success">Público</div>
                                </td>
                            @else
                                <td>
                                    <div class="badge badge-danger">Privado</div>
                                </td>
                            @endif
                            <!--<td>{{ $employee->address }}
                                    @if ($employee->city)
    {{ ', ' . $employee->city }}
    @endif
                                    @if ($employee->state)
    {{ ', ' . $employee->state }}
    @endif
                                    @if ($employee->postal_code)
    {{ ', ' . $employee->postal_code }}
    @endif
                                    @if ($employee->country)
    {{ ', ' . $employee->country }}
    @endif
                                </td>-->
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="false">{{ trans('file.action') }}
                                        <span class="caret"></span>
                                        <span class="sr-only">Toggle Dropdown</span>
                                    </button>
                                    <ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default"
                                        user="menu">
                                        @if (in_array('employees-edit', $all_permission))
                                            <li>
                                                <button type="button" data-id="{{ $employee->id }}"
                                                    data-name="{{ $employee->name }}" data-email="{{ $employee->email }}"
                                                    data-phone_number="{{ $employee->phone_number }}"
                                                    data-department_id="{{ $employee->department_id }}"
                                                    data-address="{{ $employee->address }}"
                                                    data-city="{{ $employee->city }}"
                                                    data-pre_sale="{{ $employee->pre_sale }}"
                                                    data-warehouse_id="{{ $employee->warehouse_id }}"
                                                    data-country="{{ $employee->country }}" 
                                                    data-user="{{ $employee->user_id }}" class="edit-btn btn btn-link"
                                                    data-toggle="modal" data-target="#editModal"><i
                                                        class="dripicons-document-edit"></i>
                                                    {{ trans('file.edit') }}</button>
                                            </li>
                                        @endif
                                        <li class="divider"></li>
                                        @if (in_array('employees-delete', $all_permission))
                                            {{ Form::open(['route' => ['employees.destroy', $employee->id], 'method' => 'DELETE']) }}
                                            <li>
                                                <button type="submit" class="btn btn-link"
                                                    onclick="return confirmDelete()"><i class="dripicons-trash"></i>
                                                    {{ trans('file.delete') }}</button>
                                            </li>
                                            {{ Form::close() }}
                                        @endif
                                        <li>
                                            <form method="POST" action="{{ route('employees.togglePublic', $employee->id) }}">
                                                @csrf
                                                <button type="submit" class="btn btn-link">
                                                    @if (isset($employee->is_public) && $employee->is_public)
                                                        <i class="dripicons-lock"></i> {{ trans('file.make private') }}
                                                    @else
                                                        <i class="dripicons-lock-open"></i> {{ trans('file.make public') }}
                                                    @endif
                                                </button>
                                            </form>
                                        </li>
                                        <li>
                                            <button type="button" data-id="{{ $employee->id }}"
                                                data-name="{{ $employee->name }}"
                                                data-contract_type="{{ $employee->contract_type }}"
                                                data-pay_commission="{{ $employee->pay_commission }}"
                                                data-percentage="{{ $employee->percentage }}"
                                                class="btn btn-link tipoContratoBtn" data-toggle="modal"
                                                data-target="#tipoContratoModal"><i class="dripicons-document-new"></i>
                                                {{ trans('file.type of contract') }}</button>
                                        </li>
                                        <li>
                                            <button type="button" data-id="{{ $employee->id }}"
                                                data-name="{{ $employee->name }}"
                                                class="btn btn-link scheduleBtn" data-toggle="modal"
                                                data-target="#reservationScheduleModal"><i class="dripicons-clock"></i>
                                                Horarios de reservas</button>
                                        </li>
                                        <li>
                                            <button type="button" data-id="{{ $employee->id }}"
                                                data-name="{{ $employee->name }}"
                                                data-has-pin="{{ $employee->attendance_pin ? '1' : '0' }}"
                                                class="btn btn-link pinBtn" data-toggle="modal"
                                                data-target="#pinModal"><i class="dripicons-lock"></i>
                                                Gestionar PIN Asistencia</button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

            <div id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" class="modal fade text-left">
                <div role="document" class="modal-dialog">
                    <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Employee') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    {!! Form::open(['route' => ['employees.update', 1], 'method' => 'put', 'files' => true]) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="employee_id" />
                            <label>{{ trans('file.name') }} *</label>
                            <input type="text" name="name" required class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Image') }}</label>
                            <input type="file" name="image" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Department') }} *</label>
                            <select class="form-control selectpicker" name="department_id" required>
                                @foreach ($lims_department_list as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Email') }} *</label>
                            <input type="email" name="email" required class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Phone Number') }} *</label>
                            <input type="text" name="phone_number" required class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Address') }}</label>
                            <input type="text" name="address" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.City') }}</label>
                            <input type="text" name="city" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Country') }}</label>
                            <input type="text" name="country" class="form-control">
                        </div>
                        <div class="col-md-6 form-group">
                            <input class="mt-2" type="checkbox" name="check_presale">
                            <label class="mt-2"><strong>Habilitar {{ trans('file.Pre Sale') }}</strong></label>
                        </div>
                        <div class="col-md-6 form-group" id="warehouse">
                            <label>{{ trans('file.Warehouse') }} *</label>
                            <select name="warehouse_id" class="selectpicker form-control" data-live-search="true"
                                data-live-search-style="begins" title="Seleccione Almacen...">
                                @foreach ($lims_warehouse_list as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group" id="user">
                            <label>{{ trans('file.User') }}</label>
                            <select name="user_id" class="selectpicker form-control" data-live-search="true"
                                data-live-search-style="begins" title="Seleccione Usuario...">
                                @foreach ($lims_users_all as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
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

    <div id="tipoContratoModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.type of contract') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    {!! Form::open(['route' => ['employees.update', 1], 'method' => 'put']) !!}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <input type="hidden" name="employee_id" />
                            <label>{{ trans('file.Employee') }} *</label>
                            <input type="text" name="name" required class="form-control" readonly>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.type contract') }} *</label>
                            <select class="form-control selectpicker" name="contract_type" required>
                                <option disabled>Seleccione</option>
                                <option value="SALARIO_MENSUAL">Salario Mensual</option>
                                <option value="COMISION_UNICA">Comisión Unica</option>
                                <option value="COMISION_POR_SERVICIOS">Comisión por Servicios</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.pay commission') }} *</label>
                            <select class="form-control selectpicker" name="pay_commission" required>
                                <option disabled>Seleccione</option>
                                <option value="TRUE">Si</option>
                                <option value="FALSE">No</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group" id="percentageContent">
                            <label>{{ trans('file.commission') }} (%) *</label>
                            <input type="number" min="0" max="100" name="percentage" class="form-control">
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

    <div id="reservationScheduleModal" tabindex="-1" role="dialog" aria-labelledby="reservationScheduleLabel"
        aria-hidden="true" class="modal fade text-left">
        <div role="document" class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="reservationScheduleLabel" class="modal-title">Horarios de reservas por empleado</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info" style="margin-bottom: 15px;">
                        Empleado: <strong id="scheduleEmployeeName">-</strong>
                    </div>
                    <input type="hidden" id="scheduleEmployeeId" />
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Día</th>
                                    <th>Hora inicio</th>
                                    <th>Hora fin</th>
                                    <th>Intervalo (min)</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody id="scheduleTableBody"></tbody>
                        </table>
                    </div>
                    <div class="form-group" style="margin-top: 10px; margin-bottom: 10px;">
                        <button type="button" id="addScheduleRow" class="btn btn-info">Agregar turno</button>
                    </div>
                    <div id="scheduleSaveMessage"></div>
                    <div class="form-group" style="margin-top: 15px;">
                        <button type="button" id="saveEmployeeSchedule" class="btn btn-primary">Guardar horarios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Gestionar PIN de Asistencia -->
    <div id="pinModal" tabindex="-1" role="dialog" aria-labelledby="pinModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog modal-sm" style="max-width: 420px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="pinModalLabel" class="modal-title"><i class="dripicons-lock"></i> Gestionar PIN de Asistencia</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3">
                        Empleado: <strong id="pinEmployeeName">-</strong><br>
                        Estado: <span id="pinStatusBadge"></span>
                    </div>
                    <p class="text-muted small">
                        El PIN autoriza la eliminación de turnos en <strong>Attention Shift</strong>.
                        Al generar uno nuevo, el anterior quedará invalidado.
                    </p>
                    <div class="form-group text-center">
                        <button type="button" id="btnGeneratePin" class="btn btn-warning">
                            <i class="dripicons-clockwise"></i> Generar nuevo PIN
                        </button>
                    </div>
                    <div id="pinResult" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $("ul#hrm").siblings('a').attr('aria-expanded', 'true');
        $("ul#hrm").addClass("show");
        $("ul#hrm #employee-menu").addClass("active");
        $('#warehouse').hide();
        $('#user').hide();
        var employee_id = [];
        var scheduleDayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        var scheduleWindow = { start_time: '08:00', end_time: '21:00', interval_minutes: 30 };
        // ---- PIN Modal ----
        var _pinEmployeeId = null;
        $('.pinBtn').on('click', function() {
            _pinEmployeeId = $(this).data('id');
            var name      = $(this).data('name');
            var hasPin    = $(this).data('has-pin') == '1';
            $('#pinEmployeeName').text(name);
            $('#pinResult').hide().html('');
            $('#pinStatusBadge').html(hasPin
                ? '<span class="badge badge-success">PIN configurado</span>'
                : '<span class="badge badge-warning">Sin PIN configurado</span>'
            );
        });

        $('#btnGeneratePin').on('click', function() {
            if (!_pinEmployeeId) return;
            var $btn = $(this);
            $btn.prop('disabled', true).text('Generando...');
            $.ajax({
                type: 'POST',
                url: '{{ url("employees") }}/' + _pinEmployeeId + '/generate-pin',
                success: function(resp) {
                    if (resp.success) {
                        $('#pinResult').html(
                            '<div class="alert alert-warning mt-2">'
                            + '<strong><i class="dripicons-warning"></i> ¡Copia este PIN ahora! No se mostrará nuevamente.</strong><br>'
                            + 'Código PIN de <em>' + resp.employee + '</em>:<br>'
                            + '<h2 class="text-center mt-2 mb-2" id="generatedPinCode" style="letter-spacing:8px;font-weight:bold;">' + resp.pin + '</h2>'
                            + '<button type="button" class="btn btn-sm btn-secondary" id="copyPinBtn"><i class="dripicons-copy"></i> Copiar</button>'
                            + '</div>'
                        ).show();
                        $('#pinStatusBadge').html('<span class="badge badge-success">PIN configurado</span>');
                        // Actualizar data-has-pin en el botón correspondiente
                        $(".pinBtn[data-id='" + _pinEmployeeId + "']").data('has-pin', '1');
                        // Copiar al portapapeles
                        $('#pinResult').on('click', '#copyPinBtn', function() {
                            var pin = $('#generatedPinCode').text();
                            if (navigator.clipboard) {
                                navigator.clipboard.writeText(pin).then(function() {
                                    $('#copyPinBtn').text('Copiado!').prop('disabled', true);
                                });
                            } else {
                                var $tmp = $('<input>').val(pin).appendTo('body').select();
                                document.execCommand('copy');
                                $tmp.remove();
                                $('#copyPinBtn').text('Copiado!').prop('disabled', true);
                            }
                        });
                    }
                },
                error: function(xhr) {
                    var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Error al generar PIN';
                    $('#pinResult').html('<div class="alert alert-danger">' + msg + '</div>').show();
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Generar nuevo PIN');
                }
            });
        });
        // ---- FIN PIN Modal ----
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function addMinutesToTime(timeStr, minutes) {
            var parts = (timeStr || '').split(':');
            var h = parseInt(parts[0], 10);
            var m = parseInt(parts[1], 10);
            if (isNaN(h) || isNaN(m)) {
                return scheduleWindow.end_time;
            }
            var total = (h * 60) + m + parseInt(minutes || 30, 10);
            var hh = Math.floor(total / 60);
            var mm = total % 60;
            if (hh > 23) hh = 23;
            return (hh < 10 ? '0' + hh : '' + hh) + ':' + (mm < 10 ? '0' + mm : '' + mm);
        }

        function applyScheduleWindow(posWindow) {
            if (!posWindow) {
                return;
            }
            scheduleWindow.start_time = posWindow.start_time || scheduleWindow.start_time;
            scheduleWindow.end_time = posWindow.end_time || scheduleWindow.end_time;
            scheduleWindow.interval_minutes = parseInt(posWindow.interval_minutes, 10) || scheduleWindow.interval_minutes;

            $('#scheduleTableBody .schedule-start').attr('min', scheduleWindow.start_time).attr('max', scheduleWindow.end_time);
            $('#scheduleTableBody .schedule-end').attr('min', scheduleWindow.start_time).attr('max', scheduleWindow.end_time);
            $('#scheduleTableBody .schedule-interval').attr('min', 5).attr('max', 120);
        }

        function buildScheduleRow(item) {
            var day = (item && item.day_of_week !== undefined) ? parseInt(item.day_of_week, 10) : 0;
            var interval = (item && item.interval_minutes) ? parseInt(item.interval_minutes, 10) : scheduleWindow.interval_minutes;
            var start = (item && item.start_time) ? item.start_time : scheduleWindow.start_time;
            var end = (item && item.end_time) ? item.end_time : addMinutesToTime(start, interval);
            var options = '';
            for (var i = 0; i <= 6; i++) {
                options += '<option value="' + i + '" ' + (i === day ? 'selected' : '') + '>' + scheduleDayNames[i] + '</option>';
            }

            return '<tr>'
                + '<td><select class="form-control schedule-day">' + options + '</select></td>'
                + '<td><input type="time" class="form-control schedule-start" min="' + scheduleWindow.start_time + '" max="' + scheduleWindow.end_time + '" value="' + start + '"></td>'
                + '<td><input type="time" class="form-control schedule-end" min="' + scheduleWindow.start_time + '" max="' + scheduleWindow.end_time + '" value="' + end + '"></td>'
                + '<td><input type="number" class="form-control schedule-interval" min="5" max="120" value="' + interval + '"></td>'
                + '<td><button type="button" class="btn btn-danger btn-sm remove-schedule-row">Eliminar</button></td>'
                + '</tr>';
        }

        function renderScheduleRows(schedules) {
            var tbody = $('#scheduleTableBody');
            tbody.html('');
            if (!schedules || !schedules.length) {
                $('#scheduleSaveMessage').html('<div class="alert alert-info">Sin horarios configurados. Puedes dejarlo vacío o agregar turnos dentro del rango ' + scheduleWindow.start_time + ' - ' + scheduleWindow.end_time + '.</div>');
                return;
            }
            schedules.forEach(function(item) {
                tbody.append(buildScheduleRow(item));
            });
        }

        function validateScheduleRowsClient(rows) {
            for (var i = 0; i < rows.length; i++) {
                var row = rows[i];
                if (!row.start_time || !row.end_time) {
                    return 'Debe definir hora inicio y fin (fila ' + (i + 1) + ')';
                }
                if (row.start_time >= row.end_time) {
                    return 'La hora fin debe ser mayor a la hora inicio (fila ' + (i + 1) + ')';
                }
                if (row.start_time < scheduleWindow.start_time || row.end_time > scheduleWindow.end_time) {
                    return 'Solo se permiten horarios dentro de ' + scheduleWindow.start_time + ' - ' + scheduleWindow.end_time + ' (fila ' + (i + 1) + ')';
                }
            }
            return null;
        }

        function collectScheduleRows() {
            var rows = [];
            $('#scheduleTableBody tr').each(function() {
                var $row = $(this);
                rows.push({
                    day_of_week: parseInt($row.find('.schedule-day').val(), 10),
                    is_enabled: true,
                    start_time: $row.find('.schedule-start').val(),
                    end_time: $row.find('.schedule-end').val(),
                    interval_minutes: parseInt($row.find('.schedule-interval').val(), 10) || 30
                });
            });
            return rows;
        }

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        $('.edit-btn').on('click', function() {
            $("#editModal input[name='employee_id']").val($(this).data('id'));
            $("#editModal input[name='name']").val($(this).data('name'));
            $("#editModal select[name='department_id']").val($(this).data('department_id'));
            $("#editModal input[name='email']").val($(this).data('email'));
            $("#editModal input[name='phone_number']").val($(this).data('phone_number'));
            $("#editModal input[name='address']").val($(this).data('address'));
            $("#editModal input[name='city']").val($(this).data('city'));
            $("#editModal input[name='country']").val($(this).data('country'));
            if ($(this).data('pre_sale') == 1) {
                $("#editModal input[name='check_presale']").val($(this).data('pre_sale'));
                $("#editModal input[name='check_presale']").prop('checked', true);
                $('#warehouse').show(400);
                $('select[name="warehouse_id"]').prop('required', true);
            } else {
                $("#editModal input[name='check_presale']").val($(this).data('pre_sale'));
                $("#editModal input[name='check_presale']").prop('checked', false);
                $('#warehouse').hide(400);
                $('select[name="warehouse_id"]').prop('required', false);
            }
            if ($(this).data('user') == "") {
                $("#editModal select[name='user_id']").val($(this).data('user'));
                $('#user').show(400);
            }else{
                $("#editModal select[name='user_id']").val($(this).data('user'));
                $('#user').hide(400);
            }
            $("#editModal select[name='warehouse_id']").val($(this).data('warehouse_id'));
            $('.selectpicker').selectpicker('refresh');
        });

        $(".tipoContratoBtn").on('click', function() {
            if ($(this).data('contract_type') == "COMISION_UNICA") $("#percentageContent").show();
            else $("#percentageContent").hide();
            $("#tipoContratoModal input[name='employee_id']").val($(this).data('id'));
            $("#tipoContratoModal input[name='name']").val($(this).data('name'));
            $("#tipoContratoModal select[name='contract_type']").val($(this).data('contract_type'));
            $("#tipoContratoModal input[name='percentage']").val($(this).data('percentage'));
            $("#tipoContratoModal select[name='pay_commission']").val($(this).data('pay_commission'));
            $(".selectpicker").selectpicker('refresh');
        });

        $('.scheduleBtn').on('click', function() {
            var employeeId = $(this).data('id');
            var employeeName = $(this).data('name');
            $('#scheduleEmployeeId').val(employeeId);
            $('#scheduleEmployeeName').text(employeeName);
            $('#scheduleSaveMessage').html('');
            $('#scheduleTableBody').html('<tr><td colspan="5">Cargando horarios...</td></tr>');

            $.get('{{ url("employees") }}/' + encodeURIComponent(employeeId) + '/reservation-schedules', function(resp) {
                applyScheduleWindow(resp.pos_window || null);
                renderScheduleRows(resp.schedules || []);
            }).fail(function(xhr) {
                var msg = 'No se pudo cargar la configuración de horarios.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    msg = xhr.responseJSON.message;
                }
                $('#scheduleTableBody').html('<tr><td colspan="5" class="text-danger">' + msg + '</td></tr>');
            });
        });

        $('#addScheduleRow').on('click', function() {
            $('#scheduleTableBody').append(buildScheduleRow({ day_of_week: 0 }));
            $('#scheduleSaveMessage').html('');
        });

        $(document).on('click', '.remove-schedule-row', function() {
            $(this).closest('tr').remove();
            if (!$('#scheduleTableBody tr').length) {
                $('#scheduleSaveMessage').html('<div class="alert alert-info">Sin turnos. Puedes guardar para dejar el día sin horarios o agregar nuevos turnos.</div>');
            }
        });

        $('#saveEmployeeSchedule').on('click', function() {
            var employeeId = $('#scheduleEmployeeId').val();
            if (!employeeId) {
                return;
            }

            var payload = { schedules: collectScheduleRows() };
            var validationError = validateScheduleRowsClient(payload.schedules || []);
            if (validationError) {
                $('#scheduleSaveMessage').html('<div class="alert alert-danger">' + validationError + '</div>');
                return;
            }
            var $btn = $(this);
            $btn.prop('disabled', true).text('Guardando...');
            $('#scheduleSaveMessage').html('');

            $.ajax({
                type: 'POST',
                url: '{{ url("employees") }}/' + encodeURIComponent(employeeId) + '/reservation-schedules',
                data: payload,
                success: function(resp) {
                    var okMsg = (resp && resp.message) ? resp.message : 'Horarios guardados con éxito';
                    $('#scheduleSaveMessage').html('<div class="alert alert-success">' + okMsg + '</div>');
                },
                error: function(xhr) {
                    var errMsg = 'No se pudo guardar la configuración';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    $('#scheduleSaveMessage').html('<div class="alert alert-danger">' + errMsg + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Guardar horarios');
                }
            });
        });

        $("#tipoContratoModal select[name='contract_type']").on("change", function() {
            const value = $(this).val();
            if (value == "COMISION_UNICA")
                $("#percentageContent").show();
            else {
                $("#percentageContent").hide();
                $("#tipoContratoModal input[name='percentage']").val(0);
            }
        });

        $('#editModal input[name="check_presale"]').on('change', function() {
            if ($(this).is(':checked')) {
                $('#warehouse').show(400);
                $('select[name="warehouse_id"]').prop('required', true);
            } else {
                $('#warehouse').hide(400);
                $('select[name="warehouse_id"]').prop('required', false);
            }
        });


        $('#employee-table').DataTable({
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
                    'targets': [0, 1, 6]
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
                        rows: ':visible',
                        stripHtml: false
                    },
                    customize: function(doc) {
                        for (var i = 1; i < doc.content[1].table.body.length; i++) {
                            if (doc.content[1].table.body[i][0].text.indexOf('<img src=') !== -1) {
                                var imagehtml = doc.content[1].table.body[i][0].text;
                                var regex = /<img.*?src=['"](.*?)['"]/;
                                var src = regex.exec(imagehtml)[1];
                                var tempImage = new Image();
                                tempImage.src = src;
                                var canvas = document.createElement("canvas");
                                canvas.width = tempImage.width;
                                canvas.height = tempImage.height;
                                var ctx = canvas.getContext("2d");
                                ctx.drawImage(tempImage, 0, 0);
                                var imagedata = canvas.toDataURL("image/png");
                                delete doc.content[1].table.body[i][0].text;
                                doc.content[1].table.body[i][0].image = imagedata;
                                doc.content[1].table.body[i][0].fit = [30, 30];
                            }
                        }
                    },
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                        format: {
                            body: function(data, row, column, node) {
                                if (column === 0 && (data.indexOf('<img src=') != -1)) {
                                    var regex = /<img.*?src=['"](.*?)['"]/;
                                    data = regex.exec(data)[1];
                                }
                                return data;
                            }
                        }
                    },
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:Not(.not-exported)',
                        rows: ':visible',
                        stripHtml: false
                    },
                },
                {
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        employee_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                employee_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (employee_id.length && confirm("Are you sure want to delete?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'employees/deletebyselection',
                                data: {
                                    employeeIdArray: employee_id
                                },
                                success: function(data) {
                                    alert(data);
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!employee_id.length)
                            alert('No employee is selected!');
                    }
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
        });
    </script>
@endsection
