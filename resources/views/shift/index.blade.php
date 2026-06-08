@extends('layout.main') @section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>{!! session()->get('message') !!}
        </div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close" data-dismiss="alert"
                aria-label="Close"><span aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}
        </div>
    @endif

    <section>
        <div class="container-fluid row">
            <div class="col-md-10">
                <button class="btn btn-info" data-toggle="modal" data-target="#createModal"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Attention Shift') }} </button>

            </div>
            <div class="col-md-2">
                <button class="btn btn-info" type="link" onclick="setting_turno()" disabled><i
                        class="dripicons-gear"></i> Ajuste Turno</button>
            </div>
        </div>
        <div class="table-responsive">
            <table id="turno-table" class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nro Turno</th>
                        <th>{{ trans('file.Customer') }}</th>
                        <th>{{ trans('file.Employee') }}</th>
                        <th>{{ trans('file.Status') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>

    <div id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Attention Shift') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    {!! Form::open(['route' => 'attentionshift.store', 'method' => 'post', 'id' => 'frm_turno']) !!}
                    <div class="row">

                        <div class="col-md-6 form-group">
                            <label> </label>
                            <button id="btn-attendance" class="btn btn-success" type="button" data-toggle="modal"
                                data-target="#attendance-modal"><i class="dripicons-plus"></i>
                                {{ trans('file.Add Employee') }} </button>
                            <button class="btn btn-info firstemployee" type="button"><i
                                    class="dripicons-media-shuffle"></i>
                                {{ trans('file.Add First Employee') }} </button>
                        </div>
                        <div class="col-md-6 form-group">
                            <input type="text" name="employee_name" placeholder="Empleado Asignado" class="form-control"
                                readonly>
                            <input type="hidden" name="employee_id">
                        </div>
                        <div class="col-md-6 form-group">
                            <label>{{ trans('file.Customer') }} *</label>
                            <div class="input-group pos">
                                <select id="customer_id" name="customer_id" class="form-control selectpicker"
                                    data-live-search="true" data-live-search-style="contains"
                                    title="Seleccione Cliente..." required>
                                    @foreach ($lims_customer_list as $customer)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->name . ' (' . $customer->phone_number . ')' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-default btn-sm" data-toggle="modal"
                                    data-target="#addCustomer"><i class="dripicons-plus"></i></button>
                            </div>
                            <input id="customerName" type="hidden" name="customer_name" value="Clientes Varios">
                        </div>
                    </div>
                    <div class="form-group">
                        <button id="btn_turno" type="button" class="btn btn-primary"
                            onclick="birthday()">{{ trans('file.generate') }}</button>
                    </div>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

    <div id="addCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'customer.store', 'method' => 'post', 'files' => true, 'id' => 'frmAddCustomer']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Customer') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="form-group">
                        <label>{{ trans('file.Customer Group') }} *</label>
                        <select required class="form-control selectpicker" name="customer_group_id">
                            @foreach ($lims_customer_group_all as $customer_group)
                                <option value="{{ $customer_group->id }}">{{ $customer_group->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.name') }} *</label>
                        <input type="text" name="name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Phone Number') }}</label>
                        <input type="text" name="phone_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Fecha Nacimiento (Opcional)</label>
                        <input type="date" name="date_birh" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Address') }}</label>
                        <input type="text" name="address" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Email') }}</label>
                        <input type="text" name="email" placeholder="example@example.com" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.City') }}</label>
                        <input type="text" name="city" class="form-control">
                    </div>
                    <div class="form-group">
                        <input type="hidden" name="pos" value="1">
                        <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary">
                    </div>
                </div>
                {{ Form::close() }}
            </div>
        </div>
    </div>

    <!-- Modal: Confirmación con PIN para eliminar turno -->
    <div id="pin-confirm-modal" tabindex="-1" role="dialog" aria-labelledby="pinConfirmLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog" style="max-width: 420px; width: 95%; margin: 10px auto;">
            <div class="modal-content" style="max-height: 95vh; overflow-y: auto;">
                <div class="modal-header bg-danger text-white">
                    <h5 id="pinConfirmLabel" class="modal-title"><i class="dripicons-lock"></i> Confirmar Eliminación</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close text-white"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pinConfirmTurnoId">
                    <input type="hidden" id="pinConfirmEmployeeId">
                    <p>¿Eliminar el turno de <strong id="pinConfirmEmployeeName">este empleado</strong>?</p>
                    <style>
                        /* Estilos del Teclado Virtual Numérico */
                        .keypad-grid {
                            display: grid;
                            grid-template-columns: repeat(3, 1fr);
                            gap: 12px;
                            max-width: 260px;
                            margin: 15px auto 0 auto;
                        }

                        .btn-key {
                            height: 55px;
                            font-size: 1.4rem;
                            font-weight: 600;
                            border-radius: 50% !important;
                            background-color: #f8f9fa;
                            border: 2px solid #e9ecef;
                            color: #495057;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                            transition: all 0.1s ease-in-out;
                            user-select: none;
                            touch-action: manipulation;
                        }

                        .btn-key:hover {
                            background-color: #e9ecef;
                            border-color: #dee2e6;
                            color: #212529;
                        }

                        .btn-key:active, .btn-key.active-state {
                            background-color: #ced4da !important;
                            border-color: #adb5bd !important;
                            transform: scale(0.92);
                            box-shadow: none;
                        }

                        .btn-key-danger {
                            color: #dc3545;
                            background-color: #fff5f5;
                            border-color: #ffe3e3;
                        }

                        .btn-key-danger:hover {
                            background-color: #ffe3e3;
                            border-color: #ffc9c9;
                            color: #bd2130;
                        }

                        .btn-key-danger:active, .btn-key-danger.active-state {
                            background-color: #f8d7da !important;
                            border-color: #f5c6cb !important;
                        }

                        .btn-key-warning {
                            color: #fd7e14;
                            background-color: #fff9db;
                            border-color: #ffe8cc;
                        }

                        .btn-key-warning:hover {
                            background-color: #ffe8cc;
                            border-color: #ffd8a8;
                            color: #d9480f;
                        }

                        .btn-key-warning:active, .btn-key-warning.active-state {
                            background-color: #ffe8cc !important;
                            border-color: #ffd8a8 !important;
                        }

                        /* Estilos del Contenedor OTP */
                        .otp-container {
                            display: flex;
                            justify-content: center;
                            gap: 12px;
                            margin: 15px 0;
                        }

                        .otp-field {
                            width: 50px;
                            height: 50px;
                            font-size: 1.6rem;
                            font-weight: 700;
                            text-align: center;
                            border: 2px solid #ced4da;
                            border-radius: 8px;
                            caret-color: transparent;
                            transition: all 0.15s ease-in-out;
                        }

                        .otp-field:focus {
                            border-color: #dc3545;
                            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
                            outline: none;
                        }

                        /* Responsividad para pantallas pequeñas */
                        @media (max-width: 440px) {
                            .keypad-grid {
                                gap: 10px;
                                max-width: 230px;
                            }
                            .btn-key {
                                height: 50px;
                                font-size: 1.25rem;
                            }
                            .otp-field {
                                width: 44px;
                                height: 44px;
                                font-size: 1.4rem;
                            }
                        }
                        @media (max-width: 340px) {
                            .keypad-grid {
                                gap: 8px;
                                max-width: 200px;
                            }
                            .btn-key {
                                height: 44px;
                                font-size: 1.15rem;
                            }
                            .otp-field {
                                width: 38px;
                                height: 38px;
                                font-size: 1.25rem;
                                gap: 8px;
                            }
                        }
                    </style>

                    <div id="pinInputGroup">
                        <label><i class="dripicons-lock"></i> Código PIN del empleado:</label>
                        <input type="hidden" id="pinConfirmInput">
                        
                        <!-- Contenedor OTP -->
                        <div class="otp-container">
                            <input type="password" class="otp-field" maxlength="1" pattern="[0-9]" inputmode="none" autocomplete="off" data-index="0">
                            <input type="password" class="otp-field" maxlength="1" pattern="[0-9]" inputmode="none" autocomplete="off" data-index="1">
                            <input type="password" class="otp-field" maxlength="1" pattern="[0-9]" inputmode="none" autocomplete="off" data-index="2">
                            <input type="password" class="otp-field" maxlength="1" pattern="[0-9]" inputmode="none" autocomplete="off" data-index="3">
                        </div>
                        
                        <!-- Teclado Virtual Numérico -->
                        <div id="virtualKeypad">
                            <div class="keypad-grid">
                                <button type="button" class="btn btn-key" data-val="1">1</button>
                                <button type="button" class="btn btn-key" data-val="2">2</button>
                                <button type="button" class="btn btn-key" data-val="3">3</button>
                                
                                <button type="button" class="btn btn-key" data-val="4">4</button>
                                <button type="button" class="btn btn-key" data-val="5">5</button>
                                <button type="button" class="btn btn-key" data-val="6">6</button>
                                
                                <button type="button" class="btn btn-key" data-val="7">7</button>
                                <button type="button" class="btn btn-key" data-val="8">8</button>
                                <button type="button" class="btn btn-key" data-val="9">9</button>
                                
                                <button type="button" class="btn btn-key btn-key-danger" data-val="clear">C</button>
                                <button type="button" class="btn btn-key" data-val="0">0</button>
                                <button type="button" class="btn btn-key btn-key-warning" data-val="backspace">
                                    <i class="dripicons-backspace"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div id="pinConfirmMsg" class="mt-2" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConfirmDeleteShift" class="btn btn-danger"><i class="dripicons-trash"></i> Confirmar Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- panel attendance -->
    <div id="attendance-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Seleccione {{ trans('file.Employee') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="row ml-2 mt-3 emp_list"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="addemployee-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
        class="modal fade bd-example-modal-sm">
        <div role="document" class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Seleccione {{ trans('file.Employee') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 form-group">
                        <select id="employee_id_up" class="form-control selectpicker" name="employee_id_up" required
                            data-live-search="true" data-live-search-style="begins" title="Seleccione Empleado...">
                        </select>
                    </div>
                    <div class="col-md-12 form-group">
                        <input type="hidden" name="turno_id" required>
                        <button id="btn_addemp" class="btn btn-success"><i
                                class="dripicons-plus"></i>{{ trans('file.Add Employee') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="setting-turno-modal" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true"
        class="modal fade bd-example-modal-sm">
        <div role="document" class="modal-dialog modal-dialog-centered modal-sm" style="max-width: 400px;">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Ajuste Reset de Posiciones</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="col-md-12 form-group">
                        <label class="d-tc mt-2"><strong>Hora/Minuto:</strong> &nbsp;</label>
                        <input id="hour_resetshift" type="time" name="hour_resetshift" class="form-control" />
                    </div>
                    <div class="col-md-12 form-group">
                        <button id="btn_updatepos" class="btn btn-success"><i
                                class="dripicons-clockwise"></i>Actualizar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        $("ul#sale").siblings('a').attr('aria-expanded', 'true');
        $("ul#sale").addClass("show");
        $("ul#sale #shift-menu").addClass("active");

        var attendance_id = [];
        var baseUrl = "<?php echo url('/'); ?>";

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            error: function(xhr, status, error) {
                swal("Error", "Estado: " + status + " Error: " + error, "error");

            }
        });
        loadtable();
        setInterval(function() {
            loadtable();
        }, 60000);

        function syncTurnoCustomer() {
            var selectedText = $('#customer_id option:selected').text() || '';
            if (selectedText !== '') {
                var customerName = selectedText.split(' (')[0].trim();
                $('#customerName').val(customerName);
            }
        }

        $('#customer_id').on('changed.bs.select', function() {
            syncTurnoCustomer();
        });

        if ($('#customer_id option').length > 0) {
            $('#customer_id').selectpicker('val', $('#customer_id option:first').val());
            syncTurnoCustomer();
        }

        $('.attendance-img').on('click', function() {
            var employee_id = $(this).data('employee');
            var employee_name = $(this).data('employee-name');
            $("input[name='employee_id']").val(employee_id);
            $("input[name='employee_name']").val(employee_name);
            $('#attendance-modal').modal('hide')
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        });

        function attendance(id, employee_name) {
            $("input[name='employee_id']").val(id);
            $("input[name='employee_name']").val(employee_name);
            $('#attendance-modal').modal('hide')
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
        }


        $('#btn-attendance').on('click', function() {
            $('.info').empty();
            $(".emp_list").empty();
            ///const div = document.createElement('emp_select');
            $.get('attention/list-enable-emp', function(data) {
                if (data.recordsFiltered > 0) {
                    data.data.forEach(function(emp, counter) {
                        $('.emp_list').append(emp.div);
                    });
                } else {
                    $('#attendance-modal .modal-body').append(data.data);
                }
            });
        });


        $('.firstemployee').on('click', function() {
            $.get('attention/employeefirst', function(data) {
                if (data) {
                    $("input[name='employee_id']").val(data.employee_id);
                    $("input[name='employee_name']").val(data.name);
                    swal('Asignacion', "Empleado asignado con éxito", "success");
                } else {
                    swal('Asignacion', "Fallo al asignar empleado, intente de nuevo!", "error");
                }
            });
        });

        function choose_emp(turno_id) {
            $("#employee_id_up").empty();
            //var turno_id = $(this).data('turno');
            $("input[name='turno_id']").val(turno_id);
            $.get('attention/employeelist', function(data) {
                if (data) {
                    addOption("employee_id_up", data, 1);
                } else {
                    swal('Asignacion', "Sin empleados disponibles, intente de nuevo!", "error");
                }
                $('#addemployee-modal').modal('show');
                $('.selectpicker').selectpicker('refresh');
            });
        }


        // Rutina para agregar opciones a un <select>
        function addOption(domElement, array, op) {
            var select = document.getElementById(domElement);
            if (op == 1) {
                for (value in array) {
                    var option = document.createElement("option");
                    option.text = array[value].name;
                    option.value = array[value].employee_id;
                    select.add(option);
                }
            }
        }

        $('#btn_addemp').on('click', function() {
            var idturno = $("input[name='turno_id']").val();
            var idemployee = $("select[name='employee_id_up']").val();
            if (idemployee) {
                $.ajax({
                    type: 'PUT',
                    url: 'attentionshift/' + idturno,
                    data: {
                        id: idturno,
                        employee: idemployee
                    },
                    success: function(response) {
                        //console.log(response);
                        location.reload();
                    },
                    error: function(response) {
                        //console.log(response);
                        swal("Error",
                            "Error en servidor o datos, Intente nuevamente ó contacte con soporte",
                            "error")
                    },
                });
            } else {
                swal("Error", "No se seleccion empleado de servicio, intente mas tarde", "error")
            }
        });

        function loadtable() {
            $('#turno-table').DataTable({
                destroy: true,
                "processing": true,
                "serverSide": true,
                "ajax": {
                    url: "attentionshift/list-data/1",
                    dataType: "json",
                    type: "get"
                },
                "createdRow": function(row, data, dataIndex) {
                    //$(row).addClass('sale-link');
                    //$(row).attr('data-sale', data['sale']);
                },
                "columns": [{
                        "data": "key"
                    },
                    {
                        "data": "reference_nro"
                    },
                    {
                        "data": "customer"
                    },
                    {
                        "data": "employee"
                    },
                    {
                        "data": "status"
                    },
                    {
                        "data": "options"
                    },
                ],
                'language': {

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
                        "orderable": false,
                    },
                    {
                        'render': function(data, type, row, meta) {
                            return data;
                        },
                        'targets': [0]
                    }
                ],
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
                        }
                    },
                    {
                        extend: 'csv',
                        text: '{{ trans('file.CSV') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible',
                        },
                    },
                    {
                        extend: 'print',
                        text: '{{ trans('file.Print') }}',
                        exportOptions: {
                            columns: ':visible:Not(.not-exported)',
                            rows: ':visible',
                        },
                    },
                    {
                        extend: 'colvis',
                        text: '{{ trans('file.Column visibility') }}',
                        columns: ':gt(0)'
                    },
                ],
            });
        }

        function setting_turno() {
            $("#hour_resetshift").empty();
            $.get('setting/pos_settingjson', function(data) {
                if (data) {
                    $("input[name='hour_resetshift']").val(data.hour_resetshift);
                } else {

                    $("input[name='hour_resetshift']").val(0);
                }
                $('#setting-turno-modal').modal('show');
            });
        }

        $('#btn_updatepos').on('click', function() {
            var hora = $("input[name='hour_resetshift']").val();
            $.ajax({
                type: 'POST',
                url: 'setting/pos_setting_update',
                data: {
                    hour_resetshift: hora
                },
                success: function(response) {
                    swal("Mensaje",
                        "Se actualizo la hora de Reseteo Posiciones",
                        "success");
                    $('#setting-turno-modal').modal('hide')
                    $('body').removeClass('modal-open');
                    $('.modal-backdrop').remove();
                },
                error: function(response) {
                    //console.log(response);
                    swal("Error",
                        "Error en servidor o datos, Intente nuevamente ó contacte con soporte",
                        "error");
                },
            });
        });

        $('#frm_turno').one('submit', function() {
            $(this).find('button[type="submit"]').attr('disabled', 'disabled');
        });

        function birthday() {
            $('#btn_turno').attr('disabled', 'disabled');
            var customer = $("input[name='customer_name']").val();
            $.ajax({
                type: 'POST',
                url: 'attentionshift/birthday',
                data: {
                    customer_name: customer
                },
                success: function(response) {
                    console.log(response);
                    if (response.birthday) {
                        swal({
                                title: "Mensaje Para Cliente!",
                                text: "" + response.message,
                                icon: "success",
                                buttons: {
                                    save: {
                                        text: "OK",
                                        value: true,
                                    },
                                },
                            })
                            .then((save) => {
                                $('#frm_turno').submit();
                            });
                    } else {
                        $('#frm_turno').submit();
                    }
                },
                error: function(response) {
                    //console.log(response);
                    swal("Error",
                        "Error en servidor o datos, Intente nuevamente ó contacte con soporte",
                        "error");
                    $('#frm_turno').submit();
                },
            });
        }

        $(document).on('submit', '#frmAddCustomer', function(e) {
            e.preventDefault();

            var form_data = $(this).serialize();
            $.ajax({
                url: '{{ route('customer.store') }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: form_data,
                success: function(data) {
                    if (data.status) {
                        document.getElementById('frmAddCustomer').reset();

                        if ($('#customer_id option[value="' + data.customer.id + '"]').length === 0) {
                            $('#customer_id').append(
                                $('<option>', {
                                    value: data.customer.id,
                                    text: data.customer.name + ' (' + (data.customer.phone_number || 'S/T') + ')'
                                })
                            );
                        }

                        $('#customer_id').val(data.customer.id);
                        $('.selectpicker').selectpicker('refresh');
                        $('#customer_id').trigger('changed.bs.select');

                        msg = new swal('Mensaje', data.message, 'success');
                        $('#addCustomer').modal('hide');
                    } else {
                        msg = new swal('Mensaje', data.message, 'error');
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    if (XMLHttpRequest.status === 422) {
                        var errors = $.parseJSON(XMLHttpRequest.responseText);
                        $.each(errors, function(key, value) {
                            if ($.isPlainObject(value)) {
                                $.each(value, function(key, value) {
                                    msg = new swal('Error Validacion', value, 'error');
                                });
                            } else {
                                msg = new swal('Error Validacion', value, 'error');
                            }
                        });
                    } else {
                        msg = new swal('Error', 'Estado: ' + textStatus + ' Error: ' + errorThrown, 'error');
                    }
                }
            });
        });

        /*window.Echo.channel('trades')
            .listen('Trade', (e) => {
                console.log(e.trade);
                //document.getElementById('latest_trade_user').innerText = e.trade;
            })*/

        // ---- PIN: Eliminar turno con confirmación ----
        var _pinDeleteTurnoId   = null;
        var _pinDeleteEmpId     = null;
        var _pinDeleteEmpName   = null;

        // Función global llamada desde las celdas generadas por el servidor
        window.openDeleteWithPin = function(turnoId, employeeId, employeeName) {
            _pinDeleteTurnoId = turnoId;
            _pinDeleteEmpId   = employeeId;
            _pinDeleteEmpName = employeeName || 'Sin Asignar';
            $('#pinConfirmTurnoId').val(turnoId);
            $('#pinConfirmEmployeeId').val(employeeId);
            $('#pinConfirmEmployeeName').text(_pinDeleteEmpName);
            $('#pinConfirmInput').val('');
            $('.otp-field').val(''); // Limpiar campos OTP
            $('#pinConfirmMsg').hide().html('');
            // Ocultar campo PIN si no hay empleado
            if (!employeeId) {
                $('#pinInputGroup').hide();
            } else {
                $('#pinInputGroup').show();
            }
            $('#pin-confirm-modal').modal('show');
        };

        // Foco automático al abrir modal
        $('#pin-confirm-modal').on('shown.bs.modal', function () {
            $('.otp-field').eq(0).focus();
        });

        // Manejar entrada física de teclado en campos OTP
        $(document).on('input', '.otp-field', function() {
            var $fields = $('.otp-field');
            var val = $(this).val();
            // Mantener solo números
            $(this).val(val.replace(/[^0-9]/g, ''));
            
            // Avanzar foco automático
            if (this.value.length === 1) {
                var index = $(this).data('index');
                if (index < 3) {
                    $fields.eq(index + 1).focus();
                }
            }
            updatePinValue();
        });

        $(document).on('keydown', '.otp-field', function(e) {
            var $fields = $('.otp-field');
            var index = $(this).data('index');
            
            if (e.key === 'Backspace' && this.value.length === 0) {
                if (index > 0) {
                    $fields.eq(index - 1).val('').focus();
                    updatePinValue();
                }
            }
        });

        function updatePinValue() {
            var pin = '';
            $('.otp-field').each(function() {
                pin += $(this).val();
            });
            $('#pinConfirmInput').val(pin);
        }

        // Lógica del Teclado Virtual Numérico con OTP
        $(document).on('click', '.btn-key', function(e) {
            e.preventDefault();
            var val = $(this).data('val');
            var $fields = $('.otp-field');

            // Retroalimentación visual
            var $btn = $(this);
            $btn.addClass('active-state');
            setTimeout(function() {
                $btn.removeClass('active-state');
            }, 80);

            if (val === 'clear') {
                $fields.val('');
                $fields.eq(0).focus();
                $('#pinConfirmInput').val('');
            } else if (val === 'backspace') {
                // Encontrar el último campo con valor y borrarlo
                for (var i = 3; i >= 0; i--) {
                    if ($fields.eq(i).val() !== '') {
                        $fields.eq(i).val('').focus();
                        break;
                    }
                }
                updatePinValue();
            } else {
                // Encontrar el primer campo vacío y rellenarlo
                for (var i = 0; i < 4; i++) {
                    if ($fields.eq(i).val() === '') {
                        $fields.eq(i).val(val);
                        if (i < 3) {
                            $fields.eq(i + 1).focus();
                        }
                        break;
                    }
                }
                updatePinValue();
            }
        });

        $('#btnConfirmDeleteShift').on('click', function() {
            var turnoId    = $('#pinConfirmTurnoId').val();
            var employeeId = $('#pinConfirmEmployeeId').val();
            var pin        = $('#pinConfirmInput').val();
            var $btn       = $(this);

            // Si no hay empleado asignado, eliminar directamente sin PIN
            if (!employeeId) {
                pin = '';
            } else if (!pin) {
                $('#pinConfirmMsg').html('<div class="alert alert-warning">Por favor ingresa el código PIN.</div>').show();
                return;
            }

            $btn.prop('disabled', true).html('<i class="dripicons-clockwise"></i> Eliminando...');
            $('#pinConfirmMsg').hide();

            $.ajax({
                type: 'DELETE',
                url: baseUrl + '/attentionshift/' + turnoId + '/secure',
                data: { pin: pin },
                success: function(resp) {
                    if (resp.success) {
                        $('#pin-confirm-modal').modal('hide');
                        $('body').removeClass('modal-open');
                        $('.modal-backdrop').remove();
                        swal('Eliminado', resp.message, 'success');
                        loadtable();
                    } else {
                        $('#pinConfirmMsg').html('<div class="alert alert-danger">' + (resp.message || 'Error desconocido') + '</div>').show();
                    }
                },
                error: function(xhr) {
                    var msg = 'Error al procesar la solicitud.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#pinConfirmMsg').html('<div class="alert alert-danger">' + msg + '</div>').show();
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="dripicons-trash"></i> Confirmar Eliminar');
                }
            });
        });
        // ---- FIN PIN: Eliminar turno ----
    </script>

    <script>
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = true;

        var pusher = new Pusher('deac184cc6e2c0c86615', {
            cluster: 'sa1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function(data) {
            alert(JSON.stringify(data));
        });
    </script>
@endsection
