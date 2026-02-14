@extends('layout.main') @section('content')
    @if (session()->has('create_message'))
        <div class="alert alert-success alert-dismissible text-center">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{!! session()->get('create_message') !!}
        </div>
    @endif
    @if (session()->has('edit_message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('edit_message') }}</div>
    @endif
    @if (session()->has('import_message'))
        <div class="alert alert-success alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{!! session()->get('import_message') !!}</div>
    @endif
    @if (session()->has('not_permitted'))
        <div class="alert alert-danger alert-dismissible text-center"><button type="button" class="close"
                data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>{{ session()->get('not_permitted') }}</div>
    @endif

    <section>
        <div class="container-fluid">
            @if (in_array('customers-add', $all_permission))
                <a href="{{ route('customer.create') }}" class="btn btn-info"><i class="dripicons-plus"></i>
                    {{ trans('file.Add Customer') }}</a>&nbsp;
                <a href="#" data-toggle="modal" data-target="#importCustomer" class="btn btn-primary"><i
                        class="dripicons-copy"></i> {{ trans('file.Import Customer') }}</a>
                <a href="#" data-toggle="modal" data-target="#importarClientes" class="btn btn-info"><i
                        class="dripicons-copy"></i> {{ trans('file.Import Customer') }} Detallado</a>
            @endif
        </div>
        <div class="table-responsive">
            <table id="customer-table" class="table" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="not-exported"></th>
                        <th>{{ trans('file.Customer Group') }}</th>
                        <th>{{ trans('file.name') }}</th>
                        <th>Tipo Documento</th>
                        <th>Valor Documento</th>
                        <th>{{ trans('file.Email') }}</th>
                        <th>{{ trans('file.Phone Number') }}</th>
                        <th>{{ trans('file.Address') }}</th>
                        <th>{{ trans('file.Balance') }}</th>
                        <th class="not-exported">{{ trans('file.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </section>

    <div id="importCustomer" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'customer.import', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Import Customer') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <p>{{ trans('file.The correct column order is') }} (customer_group*, name*, company_name, email,
                        phone_number*, address*, city*, state, postal_code, country)
                        {{ trans('file.and you must follow this') }}.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Upload CSV File') }} *</label>
                                {{ Form::file('file', ['class' => 'form-control', 'required']) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label> {{ trans('file.Sample File') }}</label>
                                <a href="public/sample_file/sample_customer.csv" class="btn btn-info btn-block btn-md"><i
                                        class="dripicons-download"></i> {{ trans('file.Download') }}</a>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary" id="submit-button">
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div id="depositModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'customer.addDeposit', 'method' => 'post']) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Add Deposit') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <div class="form-group">
                        <input type="hidden" name="customer_id">
                        <label>{{ trans('file.Amount') }} *</label>
                        <input type="number" name="amount" step="any" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Note') }}</label>
                        <textarea name="note" rows="4" class="form-control"></textarea>
                    </div>
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary"
                        id="submit-button">
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div id="view-deposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.All Deposit') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <table class="table table-hover deposit-list">
                        <thead>
                            <tr>
                                <th>{{ trans('file.date') }}</th>
                                <th>{{ trans('file.Amount') }}</th>
                                <th>{{ trans('file.Note') }}</th>
                                <th>{{ trans('file.Created By') }}</th>
                                <th>{{ trans('file.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div id="edit-deposit" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Update Deposit') }}</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    {!! Form::open(['route' => 'customer.updateDeposit', 'method' => 'post']) !!}
                    <div class="form-group">
                        <label>{{ trans('file.Amount') }} *</label>
                        <input type="number" name="amount" step="any" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>{{ trans('file.Note') }}</label>
                        <textarea name="note" rows="4" class="form-control"></textarea>
                    </div>
                    <input type="hidden" name="deposit_id">
                    <button type="submit" class="btn btn-primary">{{ trans('file.update') }}</button>
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>


    <div id="importarClientes" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                {!! Form::open(['route' => 'customer.importar_cliente', 'method' => 'post', 'files' => true]) !!}
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">{{ trans('file.Import Customer') }} Detallado</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <p class="italic">
                        <small>{{ trans('file.The field labels marked with * are required input fields') }}.</small>
                    </p>
                    <p>{{ trans('file.The correct column order is') }} (grupocliente*, nombrecliente*, valordocumento, tipodocumento, complemento,
                        direccion, ciudad*, pais, correoelectronico*, numerotelefono*, empresa, direccionempresa, sitioweb, telefono, latitud, longitud)
                        {{ trans('file.and you must follow this') }}.</p>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>{{ trans('file.Upload CSV File') }}/XLSX *</label>
                                <input name="file" class="form-control" type="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required/>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label> {{ trans('file.Sample File') }}</label>
                                <a href="public/sample_file/sample_customer_detail.xlsx" class="btn btn-info btn-block btn-md"><i
                                        class="dripicons-download"></i> {{ trans('file.Download') }}</a>
                            </div>
                        </div>
                    </div>
                    <input type="submit" value="{{ trans('file.submit') }}" class="btn btn-primary"
                        id="submit-button">
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <div id="showMapModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"
        class="modal fade text-left">
        <div role="document" class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="exampleModalLabel" class="modal-title">Ubicación Cliente</h5>
                    <button type="button" data-dismiss="modal" aria-label="Close" class="close"><span
                            aria-hidden="true"><i class="dripicons-cross"></i></span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Abrir Mapa en Google Maps</label>
                        <a id="urlMap" href="#" class="btn btn-success" target="_blank"><i
                                class="fa fa-external-link"></i></a>
                    </div>
                    <div id="googleMap" style="width: 100%; height: 500px"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCSZQsIN0kowDXxPWJ9bqLvlyKvzL6t7Dw" defer></script>
    <script type="text/javascript">
        $("ul#people").siblings('a').attr('aria-expanded', 'true');
        $("ul#people").addClass("show");
        $("ul#people #customer-list-menu").addClass("active");

        function confirmDelete() {
            if (confirm("Are you sure want to delete?")) {
                return true;
            }
            return false;
        }

        var customer_id = [];
        var all_permission = <?php echo json_encode($all_permission); ?>;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("table.deposit-list").on("click", ".edit-btn", function(event) {
            var id = $(this).data('id');
            var rowindex = $(this).closest('tr').index();
            var amount = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(2)')
                .text();
            var note = $('table.deposit-list tbody tr:nth-child(' + (rowindex + 1) + ')').find('td:nth-child(3)')
                .text();
            if (note == 'N/A')
                note = '';

            $('#edit-deposit input[name="deposit_id"]').val(id);
            $('#edit-deposit input[name="amount"]').val(amount);
            $('#edit-deposit textarea[name="note"]').val(note);
            $('#view-deposit').modal('hide');
        });

        $('#customer-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: "customer/list-data",
                data: {
                    all_permission: all_permission
                },
                dataType: "json",
                type: "post",
                /*success:function(data){
                    console.log(data);
                }*/
            },
            "createdRow": function(row, data, dataIndex) {
                $(row).attr('data-id', data['id']);
            },
            "columns": [{
                    "data": "key"
                },
                {
                    "data": "customer_group"
                },
                {
                    "data": "name"
                },
                {
                    "data": "tipo_documento"
                },
                {
                    "data": "valor_documento"
                },
                {
                    "data": "email"
                },
                {
                    "data": "phone_number"
                },
                {
                    "data": "address"
                },
                {
                    "data": "balance"
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
                ['2', 'asc']
            ],
            'columnDefs': [{
                    "orderable": false,
                    'targets': [0, 1, 5, 6, 7, 8]
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
                        rows: ':visible'
                    }
                },
                {
                    extend: 'csv',
                    text: '{{ trans('file.CSV') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    extend: 'print',
                    text: '{{ trans('file.Print') }}',
                    exportOptions: {
                        columns: ':visible:not(.not-exported)',
                        rows: ':visible'
                    }
                },
                {
                    text: '{{ trans('file.delete') }}',
                    className: 'buttons-delete',
                    action: function(e, dt, node, config) {
                        customer_id.length = 0;
                        $(':checkbox:checked').each(function(i) {
                            if (i) {
                                customer_id[i - 1] = $(this).closest('tr').data('id');
                            }
                        });
                        if (customer_id.length && confirm("Está seguro de eliminar?")) {
                            $.ajax({
                                type: 'POST',
                                url: 'customer/deletebyselection',
                                data: {
                                    customerIdArray: customer_id
                                },
                                success: function(data) {
                                    swal("Mensaje", data, "success");
                                }
                            });
                            dt.rows({
                                page: 'current',
                                selected: true
                            }).remove().draw(false);
                        } else if (!customer_id.length)
                            swal("Mensaje", "Ningun Cliente seleccionado", "info");
                    }
                },
                {
                    extend: 'colvis',
                    text: '{{ trans('file.Column visibility') }}',
                    columns: ':gt(0)'
                },
            ],
            drawCallback: function() {
                var api = this.api();
                //datatable_sum(api, false);
            }
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function deposit(id) {
            $("#depositModal input[name='customer_id']").val(id);
        }

        function getDeposit(id) {
            $.get('customer/getDeposit/' + id, function(data) {
                $(".deposit-list tbody").remove();
                var newBody = $("<tbody>");
                $.each(data[0], function(index) {
                    var newRow = $("<tr>");
                    var cols = '';

                    cols += '<td>' + data[1][index] + '</td>';
                    cols += '<td>' + data[2][index] + '</td>';
                    if (data[3][index])
                        cols += '<td>' + data[3][index] + '</td>';
                    else
                        cols += '<td>N/A</td>';
                    cols += '<td>' + data[4][index] + '<br>' + data[5][index] + '</td>';
                    cols +=
                        '<td><div class="btn-group"><button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">{{ trans('file.action') }}<span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button><ul class="dropdown-menu edit-options dropdown-menu-right dropdown-default" user="menu"><li><button type="button" class="btn btn-link edit-btn" data-id="' +
                        data[0][index] +
                        '" data-toggle="modal" data-target="#edit-deposit"><i class="dripicons-document-edit"></i> {{ trans('file.edit') }}</button></li><li class="divider"></li>{{ Form::open(['route' => 'customer.deleteDeposit', 'method' => 'post']) }}<li><input type="hidden" name="id" value="' +
                        data[0][index] +
                        '" /> <button type="submit" class="btn btn-link" onclick="return confirmDelete()"><i class="dripicons-trash"></i> {{ trans('file.delete') }}</button></li>{{ Form::close() }}</ul></div></td>'
                    newRow.append(cols);
                    newBody.append(newRow);
                    $("table.deposit-list").append(newBody);
                });
                $("#view-deposit").modal('show');
            });
        }

        if (all_permission.indexOf("customers-delete") == -1)
            $('.buttons-delete').addClass('d-none');

        $("#export").on("click", function(e) {
            e.preventDefault();
            var customer = [];
            $(':checkbox:checked').each(function(i) {
                customer[i] = $(this).val();
            });
            $.ajax({
                type: 'POST',
                url: '/exportcustomer',
                data: {
                    customerArray: customer
                },
                success: function(data) {
                    swal("Mensaje", "Exportado a archivo CSV exitosamente, Click Ok para descargar")
                    window.location.href = data;
                }
            });
        });

        function showMap(latitude, longitude) {
            console.log("This is latitude :" + latitude);
            console.log("This is longitude :" + longitude);
            var url = "https://www.google.com/maps/search/?api=1&query=" + latitude + "," + longitude;
            $("#urlMap").attr("href", url)
            var myCenter = new google.maps.LatLng(latitude, longitude);
            var marker;

            function initialize() {
                var mapProp = {
                    center: myCenter,
                    zoom: 15,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                var map = new google.maps.Map(document.getElementById("googleMap"), mapProp);

                var marker = new google.maps.Marker({
                    position: myCenter,
                    animation: google.maps.Animation.BOUNCE
                });

                marker.setMap(map);
            }

            initialize();
        }
    </script>
@endsection
