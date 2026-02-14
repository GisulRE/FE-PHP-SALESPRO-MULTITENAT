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
                            <input id="customerName" type="text" name="customer_name" placeholder="Nombre de cliente"
                                class="form-control" value="Clientes Varios" required>
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
                    url: 'attentionshift/update',
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

        /*window.Echo.channel('trades')
            .listen('Trade', (e) => {
                console.log(e.trade);
                //document.getElementById('latest_trade_user').innerText = e.trade;
            })*/
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
