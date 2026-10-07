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
                    <h5 id="pinConfirmLabel" class="modal-title"><i class="dripicons-trash"></i> Confirmar Eliminación</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close text-white"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="pinConfirmTurnoId">
                    <input type="hidden" id="pinConfirmEmployeeId">
                    <p class="mb-0">¿Está seguro de que desea eliminar el turno de <strong id="pinConfirmEmployeeName">este empleado</strong>?</p>
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

        // ---- Eliminar turno con confirmación directa (sin PIN) ----
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
            $('#pinConfirmMsg').hide().html('');
            $('#pin-confirm-modal').modal('show');
        };

        $('#btnConfirmDeleteShift').on('click', function() {
            var turnoId = $('#pinConfirmTurnoId').val();
            var $btn    = $(this);

            $btn.prop('disabled', true).html('<i class="dripicons-clockwise"></i> Eliminando...');
            $('#pinConfirmMsg').hide();

            $.ajax({
                type: 'DELETE',
                url: baseUrl + '/attentionshift/' + turnoId + '/secure',
                data: {},
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
        // ---- FIN: Eliminar turno ----
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
